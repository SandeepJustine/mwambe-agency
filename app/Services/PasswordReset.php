<?php

declare(strict_types=1);

namespace Mwambe\Services;

use Mwambe\Core\Application;
use Mwambe\Core\Config;
use Mwambe\Core\Database;

final class PasswordReset
{
    private const TOKEN_TTL_MINUTES = 60;

    public function __construct(
        private readonly Database $db,
        private readonly Mailer $mailer,
        private readonly Config $config,
        private readonly Application $app,
    ) {}

    /** @return array{ok:bool,error?:string,reference?:string} */
    public function request(string $email, string $ip): array
    {
        $email = strtolower(trim($email));

        // Rate limit: max 3 requests per hour per email or IP
        $recent = (int) $this->db->selectOne("
            SELECT COUNT(*) AS c FROM password_reset_attempts
            WHERE attempted_at > (NOW() - INTERVAL 1 HOUR)
              AND (email = :email OR ip_address = :ip)
        ", ['email' => $email, 'ip' => $ip])['c'];

        if ($recent >= 3) {
            return ['ok' => false, 'error' => 'Too many reset requests. Please wait an hour before trying again.'];
        }

        $this->db->execute(
            'INSERT INTO password_reset_attempts (email, ip_address) VALUES (?, ?)',
            [$email, $ip]
        );

        $user = $this->db->selectOne(
            'SELECT id, name, email FROM users WHERE email = ? AND is_active = 1',
            [$email]
        );

        // Always return the same response, regardless of whether the user exists —
        // prevents user enumeration.
        if ($user === null) {
            return ['ok' => true];
        }

        // Invalidate any existing unused tokens for this user
        $this->db->execute('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL', [$user['id']]);

        // Generate a strong token
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_MINUTES * 60);

        $this->db->execute("
            INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (?, ?, ?)
        ", [$user['id'], $tokenHash, $expiresAt]);

        $resetUrl = url('/admin/reset-password?token=' . $rawToken);

        $body = $this->renderResetEmail($user['name'], $resetUrl);
        $result = $this->mailer->send(
            $user['email'],
            $user['name'],
            'Reset your MWAMBE Admin password',
            $body
        );

        if (!$result['ok']) {
            // Log failure but do not expose to the requester
            $this->app->log('error', 'Password reset email failed', [
                'user_id' => $user['id'],
                'error'   => $result['error'] ?? 'unknown',
            ]);
            return ['ok' => false, 'error' => 'We could not send the reset email. Please contact support.'];
        }

        return ['ok' => true];
    }

    /** @return array{ok:bool,error?:string,user_id?:int} */
    public function verify(string $rawToken): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $rawToken)) {
            return ['ok' => false, 'error' => 'Invalid reset link.'];
        }
        $hash = hash('sha256', $rawToken);

        $row = $this->db->selectOne("
            SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.is_active
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token_hash = ?
            LIMIT 1
        ", [$hash]);

        if ($row === null) {
            return ['ok' => false, 'error' => 'Invalid reset link.'];
        }
        if ($row['used_at'] !== null) {
            return ['ok' => false, 'error' => 'This reset link has already been used.'];
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            return ['ok' => false, 'error' => 'This reset link has expired. Please request a new one.'];
        }
        if ((int) $row['is_active'] !== 1) {
            return ['ok' => false, 'error' => 'This account is inactive.'];
        }

        return ['ok' => true, 'user_id' => (int) $row['user_id']];
    }

    /** @return array{ok:bool,error?:string} */
    public function complete(string $rawToken, string $newPassword): array
    {
        $verify = $this->verify($rawToken);
        if (!$verify['ok']) {
            return $verify;
        }

        if (strlen($newPassword) < 12) {
            return ['ok' => false, 'error' => 'Password must be at least 12 characters.'];
        }

        $hash = hash('sha256', $rawToken);
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->db->transaction(function (Database $db) use ($hash, $newPasswordHash, $verify) {
            $db->execute('UPDATE users SET password_hash = ? WHERE id = ?', [$newPasswordHash, $verify['user_id']]);
            $db->execute('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?', [$hash]);

            // Revoke all sessions for this user
            $db->execute('DELETE FROM sessions WHERE user_id = ?', [$verify['user_id']]);
        });

        $this->app->log('info', 'Password reset completed', ['user_id' => $verify['user_id']]);
        return ['ok' => true];
    }

    private function renderResetEmail(string $name, string $url): string
    {
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        return "
            <p>Hello {$e($name)},</p>
            <p>A password reset was requested for your MWAMBE Admin account.</p>
            <p><a href=\"{$e($url)}\" style=\"display:inline-block;padding:12px 20px;background:#C29A2C;color:#231F20;text-decoration:none;border-radius:6px;font-weight:600\">Reset password</a></p>
            <p>This link expires in " . self::TOKEN_TTL_MINUTES . " minutes and can only be used once.</p>
            <p>If you did not request this, you can safely ignore this email — your password will not change.</p>
            <p>— MWAMBE Agency</p>
        ";
    }
}