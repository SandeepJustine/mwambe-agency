<?php

declare(strict_types=1);

namespace Mwambe\Tests\Feature;

use Mwambe\Core\Auth;
use Mwambe\Core\Application;
use PHPUnit\Framework\TestCase;

/**
 * @group database
 */
final class AuthTest extends TestCase
{
    private Application $app;
    private Auth $auth;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $this->app = new Application(dirname(__DIR__, 2));
        try {
            $this->app->container->get(\Mwambe\Core\Database::class)->pdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not reachable: ' . $e->getMessage());
        }
        $this->auth = $this->app->container->get(Auth::class);
        $_SESSION = [];
    }

    public function test_unknown_email_fails_login(): void
    {
        $result = $this->auth->attempt('definitely-not-a-user@example.test', 'whatever');
        $this->assertFalse($result['ok']);
    }

    public function test_wrong_password_fails_login(): void
    {
        // Create a throwaway user in the DB
        $db = $this->app->container->get(\Mwambe\Core\Database::class);
        $db->execute('DELETE FROM users WHERE email = ?', ['test-auth@example.test']);
        $db->execute(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)',
            ['Test User', 'test-auth@example.test', password_hash('CorrectHorse!23', PASSWORD_DEFAULT)]
        );

        $result = $this->auth->attempt('test-auth@example.test', 'wrong');
        $this->assertFalse($result['ok']);

        $result = $this->auth->attempt('test-auth@example.test', 'CorrectHorse!23');
        $this->assertTrue($result['ok']);

        $db->execute('DELETE FROM users WHERE email = ?', ['test-auth@example.test']);
    }

    public function test_rate_limiting_blocks_repeated_failures(): void
    {
        $db = $this->app->container->get(\Mwambe\Core\Database::class);
        $db->execute('DELETE FROM login_attempts WHERE email = ?', ['brute@example.test']);

        for ($i = 0; $i < 6; $i++) {
            $this->auth->attempt('brute@example.test', 'bad');
        }

        $result = $this->auth->attempt('brute@example.test', 'bad');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsStringIgnoringCase('too many', $result['error'] ?? '');

        $db->execute('DELETE FROM login_attempts WHERE email = ?', ['brute@example.test']);
    }
}