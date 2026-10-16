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

final class ProgrammeController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $programmes = $this->db->select("
            SELECT id, title, slug, kind, category, status, workflow_status, is_featured, updated_at
            FROM programmes ORDER BY updated_at DESC
        ");

        return $this->render('admin/programmes/index', [
            'title'       => 'Programmes & Projects',
            'activeNav'   => 'programmes',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Programmes & Projects'],
            ],
            'programmes' => $programmes,
            'flash'      => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/programmes/edit', [
            'title'       => 'New programme',
            'activeNav'   => 'programmes',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Programmes', 'url' => '/admin/programmes'],
                ['label' => 'New programme'],
            ],
            'programme' => $this->emptyProgramme(),
            'errors'    => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data, null);
        if ($errors) {
            return $this->render('admin/programmes/edit', [
                'title'       => 'New programme',
                'activeNav'   => 'programmes',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Programmes', 'url' => '/admin/programmes'],
                    ['label' => 'New programme'],
                ],
                'programme' => $data,
                'errors'    => $errors,
            ]);
        }

        $id = $this->db->insert("
            INSERT INTO programmes
                (slug, kind, title, category, summary, body, objectives, audience, activities,
                 timeline, outcomes, status, start_date, end_date, is_featured, workflow_status,
                 meta_title, meta_description)
            VALUES
                (:slug, :kind, :title, :category, :summary, :body, :objectives, :audience, :activities,
                 :timeline, :outcomes, :status, :start_date, :end_date, :is_featured, :workflow_status,
                 :meta_title, :meta_description)
        ", $data);

        $this->audit->log('programme.create', 'programmes', $id, 'Created programme: ' . $data['title']);
        $this->flash('Programme created.');
        return Response::redirect('/admin/programmes/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $programme = $this->db->selectOne('SELECT * FROM programmes WHERE id = ?', [(int) $id]);
        if ($programme === null) return Response::html('<h1>404</h1>', 404);

        return $this->render('admin/programmes/edit', [
            'title'       => 'Edit: ' . $programme['title'],
            'activeNav'   => 'programmes',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Programmes', 'url' => '/admin/programmes'],
                ['label' => $programme['title']],
            ],
            'programme' => $programme,
            'errors'    => [],
            'flash'     => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM programmes WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        $data = $this->collectInput($request);
        $errors = $this->validate($data, $id);
        if ($errors) {
            return $this->render('admin/programmes/edit', [
                'title'       => 'Edit: ' . $existing['title'],
                'activeNav'   => 'programmes',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Programmes', 'url' => '/admin/programmes'],
                    ['label' => $existing['title']],
                ],
                'programme' => array_merge($existing, $data),
                'errors'    => $errors,
            ]);
        }

        $this->db->execute("
            UPDATE programmes SET
                slug = :slug, kind = :kind, title = :title, category = :category,
                summary = :summary, body = :body, objectives = :objectives,
                audience = :audience, activities = :activities, timeline = :timeline,
                outcomes = :outcomes, status = :status, start_date = :start_date,
                end_date = :end_date, is_featured = :is_featured,
                workflow_status = :workflow_status, meta_title = :meta_title,
                meta_description = :meta_description
            WHERE id = :id
        ", array_merge($data, ['id' => $id]));

        $this->audit->log('programme.update', 'programmes', $id, 'Updated programme: ' . $data['title']);
        $this->flash('Programme updated.');
        return Response::redirect('/admin/programmes/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $programme = $this->db->selectOne('SELECT title FROM programmes WHERE id = ?', [$id]);
        if ($programme === null) return Response::html('<h1>404</h1>', 404);

        $this->db->execute('DELETE FROM programmes WHERE id = ?', [$id]);
        $this->audit->log('programme.delete', 'programmes', $id, 'Deleted programme: ' . $programme['title']);
        $this->flash('Programme deleted.');
        return Response::redirect('/admin/programmes');
    }

    /* ------------- helpers ------------- */

    private function collectInput(Request $request): array
    {
        $jsonField = function (string $field) use ($request): ?string {
            $raw = $request->string($field);
            if ($raw === '') return null;
            $lines = preg_split('/\r\n|\r|\n/', $raw);
            return json_encode(array_values(array_filter(array_map('trim', $lines))));
        };
        return [
            'slug'             => $this->slugify($request->string('slug') ?: $request->string('title')),
            'kind'             => $request->string('kind', 'programme'),
            'title'            => $request->string('title'),
            'category'         => $request->string('category'),
            'summary'          => $request->string('summary'),
            'body'             => $request->string('body'),
            'objectives'       => $jsonField('objectives'),
            'audience'         => $jsonField('audience'),
            'activities'       => $jsonField('activities'),
            'timeline'         => $request->string('timeline'),
            'outcomes'         => $request->string('outcomes'),
            'status'           => $request->string('status', 'proposed'),
            'start_date'       => $request->string('start_date') ?: null,
            'end_date'         => $request->string('end_date') ?: null,
            'is_featured'      => (int) (bool) $request->input('is_featured'),
            'workflow_status'  => $request->string('workflow_status', 'draft'),
            'meta_title'       => $request->string('meta_title'),
            'meta_description' => $request->string('meta_description'),
        ];
    }

    private function validate(array $d, ?int $excludeId): array
    {
        $errors = [];
        if ($d['title'] === '') $errors['title'] = 'Title is required.';
        if ($d['slug'] === '') $errors['slug'] = 'Slug is required.';
        if (!in_array($d['kind'], ['programme','project'], true)) $errors['kind'] = 'Invalid kind.';
        if (!in_array($d['status'], ['proposed','in_development','active','completed','paused','archived'], true)) {
            $errors['status'] = 'Invalid status.';
        }
        if (!in_array($d['workflow_status'], ['draft','published','archived'], true)) {
            $errors['workflow_status'] = 'Invalid workflow status.';
        }
        // slug uniqueness
        $sql = 'SELECT id FROM programmes WHERE slug = ?';
        $params = [$d['slug']];
        if ($excludeId !== null) { $sql .= ' AND id <> ?'; $params[] = $excludeId; }
        if ($this->db->selectOne($sql, $params)) {
            $errors['slug'] = 'That slug is already in use.';
        }
        return $errors;
    }

    private function emptyProgramme(): array
    {
        return [
            'id' => null, 'slug' => '', 'kind' => 'programme', 'title' => '', 'category' => '',
            'summary' => '', 'body' => '', 'objectives' => null, 'audience' => null, 'activities' => null,
            'timeline' => '', 'outcomes' => '', 'status' => 'proposed', 'start_date' => null,
            'end_date' => null, 'is_featured' => 0, 'workflow_status' => 'draft',
            'meta_title' => '', 'meta_description' => '',
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