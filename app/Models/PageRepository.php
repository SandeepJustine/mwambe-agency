<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class PageRepository
{
    public function __construct(private readonly Database $db) {}

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne("
            SELECT p.*, m.filename AS hero_filename, m.alt_text AS hero_alt
            FROM pages p
            LEFT JOIN media m ON m.id = p.hero_media_id
            WHERE p.slug = ? AND p.workflow_status = 'published'
            LIMIT 1
        ", [$slug]);
    }

    public function sectionsFor(int $pageId): array
    {
        return $this->db->select("
            SELECT * FROM page_sections
            WHERE page_id = ? AND is_visible = 1
            ORDER BY position ASC
        ", [$pageId]);
    }
}