<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escape a value for safe HTML output. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        /** @var \Mwambe\Core\Application $app */
        $app = $GLOBALS['__mwambe_app'] ?? null;
        return $app?->config->get($key, $default) ?? $default;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $file = BASE_PATH . '/public' . $path;
        $version = is_file($file) ? (string) filemtime($file) : '1';
        return $path . '?v=' . $version;
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $base = rtrim((string) config('APP_URL', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}