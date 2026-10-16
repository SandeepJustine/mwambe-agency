<?php

declare(strict_types=1);

namespace Mwambe\Core\Http\Middleware;

use Mwambe\Core\Csrf;
use Mwambe\Core\Http\Middleware;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class CsrfMiddleware implements Middleware
{
    public function __construct(private readonly Csrf $csrf, private readonly \Mwambe\Core\Config $config) {}

    public function handle(Request $request, callable $next): Response
    {
        if ($request->method === 'POST') {
            $field = (string) $this->config->get('CSRF_TOKEN_NAME', '_csrf');
            $token = $request->body[$field] ?? $request->headers['x-csrf-token'] ?? null;
            if (!$this->csrf->verify(is_string($token) ? $token : null)) {
                return Response::html('<h1>419 — Session expired</h1><p>Please refresh and try again.</p>', 419);
            }
        }
        return $next($request);
    }
}