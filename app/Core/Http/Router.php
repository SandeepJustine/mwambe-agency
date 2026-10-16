<?php

declare(strict_types=1);

namespace Mwambe\Core\Http;

use Mwambe\Core\Container;

final class Router
{
    /**
     * @var array<int,array{
     *     method:string,path:string,regex:string,params:array,
     *     handler:mixed,middleware:array<int,array{class:string,args:array}>
     * }>
     */
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

    /**
     * @param array $middleware Array of either:
     *   - 'ClassName'                          → no args
     *   - ['ClassName', ['arg1', 'arg2']]      → args passed to middleware constructor
     */
    public function add(string $method, string $path, mixed $handler, array $middleware = []): void
    {
        $params = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$params) {
            $params[] = $m[1];
            return '([^/]+)';
        }, $path);

        $normalisedMiddleware = [];
        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                $normalisedMiddleware[] = ['class' => $mw, 'args' => []];
            } elseif (is_array($mw) && isset($mw[0])) {
                $normalisedMiddleware[] = ['class' => $mw[0], 'args' => $mw[1] ?? []];
            }
        }

        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'regex'      => '#^' . $regex . '$#',
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => $normalisedMiddleware,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) continue;
            if (!preg_match($route['regex'], $request->uri, $matches)) continue;

            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];

            $next = fn(Request $req) => $this->invoke($route['handler'], $req, $params);

            foreach (array_reverse($route['middleware']) as $mw) {
                $currentNext = $next;
                $class = $mw['class'];
                $args  = $mw['args'];
                $next = function (Request $req) use ($class, $args, $currentNext) {
                    $instance = $this->container->getWithArgs($class, $args);
                    return $instance->handle($req, $currentNext);
                };
            }

            return $next($request);
        }

        return $this->notFound($request);
    }

    private function invoke(mixed $handler, Request $request, array $params): Response
    {
        if (is_callable($handler) && !is_string($handler)) {
            return $this->normalise($handler($request, ...array_values($params)));
        }
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = $this->container->get($class);
            return $this->normalise($instance->{$method}($request, ...array_values($params)));
        }
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = $this->container->get($class);
            return $this->normalise($instance->{$method}($request, ...array_values($params)));
        }
        throw new \RuntimeException('Unsupported route handler.');
    }

    private function normalise(mixed $result): Response
    {
        if ($result instanceof Response) return $result;
        if (is_string($result)) return Response::html($result);
        if (is_array($result)) return Response::json($result);
        return Response::text('', 204);
    }

    private function notFound(Request $request): Response
    {
        ob_start();
        require BASE_PATH . '/app/Views/errors/404.php';
        return Response::html((string) ob_get_clean(), 404);
    }
}