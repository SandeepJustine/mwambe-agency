<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class TeamRepository
{
    public function __construct(private readonly Database $db) {}

    public function active(): array
    {
        return $this->db->select("
            SELECT t.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM team_members t
            LEFT JOIN media m ON m.id = t.media_id
            WHERE t.is_active = 1
            ORDER BY t.position ASC, t.name ASC
        ");
    }
}
