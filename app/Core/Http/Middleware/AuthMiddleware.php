<?php

declare(strict_types=1);

namespace Mwambe\Core\Http\Middleware;

use Mwambe\Core\Auth;
use Mwambe\Core\Http\Middleware;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class AuthMiddleware implements Middleware
{
    public function __construct(private readonly Auth $auth) {}

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->auth->check()) {
            $target = '/admin/login';
            if (!str_starts_with($request->uri, '/admin/login')) {
                $target .= '?next=' . urlencode($request->uri);
            }
            return Response::redirect($target);
        }
        return $next($request);
    }
}