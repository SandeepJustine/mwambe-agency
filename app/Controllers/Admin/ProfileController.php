<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Services\Audit;

final class ProfileController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Application $app,
    ) {}

    public function show(Request $request): Response
    {
        $user = $this->auth->user();
        return $this->render('admin/profile/show', [
            'title'       => 'My profile',
            'activeNav'   => 'profile',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'My profile'],
            ],
            'user'  => $user,
            'flash' => $this->popFlash(),
            'error' => null,
        ]);
    }

    public function changePassword(Request $request): Response
    {
        $user = $this->auth->user();
        $current = (string) $request->input('current_password', '');
        $new     = (string) $request->input('new_password', '');
        $confirm = (string) $request->input('new_password_confirm', '');

        $error = null;
        if ($current === '' || $new === '') {
            $error = 'All fields are required.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 12) {
            $error = 'New password must be at least 12 characters.';
        } else {
            $stored = $this->db->selectOne('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
            if (!$stored || !password_verify($current, $stored['password_hash'])) {
                $error = 'Current password is incorrect.';
            }
        }

        if ($error !== null) {
            return $this->render('admin/profile/show', [
                'title'       => 'My profile',
                'activeNav'   => 'profile',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'My profile'],
                ],
                'user'  => $user,
                'flash' => null,
                'error' => $error,
            ]);
        }

        $this->db->transaction(function (Database $db) use ($user, $new) {
            $db->execute('UPDATE users SET password_hash = ? WHERE id = ?', [
                password_hash($new, PASSWORD_DEFAULT),
                $user['id'],
            ]);
            // Revoke all sessions except the current one
            $db->execute('DELETE FROM sessions WHERE user_id = ? AND id <> ?', [
                $user['id'],
                session_id(),
            ]);
        });

        $this->audit->log('user.password_change', 'users', (int) $user['id'], 'Changed own password');
        $this->flash('Your password has been changed.');
        return Response::redirect('/admin/profile');
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app  = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }

    private function flash(string $m): void { $_SESSION['_flash'] = $m; }
    private function popFlash(): ?string { $m = $_SESSION['_flash'] ?? null; unset($_SESSION['_flash']); return $m; }
}