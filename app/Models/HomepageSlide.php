<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class HomepageSlide
{
    public function __construct(private readonly Database $db) {}

    /** @return array<int,array<string,mixed>> */
    public function active(): array
    {
        return $this->db->select("
            SELECT s.*,
                   m.filename AS media_filename,
                   m.alt_text AS media_alt
            FROM homepage_slides s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.is_active = 1
            ORDER BY s.position ASC
            LIMIT 3
        ");
    }

    public function activeCount(): int
    {
        return (int) $this->db->selectOne(
            'SELECT COUNT(*) AS c FROM homepage_slides WHERE is_active = 1'
        )['c'];
    }
}