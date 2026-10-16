<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class EventRepository
{
    public function __construct(private readonly Database $db) {}

    public function upcoming(int $limit = 5): array
    {
        return $this->db->select("
            SELECT e.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM events e
            LEFT JOIN media m ON m.id = e.media_id
            WHERE e.workflow_status = 'published' AND e.starts_at >= NOW()
            ORDER BY e.starts_at ASC
            LIMIT " . (int) $limit
        );
    }
}