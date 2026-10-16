<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Csrf;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Services\PasswordReset;

final class PasswordResetController
{
    public function __construct(
        private readonly Csrf $csrf,
        private readonly PasswordReset $reset,
        private readonly Application $app,
    ) {}

    public function showRequest(Request $request): Response
    {
        return $this->renderReset('admin/auth/forgot', [
            'title'   => 'Reset password',
            'sent'    => false,
            'error'   => null,
            'email'   => '',
        ]);
    }

    public function sendRequest(Request $request): Response
    {
        $email = $request->string('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->renderReset('admin/auth/forgot', [
                'title' => 'Reset password',
                'sent'  => false,
                'error' => 'Please enter a valid email address.',
                'email' => $email,
            ]);
        }
        $result = $this->reset->request($email, $request->ip());
        if (!$result['ok']) {
            return $this->renderReset('admin/auth/forgot', [
                'title' => 'Reset password',
                'sent'  => false,
                'error' => $result['error'] ?? 'Could not process the request.',
                'email' => $email,
            ]);
        }
        return $this->renderReset('admin/auth/forgot', [
            'title' => 'Reset password',
            'sent'  => true,
            'error' => null,
            'email' => $email,
        ]);
    }

    public function showReset(Request $request): Response
    {
        $token = $request->string('token');
        $verify = $this->reset->verify($token);
        if (!$verify['ok']) {
            return $this->renderReset('admin/auth/reset', [
                'title'  => 'Set new password',
                'token'  => '',
                'valid'  => false,
                'error'  => $verify['error'] ?? 'Invalid link.',
                'done'   => false,
            ]);
        }
        return $this->renderReset('admin/auth/reset', [
            'title' => 'Set new password',
            'token' => $token,
            'valid' => true,
            'error' => null,
            'done'  => false,
        ]);
    }

    public function complete(Request $request): Response
    {
        $token = $request->string('token');
        $password = (string) $request->input('password', '');
        $confirm  = (string) $request->input('password_confirm', '');

        if ($password !== $confirm) {
            return $this->renderReset('admin/auth/reset', [
                'title' => 'Set new password',
                'token' => $token,
                'valid' => true,
                'error' => 'Passwords do not match.',
                'done'  => false,
            ]);
        }

        $result = $this->reset->complete($token, $password);
        if (!$result['ok']) {
            return $this->renderReset('admin/auth/reset', [
                'title' => 'Set new password',
                'token' => $token,
                'valid' => true,
                'error' => $result['error'] ?? 'Could not reset the password.',
                'done'  => false,
            ]);
        }

        return $this->renderReset('admin/auth/reset', [
            'title' => 'Set new password',
            'token' => '',
            'valid' => false,
            'error' => null,
            'done'  => true,
        ]);
    }

    private function renderReset(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $csrf = $this->csrf;
        ob_start();
        require $viewPath;
        return Response::html((string) ob_get_clean());
    }
}