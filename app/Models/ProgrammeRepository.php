<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class ProgrammeRepository
{
    public function __construct(private readonly Database $db) {}

    public function published(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $sql = "
            SELECT p.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM programmes p
            LEFT JOIN media m ON m.id = p.media_id
            WHERE p.workflow_status = 'published'
        ";
        $params = [];
        if (!empty($filters['kind'])) {
            $sql .= ' AND p.kind = :kind';
            $params['kind'] = $filters['kind'];
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND p.category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND p.status = :status';
            $params['status'] = $filters['status'];
        }
        $sql .= ' ORDER BY p.is_featured DESC, p.start_date DESC, p.id DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        return $this->db->select($sql, $params);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne("
            SELECT p.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM programmes p
            LEFT JOIN media m ON m.id = p.media_id
            WHERE p.slug = ? AND p.workflow_status = 'published'
            LIMIT 1
        ", [$slug]);
    }

    public function featured(int $limit = 3): array
    {
        return $this->db->select("
            SELECT p.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM programmes p
            LEFT JOIN media m ON m.id = p.media_id
            WHERE p.workflow_status = 'published' AND p.is_featured = 1
            ORDER BY p.id DESC
            LIMIT " . (int) $limit
        );
    }
}