<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class NewsRepository
{
    public function __construct(private readonly Database $db) {}

    public function published(int $limit = 12, int $offset = 0, ?string $category = null): array
    {
        $sql = "
            SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                   m.filename AS media_filename, m.alt_text AS media_alt
            FROM news_articles a
            LEFT JOIN news_categories c ON c.id = a.category_id
            LEFT JOIN media m ON m.id = a.media_id
            WHERE a.workflow_status = 'published' AND a.published_at <= NOW()
        ";
        $params = [];
        if ($category) {
            $sql .= ' AND c.slug = :cat';
            $params['cat'] = $category;
        }
        $sql .= ' ORDER BY a.published_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        return $this->db->select($sql, $params);
    }

    public function countPublished(?string $category = null): int
    {
        $sql = "SELECT COUNT(*) AS c FROM news_articles a
                LEFT JOIN news_categories c ON c.id = a.category_id
                WHERE a.workflow_status = 'published' AND a.published_at <= NOW()";
        $params = [];
        if ($category) { $sql .= ' AND c.slug = :cat'; $params['cat'] = $category; }
        return (int) $this->db->selectOne($sql, $params)['c'];
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne("
            SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                   m.filename AS media_filename, m.alt_text AS media_alt,
                   u.name AS author_name
            FROM news_articles a
            LEFT JOIN news_categories c ON c.id = a.category_id
            LEFT JOIN media m ON m.id = a.media_id
            LEFT JOIN users u ON u.id = a.author_id
            WHERE a.slug = ? AND a.workflow_status = 'published'
            LIMIT 1
        ", [$slug]);
    }
}