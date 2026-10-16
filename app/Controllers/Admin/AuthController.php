<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class AuthController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Application $app,
    ) {}

    public function showLogin(Request $request): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/admin');
        }
        return $this->renderLogin(null, null);
    }

    public function login(Request $request): Response
    {
        $email    = $request->string('email');
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return $this->renderLogin('Please enter both email and password.', $email);
        }

        $result = $this->auth->attempt($email, $password);
        if (!$result['ok']) {
            return $this->renderLogin($result['error'] ?? 'Login failed.', $email);
        }

        $next = $request->query['next'] ?? '/admin';
        // Only allow internal redirects
        if (!is_string($next) || !str_starts_with($next, '/admin')) {
            $next = '/admin';
        }
        return Response::redirect($next);
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return Response::redirect('/admin/login');
    }

    private function renderLogin(?string $error, ?string $email): Response
    {
        ob_start();
        $csrf = $this->csrf;
        require BASE_PATH . '/app/Views/admin/auth/login.php';
        return Response::html((string) ob_get_clean());
    }
}