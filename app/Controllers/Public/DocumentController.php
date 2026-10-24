<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class DocumentController
{
    private const PER_PAGE = 12;

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(
        private readonly Application $app,
        private readonly Database $db,
    ) {}

    public function index(Request $request): Response
    {
        $page = max(1, (int) ($request->query['page'] ?? 1));
        $query = mb_substr($request->string('q'), 0, 100);
        $category = mb_substr($request->string('category'), 0, 80);
        $offset = ($page - 1) * self::PER_PAGE;

        $where = "WHERE workflow_status = 'published' AND is_public = 1";
        $params = [];
        if ($query !== '') {
            $term = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $where .= " AND (
                title LIKE :title ESCAPE '!'
                OR description LIKE :description ESCAPE '!'
                OR category LIKE :category_search ESCAPE '!'
                OR original_name LIKE :original_name ESCAPE '!'
            )";
            $params += [
                'title' => $term,
                'description' => $term,
                'category_search' => $term,
                'original_name' => $term,
            ];
        }
        if ($category !== '') {
            $where .= ' AND category = :category_filter';
            $params['category_filter'] = $category;
        }

        $documents = $this->db->select("
            SELECT title, slug, description, category, original_name, mime_type,
                   size_bytes, download_count, created_at
            FROM documents
            {$where}
            ORDER BY category IS NULL, category ASC, title ASC
            LIMIT " . self::PER_PAGE . ' OFFSET ' . $offset,
            $params
        );
        $total = (int) ($this->db->selectOne(
            "SELECT COUNT(*) AS total FROM documents {$where}",
            $params
        )['total'] ?? 0);
        $categories = $this->db->select("
            SELECT DISTINCT category
            FROM documents
            WHERE workflow_status = 'published' AND is_public = 1
              AND category IS NOT NULL AND category <> ''
            ORDER BY category ASC
        ");

        return $this->render('public/documents/index', [
            'title' => 'Resources & Documents | MWAMBE Agency',
            'documents' => $documents,
            'categories' => array_column($categories, 'category'),
            'category' => $category,
            'query' => $query,
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'total' => $total,
        ]);
    }

    public function download(Request $request, string $slug): Response
    {
        $document = $this->db->selectOne("
            SELECT id, filename, original_name, mime_type
            FROM documents
            WHERE slug = ? AND workflow_status = 'published' AND is_public = 1
            LIMIT 1
        ", [$slug]);

        if ($document === null) {
            return $this->notFound();
        }

        $storageRoot = realpath(BASE_PATH . '/storage/documents');
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $document['filename']);
        $filePath = $storageRoot === false
            ? false
            : realpath($storageRoot . DIRECTORY_SEPARATOR . $relativePath);

        if ($storageRoot === false || $filePath === false
            || !str_starts_with($filePath, $storageRoot . DIRECTORY_SEPARATOR)
            || !is_file($filePath)) {
            $this->app->log('error', 'Published document file is missing or outside its storage directory.', [
                'document_id' => (int) $document['id'],
            ]);
            return $this->notFound();
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            $this->app->log('error', 'Failed to read published document.', [
                'document_id' => (int) $document['id'],
            ]);
            return Response::text('Unable to download this document. Please try again later.', 500);
        }

        $mimeType = (string) $document['mime_type'];
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $this->app->log('warning', 'Published document has an unsupported MIME type.', [
                'document_id' => (int) $document['id'],
            ]);
            $mimeType = 'application/octet-stream';
        }

        $downloadName = basename(str_replace('\\', '/', (string) $document['original_name']));
        $fallbackName = preg_replace('/[^A-Za-z0-9._-]/', '_', $downloadName) ?: 'document';
        $fallbackName = trim(substr($fallbackName, 0, 180), '._-') ?: 'document';

        $this->db->execute(
            'UPDATE documents SET download_count = download_count + 1 WHERE id = ?',
            [(int) $document['id']]
        );

        return new Response($contents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fallbackName
                . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName),
            'Content-Length' => (string) strlen($contents),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app = $this->app;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }

    private function notFound(): Response
    {
        ob_start();
        require BASE_PATH . '/app/Views/errors/404.php';
        return Response::html((string) ob_get_clean(), 404);
    }
}
