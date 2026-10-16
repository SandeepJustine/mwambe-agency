<?php

declare(strict_types=1);

namespace Mwambe\Core;

final class Config
{
    private array $values = [];

    public function __construct(string $basePath)
    {
        $this->values = $_ENV + $_SERVER;

        // Also read PHP-style names from .env (APP_NAME etc.) as-is.
        foreach ($_ENV as $k => $v) {
            if (is_string($v)) {
                $this->values[$k] = $v;
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->values)) {
            return $default;
        }
        $value = $this->values[$key];
        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }

    public function require(string $key): mixed
    {
        $value = $this->get($key);
        if ($value === null || $value === '') {
            throw new \RuntimeException("Missing required config key: {$key}");
        }
        return $value;
    }
}