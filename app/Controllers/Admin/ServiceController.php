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

final class ServiceController
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
        $services = $this->db->select("
            SELECT s.*, m.filename AS media_filename
            FROM services s
            LEFT JOIN media m ON m.id = s.media_id
            ORDER BY s.position ASC, s.name ASC
        ");
        return $this->render('admin/services/index', [
            'title'       => 'Services',
            'activeNav'   => 'services',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Services'],
            ],
            'services' => $services,
            'flash'    => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/services/edit', [
            'title'       => 'New service',
            'activeNav'   => 'services',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Services', 'url' => '/admin/services'],
                ['label' => 'New service'],
            ],
            'service' => $this->emptyService(),
            'errors'  => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data, null);
        if ($errors) {
            return $this->render('admin/services/edit', [
                'title'       => 'New service',
                'activeNav'   => 'services',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Services', 'url' => '/admin/services'],
                    ['label' => 'New service'],
                ],
                'service' => $data,
                'errors'  => $errors,
            ]);
        }

        $data['media_id'] = $this->maybeUploadImage($request);

        $id = $this->db->insert("
            INSERT INTO services (slug, name, category, summary, body, icon_key, media_id, position, is_featured, workflow_status)
            VALUES (:slug, :name, :category, :summary, :body, :icon_key, :media_id, :position, :is_featured, :workflow_status)
        ", $data);

        $this->audit->log('service.create', 'services', $id, 'Created service: ' . $data['name']);
        $this->flash('Service created.');
        return Response::redirect('/admin/services/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $service = $this->db->selectOne("
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM services s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.id = ?
        ", [(int) $id]);
        if ($service === null) return Response::html('<h1>404</h1>', 404);

        return $this->render('admin/services/edit', [
            'title'       => 'Edit: ' . $service['name'],
            'activeNav'   => 'services',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Services', 'url' => '/admin/services'],
                ['label' => $service['name']],
            ],
            'service' => $service,
            'errors'  => [],
            'flash'   => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM services WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        $data = $this->collectInput($request);
        $errors = $this->validate($data, $id);
        if ($errors) {
            return $this->render('admin/services/edit', [
                'title'       => 'Edit: ' . $existing['name'],
                'activeNav'   => 'services',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Services', 'url' => '/admin/services'],
                    ['label' => $existing['name']],
                ],
                'service' => array_merge($existing, $data),
                'errors'  => $errors,
            ]);
        }

        $mediaId = $this->maybeUploadImage($request);
        if ($mediaId === null) $mediaId = $existing['media_id'];
        $data['media_id'] = $mediaId;

        $this->db->execute("
            UPDATE services SET
                slug = :slug, name = :name, category = :category, summary = :summary,
                body = :body, icon_key = :icon_key, media_id = :media_id,
                position = :position, is_featured = :is_featured, workflow_status = :workflow_status
            WHERE id = :id
        ", array_merge($data, ['id' => $id]));

        $this->audit->log('service.update', 'services', $id, 'Updated service: ' . $data['name']);
        $this->flash('Service updated.');
        return Response::redirect('/admin/services/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $service = $this->db->selectOne('SELECT name FROM services WHERE id = ?', [$id]);
        if ($service === null) return Response::html('<h1>404</h1>', 404);

        $this->db->execute('DELETE FROM services WHERE id = ?', [$id]);
        $this->audit->log('service.delete', 'services', $id, 'Deleted service: ' . $service['name']);
        $this->flash('Service deleted.');
        return Response::redirect('/admin/services');
    }

    private function collectInput(Request $request): array
    {
        return [
            'slug'             => $this->slugify($request->string('slug') ?: $request->string('name')),
            'name'             => $request->string('name'),
            'category'         => $request->string('category'),
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
        if ($d['name'] === '') $errors['name'] = 'Name is required.';
        if ($d['slug'] === '') $errors['slug'] = 'Slug is required.';
        if (!in_array($d['workflow_status'], ['draft','published','archived'], true)) {
            $errors['workflow_status'] = 'Invalid status.';
        }
        $sql = 'SELECT id FROM services WHERE slug = ?';
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

    private function emptyService(): array
    {
        return [
            'id' => null, 'slug' => '', 'name' => '', 'category' => '', 'summary' => '',
            'body' => '', 'icon_key' => '', 'media_id' => null, 'position' => 0,
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