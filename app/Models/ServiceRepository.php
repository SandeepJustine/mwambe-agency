<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class ServiceRepository
{
    public function __construct(private readonly Database $db) {}

    public function published(): array
    {
        return $this->db->select("
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM services s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.workflow_status = 'published'
            ORDER BY s.position ASC, s.name ASC
        ");
    }

    public function featured(): array
    {
        return $this->db->select("
            SELECT * FROM services WHERE workflow_status = 'published' AND is_featured = 1
            ORDER BY position ASC
        ");
    }
}