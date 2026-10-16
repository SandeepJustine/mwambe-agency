<?php

declare(strict_types=1);

namespace Mwambe\Core;

final class Auth
{
    private const SESSION_KEY = 'auth_user_id';

    public function __construct(
        private readonly Database $db,
        private readonly Config $config,
        private readonly Application $app,
    ) {}

    /* ---------------- Session lifecycle ---------------- */

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (bool) $this->config->get('SESSION_SECURE', false);
        $name   = (string) $this->config->get('SESSION_NAME', 'mwambe_session');

        session_name($name);
        session_set_cookie_params([
            'lifetime' => (int) $this->config->get('SESSION_LIFETIME', 7200),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => (string) $this->config->get('SESSION_SAMESITE', 'Lax'),
        ]);

        session_start();

        // Session timeout
        $lifetime = (int) $this->config->get('SESSION_LIFETIME', 7200);
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $lifetime) {
            $this->logout();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }

    public function user(): ?array
    {
        $id = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$id) {
            return null;
        }
        static $cache = null;
        if ($cache !== null && ($cache['id'] ?? null) === (int) $id) {
            return $cache;
        }
        $user = $this->db->selectOne(
            'SELECT id, name, email, is_active, last_login_at FROM users WHERE id = ? AND is_active = 1',
            [(int) $id]
        );
        if ($user === null) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }
        $user['roles'] = $this->rolesFor((int) $user['id']);
        $cache = $user;
        return $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function hasRole(string ...$slugs): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }
        return count(array_intersect($slugs, $user['roles'])) > 0;
    }

    /* ---------------- Login ---------------- */

    public function attempt(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        // Rate limit: 5 failed attempts in last 15 minutes per email OR per IP.
        $window = (int) $this->config->get('LOGIN_LOCKOUT_MINUTES', 15);
        $max    = (int) $this->config->get('LOGIN_MAX_ATTEMPTS', 5);

        $recentFailures = (int) $this->db->selectOne("
            SELECT COUNT(*) AS c FROM login_attempts
            WHERE success = 0
              AND attempted_at > (NOW() - INTERVAL :window MINUTE)
              AND (email = :email OR ip_address = :ip)
        ", ['window' => $window, 'email' => $email, 'ip' => $this->ip()])['c'];

        if ($recentFailures >= $max) {
            $this->recordAttempt($email, false);
            return ['ok' => false, 'error' => 'Too many failed attempts. Please try again in a few minutes.'];
        }

        $user = $this->db->selectOne(
            'SELECT id, password_hash, is_active FROM users WHERE email = ?',
            [$email]
        );

        if ($user === null || (int) $user['is_active'] !== 1) {
            $this->recordAttempt($email, false);
            // Constant-time response — do a dummy verify anyway
            password_verify($password, '$2y$10$' . str_repeat('x', 53));
            return ['ok' => false, 'error' => 'Invalid email or password.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->recordAttempt($email, false);
            return ['ok' => false, 'error' => 'Invalid email or password.'];
        }

        // Rehash if needed
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->db->execute('UPDATE users SET password_hash = ? WHERE id = ?', [$newHash, $user['id']]);
        }

        // Regenerate session ID and log in
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = (int) $user['id'];
        $_SESSION['last_activity'] = time();

        $this->db->execute('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        $this->recordAttempt($email, true);

        $this->app->log('info', 'User logged in', ['user_id' => (int) $user['id'], 'email' => $email]);

        return ['ok' => true];
    }

    public function logout(): void
    {
        $userId = $_SESSION[self::SESSION_KEY] ?? null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        if ($userId) {
            $this->app->log('info', 'User logged out', ['user_id' => $userId]);
        }
    }

    /* ---------------- Helpers ---------------- */

    public function rolesFor(int $userId): array
    {
        $rows = $this->db->select("
            SELECT r.slug FROM roles r
            JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = ?
        ", [$userId]);
        return array_column($rows, 'slug');
    }

    private function recordAttempt(string $email, bool $success): void
    {
        $this->db->execute(
            'INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, ?)',
            [$email, $this->ip(), $success ? 1 : 0]
        );
    }

    private function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}