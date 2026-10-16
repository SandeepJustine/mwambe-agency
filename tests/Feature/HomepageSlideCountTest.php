<?php

declare(strict_types=1);

namespace Mwambe\Tests\Feature;

use Mwambe\Core\Config;
use Mwambe\Core\Database;
use PHPUnit\Framework\TestCase;

/**
 * @group database
 */
final class HomepageSlideEditTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        $config = new Config(__DIR__);
        $this->db = new Database($config);
        try {
            $this->db->pdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not reachable: ' . $e->getMessage());
        }
    }

    public function test_trigger_rejects_a_fourth_active_slide(): void
    {
        $this->expectException(\PDOException::class);
        $this->db->execute("
            INSERT INTO homepage_slides
                (position, layout, headline, supporting, is_active, workflow_status)
            VALUES
                (99, 'editorial', 'Trigger test — must fail', 'Should not persist', 1, 'published')
        ");
    }

    public function test_trigger_rejects_deleting_an_active_slide(): void
    {
        $this->expectException(\PDOException::class);
        $this->db->execute('DELETE FROM homepage_slides WHERE is_active = 1 AND position = 1 LIMIT 1');
    }
}