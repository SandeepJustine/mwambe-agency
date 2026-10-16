<?php

declare(strict_types=1);

namespace Mwambe\Tests\Unit;

use Mwambe\Core\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['TEST_STRING']  = 'hello';
        $_ENV['TEST_TRUE']    = 'true';
        $_ENV['TEST_FALSE']   = 'false';
        $_ENV['TEST_INT']     = '42';
    }

    public function test_it_reads_string_values(): void
    {
        $config = new Config(__DIR__);
        $this->assertSame('hello', $config->get('TEST_STRING'));
    }

    public function test_it_coerces_booleans(): void
    {
        $config = new Config(__DIR__);
        $this->assertTrue($config->get('TEST_TRUE'));
        $this->assertFalse($config->get('TEST_FALSE'));
    }

    public function test_it_returns_default_when_missing(): void
    {
        $config = new Config(__DIR__);
        $this->assertSame('fallback', $config->get('DOES_NOT_EXIST', 'fallback'));
    }
}