<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Services\Audit;
use Mwambe\Services\MediaUploader;

final class MediaController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly MediaUploader $uploader,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $q       = trim($request->string('q'));
        $page    = max(1, (int) ($request->query['page'] ?? 1));
        $perPage = 24;
        $offset  = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($q !== '') {
            $where = 'WHERE m.original_name LIKE :q OR m.alt_text LIKE :q OR m.caption LIKE :q';
            $params['q'] = '%' . $q . '%';
        }

        $total = (int) $this->db->selectOne(
            "SELECT COUNT(*) AS c FROM media m {$where}",
            $params
        )['c'];

        $media = $this->db->select("
            SELECT m.*, u.name AS uploaded_by_name
            FROM media m
            LEFT JOIN users u ON u.id = m.uploaded_by
            {$where}
            ORDER BY m.created_at DESC
            LIMIT " . (int) $perPage . " OFFSET " . (int) $offset , $params);

        // Count references for each media item (across tables we know reference it)
        foreach ($media as &$m) {
            $refs = 0;
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM homepage_slides WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM pages WHERE hero_media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM programmes WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM sectors WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM services WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM news_articles WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM events WHERE media_id = ?', [(int) $m['id']])['c'];
            $refs += (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM team_members WHERE media_id = ?', [(int) $m['id']])['c'];
            $m['reference_count'] = $refs;
        }
        unset($m);

        return $this->render('admin/media/index', [
            'title'       => 'Media Library',
            'activeNav'   => 'media',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Media Library'],
            ],
            'media'    => $media,
            'query'    => $q,
            'page'     => $page,
            'perPage'  => $perPage,
            'total'    => $total,
            'flash'    => $this->popFlash(),
        ]);
    }

    public function store(Request $request): Response
    {
        if (empty($_FILES['image']['name'])) {
            $this->flash('Please select an image.');
            return Response::redirect('/admin/media');
        }

        $result = $this->uploader->storeImage(
            $_FILES['image'],
            $request->string('alt_text'),
            $request->string('caption')
        );

        if (!$result['ok']) {
            $this->flash('Upload failed: ' . ($result['error'] ?? 'unknown'));
            return Response::redirect('/admin/media');
        }

        $this->audit->log('media.upload', 'media', (int) $result['media']['id'], 'Uploaded: ' . $result['media']['original_name']);
        $this->flash('Image uploaded.');
        return Response::redirect('/admin/media');
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $media = $this->db->selectOne('SELECT id FROM media WHERE id = ?', [$id]);
        if ($media === null) return Response::html('<h1>404</h1>', 404);

        $alt     = $request->string('alt_text');
        $caption = $request->string('caption');

        $this->db->execute(
            'UPDATE media SET alt_text = ?, caption = ? WHERE id = ?',
            [$alt ?: null, $caption ?: null, $id]
        );

        $this->audit->log('media.update', 'media', $id, 'Updated metadata');
        $this->flash('Metadata saved.');
        return Response::redirect('/admin/media');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $media = $this->db->selectOne('SELECT * FROM media WHERE id = ?', [$id]);
        if ($media === null) return Response::html('<h1>404</h1>', 404);

        // Safety: refuse to delete if any content still references it
        $refs = 0;
        $tables = [
            ['homepage_slides', 'media_id'],
            ['pages', 'hero_media_id'],
            ['programmes', 'media_id'],
            ['sectors', 'media_id'],
            ['services', 'media_id'],
            ['news_articles', 'media_id'],
            ['events', 'media_id'],
            ['team_members', 'media_id'],
        ];
        foreach ($tables as [$table, $column]) {
            $refs += (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM {$table} WHERE {$column} = ?",
                [$id]
            )['c'];
        }

        if ($refs > 0) {
            $this->flash("Cannot delete: this image is still in use in {$refs} place(s). Remove it from content first.");
            return Response::redirect('/admin/media');
        }

        // Delete the file from disk
        $path = BASE_PATH . '/public/uploads/' . $media['filename'];
        if (is_file($path)) @unlink($path);

        $this->db->execute('DELETE FROM media WHERE id = ?', [$id]);
        $this->audit->log('media.delete', 'media', $id, 'Deleted: ' . $media['original_name']);
        $this->flash('Image deleted.');
        return Response::redirect('/admin/media');
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app  = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }

    private function flash(string $m): void { $_SESSION['_flash'] = $m; }
    private function popFlash(): ?string { $m = $_SESSION['_flash'] ?? null; unset($_SESSION['_flash']); return $m; }
}