<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class SearchController
{
    public function __construct(
        private readonly Application $app,
        private readonly Database $db,
    ) {}

    public function index(Request $request): Response
    {
        $q = trim($request->string('q'));
        $results = [];

        if (mb_strlen($q) >= 2) {
            $like = '%' . $q . '%';

            $results['pages'] = $this->db->select("
                SELECT 'page' AS type, p.slug AS url, p.title, p.hero_lede AS excerpt
                FROM pages p
                WHERE p.workflow_status = 'published'
                  AND (p.title LIKE :q OR p.hero_lede LIKE :q OR p.slug LIKE :q)
                LIMIT 10
            ", ['q' => $like]);

            $results['programmes'] = $this->db->select("
                SELECT 'programme' AS type, CONCAT('/our-work/programmes/', p.slug) AS url, p.title, p.summary AS excerpt
                FROM programmes p
                WHERE p.workflow_status = 'published'
                  AND (p.title LIKE :q OR p.summary LIKE :q OR p.body LIKE :q)
                LIMIT 10
            ", ['q' => $like]);

            $results['news'] = $this->db->select("
                SELECT 'article' AS type, CONCAT('/news/', a.slug) AS url, a.title, a.excerpt
                FROM news_articles a
                WHERE a.workflow_status = 'published'
                  AND a.published_at <= NOW()
                  AND (a.title LIKE :q OR a.excerpt LIKE :q OR a.body LIKE :q)
                LIMIT 10
            ", ['q' => $like]);

            $results['sectors'] = $this->db->select("
                SELECT 'sector' AS type, CONCAT('/sectors/', s.slug) AS url, s.name AS title, s.summary AS excerpt
                FROM sectors s
                WHERE s.workflow_status = 'published'
                  AND (s.name LIKE :q OR s.summary LIKE :q)
                LIMIT 5
            ", ['q' => $like]);
        }

        $totalCount = 0;
        foreach ($results as $group) {
            $totalCount += count($group);
        }

        $viewPath = BASE_PATH . '/app/Views/public/search.php';
        extract([
            'title'   => $q ? 'Search results for "' . $q . '" | MWAMBE Agency' : 'Search | MWAMBE Agency',
            'query'   => $q,
            'results' => $results,
            'total'   => $totalCount,
        ], EXTR_SKIP);
        $app = $this->app;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }
}