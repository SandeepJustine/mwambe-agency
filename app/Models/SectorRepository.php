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
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM sectors s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.workflow_status = 'published'
            ORDER BY s.position ASC, s.name ASC
        ");
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne("
            SELECT * FROM sectors WHERE slug = ? AND workflow_status = 'published'
        ", [$slug]);
    }
}