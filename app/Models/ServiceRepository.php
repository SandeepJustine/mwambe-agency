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
            SELECT * FROM services WHERE workflow_status = 'published'
            ORDER BY position ASC, name ASC
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