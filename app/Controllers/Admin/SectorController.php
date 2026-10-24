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
    private ?string $uploadError = null;

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
        $pageSection = $this->db->selectOne("
            SELECT s.*
            FROM pages p
            LEFT JOIN page_sections s
                ON s.page_id = p.id AND s.section_type = 'rich_text'
            WHERE p.slug = 'sectors'
            ORDER BY s.position ASC
            LIMIT 1
        ");
        if ($pageSection !== null) {
            $sectionData = $pageSection['data'] ? json_decode((string) $pageSection['data'], true) : [];
            $pageSection['cta_label'] = is_array($sectionData) ? ($sectionData['cta_label'] ?? 'Explore a partnership') : 'Explore a partnership';
            $pageSection['cta_url'] = is_array($sectionData) ? ($sectionData['cta_url'] ?? '/contact') : '/contact';
        }

        return $this->render('admin/sectors/index', [
            'title'       => 'Sectors',
            'activeNav'   => 'sectors',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Sectors'],
            ],
            'sectors' => $sectors,
            'pageSection' => $pageSection,
            'flash'   => $this->popFlash(),
        ]);
    }

    public function updatePageSection(Request $request): Response
    {
        $page = $this->db->selectOne("SELECT id FROM pages WHERE slug = 'sectors' LIMIT 1");
        if ($page === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        $data = [
            'eyebrow' => $request->string('section_eyebrow'),
            'heading' => $request->string('section_heading'),
            'body' => $request->string('section_body'),
            'cta_label' => $request->string('cta_label'),
            'cta_url' => $request->string('cta_url'),
            'is_visible' => (int) (bool) $request->input('section_visible'),
        ];
        $errors = [];
        if ($data['cta_label'] !== '' && $data['cta_url'] === '') {
            $errors[] = 'Enter a destination for the section button.';
        } elseif ($data['cta_url'] !== '' && !preg_match('#^(?:/(?!/)|https?://)#i', $data['cta_url'])) {
            $errors[] = 'The button destination must be a site path or an http(s) URL.';
        }
        if (mb_strlen($data['eyebrow']) > 120 || mb_strlen($data['heading']) > 190 || mb_strlen($data['cta_label']) > 80 || mb_strlen($data['cta_url']) > 500) {
            $errors[] = 'One or more fields exceed their maximum length.';
        }

        $section = $this->db->selectOne("
            SELECT id FROM page_sections
            WHERE page_id = ? AND section_type = 'rich_text'
            ORDER BY position ASC
            LIMIT 1
        ", [(int) $page['id']]);
        $sectionData = json_encode([
            'cta_label' => $data['cta_label'],
            'cta_url' => $data['cta_url'],
        ], JSON_UNESCAPED_SLASHES);

        if ($errors) {
            $this->flash(implode(' ', $errors));
            return Response::redirect('/admin/sectors');
        }

        if ($section !== null) {
            $this->db->execute("
                UPDATE page_sections
                SET eyebrow = ?, heading = ?, body = ?, data = ?, is_visible = ?
                WHERE id = ?
            ", [
                $data['eyebrow'] ?: null,
                $data['heading'] ?: null,
                $data['body'] ?: null,
                $sectionData,
                $data['is_visible'],
                (int) $section['id'],
            ]);
            $sectionId = (int) $section['id'];
        } else {
            $sectionId = $this->db->insert("
                INSERT INTO page_sections (page_id, position, section_type, eyebrow, heading, body, data, is_visible)
                VALUES (?, 1, 'rich_text', ?, ?, ?, ?, ?)
            ", [
                (int) $page['id'],
                $data['eyebrow'] ?: null,
                $data['heading'] ?: null,
                $data['body'] ?: null,
                $sectionData,
                $data['is_visible'],
            ]);
        }

        $this->audit->log('sector_page.update', 'page_sections', $sectionId, 'Updated sectors page introduction');
        $this->flash('Sectors page section updated.');
        return Response::redirect('/admin/sectors');
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
        if ($this->uploadError !== null) {
            return $this->render('admin/sectors/edit', [
                'title' => 'New sector',
                'activeNav' => 'sectors',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Sectors', 'url' => '/admin/sectors'],
                    ['label' => 'New sector'],
                ],
                'sector' => $data,
                'errors' => ['image' => $this->uploadError],
            ]);
        }
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
        if ($this->uploadError !== null) {
            return $this->render('admin/sectors/edit', [
                'title' => 'Edit: ' . $existing['name'],
                'activeNav' => 'sectors',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Sectors', 'url' => '/admin/sectors'],
                    ['label' => $existing['name']],
                ],
                'sector' => array_merge($existing, $data),
                'errors' => ['image' => $this->uploadError],
            ]);
        }
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
        $this->uploadError = null;
        if (empty($_FILES['image']['name'])) return null;
        $result = $this->uploader->storeImage(
            $_FILES['image'],
            $request->string('image_alt'),
            null
        );
        if (!$result['ok']) {
            $this->uploadError = $result['error'] ?? 'The sector image could not be uploaded.';
            return null;
        }
        return (int) $result['media']['id'];
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