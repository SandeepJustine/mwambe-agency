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

final class SectorController
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
        $sectors = $this->db->select("
            SELECT s.*, m.filename AS media_filename
            FROM sectors s
            LEFT JOIN media m ON m.id = s.media_id
            ORDER BY s.position ASC, s.name ASC
        ");
        return $this->render('admin/sectors/index', [
            'title'       => 'Sectors',
            'activeNav'   => 'sectors',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Sectors'],
            ],
            'sectors' => $sectors,
            'flash'   => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/sectors/edit', [
            'title'       => 'New sector',
            'activeNav'   => 'sectors',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Sectors', 'url' => '/admin/sectors'],
                ['label' => 'New sector'],
            ],
            'sector' => $this->emptySector(),
            'errors' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data, null);
        if ($errors) {
            return $this->render('admin/sectors/edit', [
                'title'       => 'New sector',
                'activeNav'   => 'sectors',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Sectors', 'url' => '/admin/sectors'],
                    ['label' => 'New sector'],
                ],
                'sector' => $data,
                'errors' => $errors,
            ]);
        }

        $mediaId = $this->maybeUploadImage($request);
        $data['media_id'] = $mediaId;

        $id = $this->db->insert("
            INSERT INTO sectors (slug, name, summary, body, icon_key, media_id, position, is_featured, workflow_status)
            VALUES (:slug, :name, :summary, :body, :icon_key, :media_id, :position, :is_featured, :workflow_status)
        ", $data);

        $this->audit->log('sector.create', 'sectors', $id, 'Created sector: ' . $data['name']);
        $this->flash('Sector created.');
        return Response::redirect('/admin/sectors/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $sector = $this->db->selectOne("
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM sectors s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.id = ?
        ", [(int) $id]);
        if ($sector === null) return Response::html('<h1>404</h1>', 404);

        return $this->render('admin/sectors/edit', [
            'title'       => 'Edit: ' . $sector['name'],
            'activeNav'   => 'sectors',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Sectors', 'url' => '/admin/sectors'],
                ['label' => $sector['name']],
            ],
            'sector' => $sector,
            'errors' => [],
            'flash'  => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM sectors WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        $data = $this->collectInput($request);
        $errors = $this->validate($data, $id);
        if ($errors) {
            return $this->render('admin/sectors/edit', [
                'title'       => 'Edit: ' . $existing['name'],
                'activeNav'   => 'sectors',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Sectors', 'url' => '/admin/sectors'],
                    ['label' => $existing['name']],
                ],
                'sector' => array_merge($existing, $data),
                'errors' => $errors,
            ]);
        }

        $mediaId = $this->maybeUploadImage($request);
        if ($mediaId === null) {
            $mediaId = $existing['media_id'];
        }
        $data['media_id'] = $mediaId;

        $this->db->execute("
            UPDATE sectors SET
                slug = :slug, name = :name, summary = :summary, body = :body,
                icon_key = :icon_key, media_id = :media_id, position = :position,
                is_featured = :is_featured, workflow_status = :workflow_status
            WHERE id = :id
        ", array_merge($data, ['id' => $id]));

        $this->audit->log('sector.update', 'sectors', $id, 'Updated sector: ' . $data['name']);
        $this->flash('Sector updated.');
        return Response::redirect('/admin/sectors/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $sector = $this->db->selectOne('SELECT name FROM sectors WHERE id = ?', [$id]);
        if ($sector === null) return Response::html('<h1>404</h1>', 404);

        $this->db->execute('DELETE FROM sectors WHERE id = ?', [$id]);
        $this->audit->log('sector.delete', 'sectors', $id, 'Deleted sector: ' . $sector['name']);
        $this->flash('Sector deleted.');
        return Response::redirect('/admin/sectors');
    }

    private function collectInput(Request $request): array
    {
        return [
            'slug'             => $this->slugify($request->string('slug') ?: $request->string('name')),
            'name'             => $request->string('name'),
            'summary'          => $request->string('summary'),
            'body'             => $request->string('body'),
            'icon_key'         => $request->string('icon_key'),
            'position'         => (int) $request->input('position', 0),
            'is_featured'      => (int) (bool) $request->input('is_featured'),
            'workflow_status'  => $request->string('workflow_status', 'draft'),
            'media_id'         => null,
        ];
    }

    private function validate(array $d, ?int $excludeId): array
    {
        $errors = [];
        if ($d['name'] === '')    $errors['name'] = 'Name is required.';
        if ($d['slug'] === '')    $errors['slug'] = 'Slug is required.';
        if (!in_array($d['workflow_status'], ['draft','published','archived'], true)) {
            $errors['workflow_status'] = 'Invalid status.';
        }
        $sql = 'SELECT id FROM sectors WHERE slug = ?';
        $params = [$d['slug']];
        if ($excludeId !== null) { $sql .= ' AND id <> ?'; $params[] = $excludeId; }
        if ($this->db->selectOne($sql, $params)) $errors['slug'] = 'That slug is already in use.';
        return $errors;
    }

    private function maybeUploadImage(Request $request): ?int
    {
        if (empty($_FILES['image']['name'])) return null;
        $result = $this->uploader->storeImage(
            $_FILES['image'],
            $request->string('image_alt'),
            null
        );
        return $result['ok'] ? (int) $result['media']['id'] : null;
    }

    private function emptySector(): array
    {
        return [
            'id' => null, 'slug' => '', 'name' => '', 'summary' => '', 'body' => '',
            'icon_key' => '', 'media_id' => null, 'position' => 0,
            'is_featured' => 0, 'workflow_status' => 'draft',
        ];
    }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9-]+/', '-', $s) ?? '';
        return trim($s, '-');
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