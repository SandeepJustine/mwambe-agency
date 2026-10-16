<?php

declare(strict_types=1);

namespace Mwambe\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public function __construct(private readonly Config $config) {}

    public function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public function field(): string
    {
        return '<input type="hidden" name="' . $this->config->get('CSRF_TOKEN_NAME', '_csrf') . '" value="' . e($this->token()) . '">';
    }

    public function verify(?string $token): bool
    {
        if (empty($_SESSION[self::KEY]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[self::KEY], $token);
    }
}