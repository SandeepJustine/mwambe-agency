<?php

declare(strict_types=1);

namespace Mwambe\Core\Http\Middleware;

use Mwambe\Core\Auth;
use Mwambe\Core\Http\Middleware;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class RoleMiddleware implements Middleware
{
    /** @var string[] */
    private array $roles;

    public function __construct(private readonly Auth $auth, string ...$roles)
    {
        $this->roles = $roles;
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->auth->hasRole(...$this->roles)) {
            return Response::html(
                '<!doctype html><html><head><title>403 — Forbidden</title>'
                . '<link rel="stylesheet" href="' . e(asset('assets/css/admin.css')) . '"></head>'
                . '<body class="admin"><main style="padding:4rem;font-family:system-ui">'
                . '<h1 style="color:#C29A2C">403 — Forbidden</h1>'
                . '<p>You do not have permission to access this area.</p>'
                . '<p><a href="/admin">← Back to dashboard</a></p>'
                . '</main></body></html>',
                403
            );
        }
        return $next($request);
    }
}