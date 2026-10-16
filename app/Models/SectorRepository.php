<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class SectorRepository
{
    public function __construct(private readonly Database $db) {}

    public function published(): array
    {
        return $this->db->select("
            SELECT * FROM sectors WHERE workflow_status = 'published'
            ORDER BY position ASC, name ASC
        ");
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne("
            SELECT * FROM sectors WHERE slug = ? AND workflow_status = 'published'
        ", [$slug]);
    }
}