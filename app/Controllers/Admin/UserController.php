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
use Mwambe\Services\Mailer;

final class UserController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Mailer $mailer,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->db->select("
            SELECT u.id, u.name, u.email, u.is_active, u.last_login_at, u.created_at,
                   GROUP_CONCAT(r.slug ORDER BY r.slug SEPARATOR ',') AS role_slugs,
                   GROUP_CONCAT(r.name ORDER BY r.slug SEPARATOR ', ') AS role_names
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            GROUP BY u.id
            ORDER BY u.is_active DESC, u.created_at ASC
        ");

        $allRoles = $this->db->select('SELECT slug, name FROM roles ORDER BY id ASC');

        return $this->render('admin/users/index', [
            'title'       => 'Users & Roles',
            'activeNav'   => 'users',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Users & Roles'],
            ],
            'users'    => $users,
            'allRoles' => $allRoles,
            'flash'    => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        $allRoles = $this->db->select('SELECT slug, name FROM roles ORDER BY id ASC');
        return $this->render('admin/users/edit', [
            'title'       => 'New user',
            'activeNav'   => 'users',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Users & Roles', 'url' => '/admin/users'],
                ['label' => 'New user'],
            ],
            'user'     => ['id' => null, 'name' => '', 'email' => '', 'is_active' => 1, 'roles' => []],
            'allRoles' => $allRoles,
            'errors'   => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $name     = $request->string('name');
        $email    = strtolower($request->string('email'));
        $password = (string) $request->input('password', '');
        $roleSlugs = $this->collectRoles($request);

        $errors = [];
        if ($name === '')  $errors['name'] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if (strlen($password) < 12) $errors['password'] = 'Password must be at least 12 characters.';
        if (empty($roleSlugs)) $errors['roles'] = 'Select at least one role.';

        if ($this->db->selectOne('SELECT id FROM users WHERE email = ?', [$email])) {
            $errors['email'] = 'That email is already registered.';
        }

        // Only super_admin can create super_admin or admin users
        if (!$this->auth->hasRole('super_admin')) {
            if (array_intersect($roleSlugs, ['super_admin', 'admin'])) {
                $errors['roles'] = 'Only a Super Administrator can grant administrative roles.';
            }
        }

        if ($errors) {
            return $this->render('admin/users/edit', [
                'title'       => 'New user',
                'activeNav'   => 'users',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Users & Roles', 'url' => '/admin/users'],
                    ['label' => 'New user'],
                ],
                'user'     => ['id' => null, 'name' => $name, 'email' => $email, 'is_active' => 1, 'roles' => $roleSlugs],
                'allRoles' => $this->db->select('SELECT slug, name FROM roles ORDER BY id ASC'),
                'errors'   => $errors,
            ]);
        }

        $userId = $this->db->transaction(function (Database $db) use ($name, $email, $password, $roleSlugs) {
            $id = $db->insert(
                'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)',
                [$name, $email, password_hash($password, PASSWORD_DEFAULT)]
            );
            foreach ($roleSlugs as $slug) {
                $roleId = (int) $db->selectOne('SELECT id FROM roles WHERE slug = ?', [$slug])['id'];
                $db->execute('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)', [$id, $roleId]);
            }
            return $id;
        });

        $this->audit->log('user.create', 'users', $userId, 'Created user: ' . $email, ['roles' => $roleSlugs]);
        $this->flash('User created.');
        return Response::redirect('/admin/users');
    }

    public function edit(Request $request, string $id): Response
    {
        $user = $this->db->selectOne(
            'SELECT id, name, email, is_active FROM users WHERE id = ?',
            [(int) $id]
        );
        if ($user === null) return Response::html('<h1>404</h1>', 404);

        $user['roles'] = array_column(
            $this->db->select('
                SELECT r.slug FROM roles r
                JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = ?
            ', [(int) $id]),
            'slug'
        );

        return $this->render('admin/users/edit', [
            'title'       => 'Edit: ' . $user['name'],
            'activeNav'   => 'users',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Users & Roles', 'url' => '/admin/users'],
                ['label' => $user['name']],
            ],
            'user'     => $user,
            'allRoles' => $this->db->select('SELECT slug, name FROM roles ORDER BY id ASC'),
            'errors'   => [],
            'flash'    => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM users WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        $name  = $request->string('name');
        $email = strtolower($request->string('email'));
        $isActive = (int) (bool) $request->input('is_active');
        $roleSlugs = $this->collectRoles($request);

        $errors = [];
        if ($name === '')  $errors['name'] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if (empty($roleSlugs)) $errors['roles'] = 'Select at least one role.';

        if ($email !== $existing['email']) {
            if ($this->db->selectOne('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id])) {
                $errors['email'] = 'That email is already registered.';
            }
        }

        // Prevent self-deactivation or self-demotion from super_admin
        if ($id === $this->auth->id()) {
            if ($isActive === 0) $errors['is_active'] = 'You cannot deactivate your own account.';
            if (!in_array('super_admin', $roleSlugs, true) && $this->auth->hasRole('super_admin')) {
                $errors['roles'] = 'You cannot remove your own Super Administrator role.';
            }
        }

        // Only super_admin can create or modify super_admin or admin
        if (!$this->auth->hasRole('super_admin')) {
            $existingRoles = array_column(
                $this->db->select('
                    SELECT r.slug FROM roles r
                    JOIN user_roles ur ON ur.role_id = r.id
                    WHERE ur.user_id = ?
                ', [$id]),
                'slug'
            );
            $adminRoles = ['super_admin', 'admin'];
            if (array_intersect($roleSlugs, $adminRoles) || array_intersect($existingRoles, $adminRoles)) {
                $errors['roles'] = 'Only a Super Administrator can grant or modify administrative roles.';
            }
        }

        if ($errors) {
            return $this->render('admin/users/edit', [
                'title'       => 'Edit: ' . $existing['name'],
                'activeNav'   => 'users',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Users & Roles', 'url' => '/admin/users'],
                    ['label' => $existing['name']],
                ],
                'user'     => array_merge($existing, ['name' => $name, 'email' => $email, 'is_active' => $isActive, 'roles' => $roleSlugs]),
                'allRoles' => $this->db->select('SELECT slug, name FROM roles ORDER BY id ASC'),
                'errors'   => $errors,
            ]);
        }

        $this->db->transaction(function (Database $db) use ($id, $name, $email, $isActive, $roleSlugs) {
            $db->execute(
                'UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?',
                [$name, $email, $isActive, $id]
            );

            $db->execute('DELETE FROM user_roles WHERE user_id = ?', [$id]);
            foreach ($roleSlugs as $slug) {
                $roleId = (int) $db->selectOne('SELECT id FROM roles WHERE slug = ?', [$slug])['id'];
                $db->execute('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)', [$id, $roleId]);
            }

            // If deactivated, revoke all sessions
            if ($isActive === 0) {
                $db->execute('DELETE FROM sessions WHERE user_id = ?', [$id]);
            }
        });

        $this->audit->log('user.update', 'users', $id, 'Updated user: ' . $email, [
            'is_active' => $isActive,
            'roles' => $roleSlugs,
        ]);
        $this->flash('User updated.');
        return Response::redirect('/admin/users/' . $id . '/edit');
    }

    public function forceLogout(Request $request, string $id): Response
    {
        $id = (int) $id;
        $count = $this->db->execute('DELETE FROM sessions WHERE user_id = ?', [$id]);
        $this->audit->log('user.force_logout', 'users', $id, 'Revoked sessions', ['count' => $count]);
        $this->flash('All sessions for that user have been revoked.');
        return Response::redirect('/admin/users/' . $id . '/edit');
    }

    public function sendReset(Request $request, string $id): Response
    {
        $id = (int) $id;
        $user = $this->db->selectOne('SELECT id, name, email FROM users WHERE id = ?', [$id]);
        if ($user === null) return Response::html('<h1>404</h1>', 404);

        $reset = $this->app->container->get(\Mwambe\Services\PasswordReset::class);
        $result = $reset->request($user['email'], $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($result['ok']) {
            $this->audit->log('user.reset_sent', 'users', $id, 'Password reset email sent');
            $this->flash('Password reset email sent to ' . $user['email'] . '.');
        } else {
            $this->flash('Could not send reset: ' . ($result['error'] ?? 'unknown error'));
        }
        return Response::redirect('/admin/users/' . $id . '/edit');
    }

    private function collectRoles(Request $request): array
    {
        $input = $request->input('roles', []);
        if (!is_array($input)) return [];
        $valid = ['super_admin', 'admin', 'editor', 'contributor', 'enquiries'];
        return array_values(array_intersect($input, $valid));
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