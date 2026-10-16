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

final class EventController
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
        $events = $this->db->select("
            SELECT e.*, m.filename AS media_filename
            FROM events e
            LEFT JOIN media m ON m.id = e.media_id
            ORDER BY e.starts_at DESC
        ");

        return $this->render('admin/events/index', [
            'title'       => 'Events & Activities',
            'activeNav'   => 'events',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Events & Activities'],
            ],
            'events' => $events,
            'flash'  => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/events/edit', [
            'title'       => 'New event',
            'activeNav'   => 'events',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Events & Activities', 'url' => '/admin/events'],
                ['label' => 'New event'],
            ],
            'event'  => $this->emptyEvent(),
            'errors' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data, null);
        if ($errors) {
            return $this->render('admin/events/edit', [
                'title'       => 'New event',
                'activeNav'   => 'events',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Events & Activities', 'url' => '/admin/events'],
                    ['label' => 'New event'],
                ],
                'event'  => array_merge($this->emptyEvent(), $data),
                'errors' => $errors,
            ]);
        }

        $data['media_id'] = $this->maybeUploadImage($request);

        $id = $this->db->insert("
            INSERT INTO events (slug, title, summary, body, location, starts_at, ends_at,
                                media_id, is_featured, workflow_status)
            VALUES (:slug, :title, :summary, :body, :location, :starts_at, :ends_at,
                    :media_id, :is_featured, :workflow_status)
        ", $data);

        $this->audit->log('event.create', 'events', $id, 'Created event: ' . $data['title']);
        $this->flash('Event created.');
        return Response::redirect('/admin/events/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $event = $this->db->selectOne("
            SELECT e.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM events e
            LEFT JOIN media m ON m.id = e.media_id
            WHERE e.id = ?
        ", [(int) $id]);

        if ($event === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        return $this->render('admin/events/edit', [
            'title'       => 'Edit: ' . $event['title'],
            'activeNav'   => 'events',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Events & Activities', 'url' => '/admin/events'],
                ['label' => $event['title']],
            ],
            'event'  => $event,
            'errors' => [],
            'flash'  => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM events WHERE id = ?', [$id]);
        if ($existing === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        $data = $this->collectInput($request);
        $errors = $this->validate($data, $id);
        if ($errors) {
            return $this->render('admin/events/edit', [
                'title'       => 'Edit: ' . $existing['title'],
                'activeNav'   => 'events',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Events & Activities', 'url' => '/admin/events'],
                    ['label' => $existing['title']],
                ],
                'event'  => array_merge($existing, $data),
                'errors' => $errors,
            ]);
        }

        // Optional image replacement
        $mediaId = $this->maybeUploadImage($request);
        if ($mediaId === null) {
            $mediaId = $existing['media_id'];
        }

        // Explicit parameter array — mirrors the SQL placeholders exactly.
        // We deliberately do NOT merge $data wholesale, because it contains
        // no keys the UPDATE doesn't bind but this pattern makes the
        // correspondence obvious and avoids the "Invalid parameter number"
        // bug that bit the news module.
        $updateParams = [
            'slug'            => $data['slug'],
            'title'           => $data['title'],
            'summary'         => $data['summary'],
            'body'            => $data['body'],
            'location'        => $data['location'],
            'starts_at'       => $data['starts_at'],
            'ends_at'         => $data['ends_at'],
            'media_id'        => $mediaId,
            'is_featured'     => $data['is_featured'],
            'workflow_status' => $data['workflow_status'],
            'id'              => $id,
        ];

        $this->db->execute("
            UPDATE events SET
                slug = :slug,
                title = :title,
                summary = :summary,
                body = :body,
                location = :location,
                starts_at = :starts_at,
                ends_at = :ends_at,
                media_id = :media_id,
                is_featured = :is_featured,
                workflow_status = :workflow_status
            WHERE id = :id
        ", $updateParams);

        $this->audit->log('event.update', 'events', $id, 'Updated event: ' . $data['title']);
        $this->flash('Event saved.');
        return Response::redirect('/admin/events/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $event = $this->db->selectOne('SELECT title FROM events WHERE id = ?', [$id]);
        if ($event === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        $this->db->execute('DELETE FROM events WHERE id = ?', [$id]);
        $this->audit->log('event.delete', 'events', $id, 'Deleted event: ' . $event['title']);
        $this->flash('Event deleted.');
        return Response::redirect('/admin/events');
    }

    /* ---------- helpers ---------- */

    private function collectInput(Request $request): array
    {
        $startsAt = str_replace('T', ' ', $request->string('starts_at'));
        if (strlen($startsAt) === 16) $startsAt .= ':00';

        $endsAt = str_replace('T', ' ', $request->string('ends_at'));
        if (strlen($endsAt) === 16) $endsAt .= ':00';

        return [
            'slug'             => $this->slugify($request->string('slug') ?: $request->string('title')),
            'title'            => $request->string('title'),
            'summary'          => $request->string('summary'),
            'body'             => $request->string('body'),
            'location'         => $request->string('location'),
            'starts_at'        => $startsAt,
            'ends_at'          => $endsAt !== '' ? $endsAt : null,
            'is_featured'      => (int) (bool) $request->input('is_featured'),
            'workflow_status'  => $request->string('workflow_status', 'draft'),
            'media_id'         => null,
        ];
    }

    private function validate(array $d, ?int $excludeId): array
    {
        $errors = [];
        if ($d['title'] === '')     $errors['title'] = 'Title is required.';
        if ($d['slug'] === '')      $errors['slug'] = 'Slug is required.';
        if ($d['starts_at'] === '') $errors['starts_at'] = 'Start date and time are required.';
        if (!in_array($d['workflow_status'], ['draft', 'published', 'archived'], true)) {
            $errors['workflow_status'] = 'Invalid status.';
        }
        if ($d['ends_at'] !== null && $d['starts_at'] !== ''
            && strtotime($d['ends_at']) < strtotime($d['starts_at'])) {
            $errors['ends_at'] = 'End time must be after start time.';
        }

        $sql = 'SELECT id FROM events WHERE slug = ?';
        $params = [$d['slug']];
        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        if ($this->db->selectOne($sql, $params)) {
            $errors['slug'] = 'That slug is already in use.';
        }

        return $errors;
    }

    private function maybeUploadImage(Request $request): ?int
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }
        $result = $this->uploader->storeImage(
            $_FILES['image'],
            $request->string('image_alt'),
            null
        );
        return $result['ok'] ? (int) $result['media']['id'] : null;
    }

    private function emptyEvent(): array
    {
        return [
            'id'              => null,
            'slug'            => '',
            'title'           => '',
            'summary'         => '',
            'body'            => '',
            'location'        => '',
            'starts_at'       => date('Y-m-d H:i:s'),
            'ends_at'         => null,
            'media_id'        => null,
            'is_featured'     => 0,
            'workflow_status' => 'draft',
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

    private function flash(string $m): void
    {
        $_SESSION['_flash'] = $m;
    }

    private function popFlash(): ?string
    {
        $m = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $m;
    }
}