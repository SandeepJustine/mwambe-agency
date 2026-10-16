<?php

declare(strict_types=1);

namespace Mwambe\Core;

use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Core\Http\Router;
use Throwable;

final class Application
{
    public readonly string $basePath;
    public readonly Router $router;
    public readonly Request $request;
    public readonly Config $config;
    public readonly Container $container;

    public function __construct(string $basePath)
    {
        $this->basePath  = $basePath;
        $this->config    = new Config($basePath);
        $this->container = new Container();
        $this->registerCoreServices();

        $this->request = Request::capture();
        $this->router  = new Router($this->container);

        $this->configureErrorHandling();
        $this->configureTimezone();

        // Register routes (returns closure that defines them)
        $routesFile = $this->basePath . '/routes/web.php';
        if (is_file($routesFile)) {
            (require $routesFile)($this->router);
        }
        $adminRoutes = $this->basePath . '/routes/admin.php';
        if (is_file($adminRoutes)) {
            (require $adminRoutes)($this->router);
        }
    }

    public function run(): void
    {
        try {
            $response = $this->router->dispatch($this->request);
        } catch (Throwable $e) {
            $response = $this->handleException($e);
        }

        $response->send();
    }

    private function registerCoreServices(): void
    {
        $this->container->set(Container::class, $this->container);
        $this->container->set(Config::class, $this->config);
        $this->container->set(Application::class, $this);
    }

    private function configureTimezone(): void
    {
        date_default_timezone_set($this->config->get('APP_TIMEZONE', 'Africa/Blantyre'));
    }

    private function configureErrorHandling(): void
    {
        $debug = (bool) $this->config->get('APP_DEBUG', false);

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        $logDir = $this->basePath . '/storage/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            ini_set('error_log', $logDir . '/php-error.log');
        }
    }

    private function handleException(Throwable $e): Response
    {
        $debug = (bool) $this->config->get('APP_DEBUG', false);

        $this->log('error', $e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $debug ? $e->getTraceAsString() : null,
        ]);

        if ($debug) {
            return Response::html(
                '<pre style="padding:2rem;font:14px/1.5 ui-monospace,monospace;background:#1a1a1a;color:#f5f5f5">'
                . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8')
                . '</pre>',
                500
            );
        }

        return Response::html(
            '<!doctype html><html><head><title>Something went wrong</title></head><body>'
            . '<main style="max-width:640px;margin:4rem auto;font-family:system-ui;padding:2rem">'
            . '<h1 style="color:#C9A22B">Something went wrong</h1>'
            . '<p>We could not complete your request. Please try again, or contact '
            . '<a href="mailto:mwambeagency@gmail.com">mwambeagency@gmail.com</a>.</p>'
            . '</main></body></html>',
            500
        );
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $logDir = $this->basePath . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $line = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );
        @file_put_contents($logDir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}