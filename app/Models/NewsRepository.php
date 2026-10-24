<?php

declare(strict_types=1);

namespace Mwambe\Models;

use Mwambe\Core\Database;

final class NewsRepository
{
    public function __construct(private readonly Database $db) {}

    public function published(
        int $limit = 12,
        int $offset = 0,
        ?string $category = null,
        ?string $search = null,
    ): array
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
        if ($search !== null && $search !== '') {
            $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $sql .= " AND (
                a.title LIKE :search_title ESCAPE '!'
                OR a.excerpt LIKE :search_excerpt ESCAPE '!'
                OR a.body LIKE :search_body ESCAPE '!'
                OR c.name LIKE :search_category ESCAPE '!'
            )";
            $params['search_title'] = $term;
            $params['search_excerpt'] = $term;
            $params['search_body'] = $term;
            $params['search_category'] = $term;
        }
        $sql .= ' ORDER BY a.published_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        return $this->db->select($sql, $params);
    }

    public function countPublished(?string $category = null, ?string $search = null): int
    {
        $sql = "SELECT COUNT(*) AS c FROM news_articles a
                LEFT JOIN news_categories c ON c.id = a.category_id
                WHERE a.workflow_status = 'published' AND a.published_at <= NOW()";
        $params = [];
        if ($category) { $sql .= ' AND c.slug = :cat'; $params['cat'] = $category; }
        if ($search !== null && $search !== '') {
            $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $sql .= " AND (
                a.title LIKE :search_title ESCAPE '!'
                OR a.excerpt LIKE :search_excerpt ESCAPE '!'
                OR a.body LIKE :search_body ESCAPE '!'
                OR c.name LIKE :search_category ESCAPE '!'
            )";
            $params['search_title'] = $term;
            $params['search_excerpt'] = $term;
            $params['search_body'] = $term;
            $params['search_category'] = $term;
        }
        return (int) $this->db->selectOne($sql, $params)['c'];
    }

    public function publicCategories(): array
    {
        return $this->db->select("
            SELECT c.slug, c.name
            FROM news_categories c
            INNER JOIN news_articles a ON a.category_id = c.id
            WHERE a.workflow_status = 'published' AND a.published_at <= NOW()
            GROUP BY c.id, c.slug, c.name
            ORDER BY c.name ASC
        ");
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