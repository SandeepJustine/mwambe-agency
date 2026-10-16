<?php

declare(strict_types=1);

namespace Mwambe\Core;

use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Core\Http\Router;
use Mwambe\Services\Audit;
use Mwambe\Services\MediaUploader;
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

        // Make the app globally accessible for the `config()` helper
        $GLOBALS['__mwambe_app'] = $this;

        $this->request = Request::capture();

        // Start session early (safe for all routes)
        $this->container->get(Auth::class)->startSession();

        $this->router = new Router($this->container);

        $this->configureErrorHandling();
        $this->configureTimezone();

        foreach (['web', 'admin'] as $file) {
            $routes = $this->basePath . '/routes/' . $file . '.php';
            if (is_file($routes)) {
                (require $routes)($this->router);
            }
        }
    }

    public function run(): void
    {
        $this->trackVisit();

        try {
            $response = $this->router->dispatch($this->request);
        } catch (Throwable $e) {
            $response = $this->handleException($e);
        }
        $response->send();
    }

    private function trackVisit(): void
    {
        try {
            $method = strtoupper($this->request->method());
            if ($method !== 'GET') return;
            $path = $this->request->path();
            if (str_starts_with($path, '/admin') || str_starts_with($path, '/assets')) return;
            if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|woff2?|ttf|map)$/i', $path)) return;

            $db = $this->container->get(Database::class);
            $db->execute(
                'INSERT INTO page_views (path, ip_address, user_agent) VALUES (?, ?, ?)',
                [$path, $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]
            );
        } catch (Throwable $e) {
            // tracking is best-effort; never break requests
        }
    }

    private function registerCoreServices(): void
    {
        $this->container->set(Container::class, $this->container);
        $this->container->set(Config::class, $this->config);
        $this->container->set(Application::class, $this);

        $this->container->bind(Database::class, fn () => new Database($this->config));
        $this->container->bind(Csrf::class,     fn ($c) => new Csrf($c->get(Config::class)));
        $this->container->bind(Auth::class,     fn ($c) => new Auth(
            $c->get(Database::class),
            $c->get(Config::class),
            $this
        ));
        $this->container->bind(Audit::class,    fn ($c) => new Audit(
            $c->get(Database::class),
            $c->get(Auth::class)
        ));
        $this->container->bind(MediaUploader::class, fn ($c) => new MediaUploader(
            $c->get(Database::class),
            $c->get(Config::class),
            $c->get(Auth::class)
        ));
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
            . '<h1 style="color:#C29A2C">Something went wrong</h1>'
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