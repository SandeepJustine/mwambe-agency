<?php

declare(strict_types=1);

namespace Mwambe\Core\Http;

use Mwambe\Core\Container;
use Mwambe\Core\Application;

final class Router
{
    /** @var array<int,array{method:string,path:string,regex:string,params:array,handler:mixed,middleware:array}> */
    private array $routes = [];

    public function __construct(private readonly Container $container) {}

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function add(string $method, string $path, mixed $handler, array $middleware = []): void
    {
        $params = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$params) {
            $params[] = $m[1];
            return '([^/]+)';
        }, $path);

        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'regex'      => '#^' . $regex . '$#',
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method;
        $uri    = $request->uri;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];

            // Build middleware pipeline: outermost first
            $pipeline = array_reverse($route['middleware']);
            $next = fn(Request $req) => $this->invoke($route['handler'], $req, $params);

            foreach ($pipeline as $middlewareClass) {
                $currentNext = $next;
                $next = function (Request $req) use ($middlewareClass, $currentNext) {
                    /** @var \Mwambe\Core\Http\Middleware $mw */
                    $mw = $this->container->get($middlewareClass);
                    return $mw->handle($req, $currentNext);
                };
            }

            return $next($request);
        }

        return $this->notFound($request);
    }

    private function invoke(mixed $handler, Request $request, array $params): Response
    {
        if (is_callable($handler) && !is_string($handler)) {
            $result = $handler($request, ...array_values($params));
            return $this->normalise($result);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = $this->container->get($class);
            $result = $instance->{$method}($request, ...array_values($params));
            return $this->normalise($result);
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = $this->container->get($class);
            $result = $instance->{$method}($request, ...array_values($params));
            return $this->normalise($result);
        }

        throw new \RuntimeException('Unsupported route handler.');
    }

    private function normalise(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if (is_string($result)) {
            return Response::html($result);
        }
        if (is_array($result)) {
            return Response::json($result);
        }
        return Response::text('', 204);
    }

    private function notFound(Request $request): Response
    {
        $view = BASE_PATH . '/app/Views/errors/404.php';
        if (is_file($view)) {
            ob_start();
            require $view;
            return Response::html((string) ob_get_clean(), 404);
        }
        return Response::html('<h1>404 — Not Found</h1>', 404);
    }
}