<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class ImpactRepository
{
    public function __construct(private readonly Database $db) {}

    /** Only verified, public indicators. */
    public function publicIndicators(): array
    {
        return $this->db->select("
            SELECT * FROM impact_indicators
            WHERE verification_status = 'verified' AND is_public = 1
            ORDER BY position ASC
        ");
    }

    /** All indicators (for admin). */
    public function all(): array
    {
        return $this->db->select("
            SELECT i.*, u.name AS reviewer_name
            FROM impact_indicators i
            LEFT JOIN users u ON u.id = i.reviewer_id
            ORDER BY i.position ASC
        ");
    }
}