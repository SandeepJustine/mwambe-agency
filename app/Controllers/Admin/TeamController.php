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

final class TeamController
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
        $members = $this->db->select("
            SELECT t.*, m.filename AS media_filename
            FROM team_members t
            LEFT JOIN media m ON m.id = t.media_id
            ORDER BY t.position ASC, t.name ASC
        ");

        return $this->render('admin/team/index', [
            'title' => 'Our Team',
            'activeNav' => 'team',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Our Team'],
            ],
            'members' => $members,
            'flash' => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->editView('New team member', $this->emptyMember(), []);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data);
        if (empty($_FILES['image']['name'])) {
            $errors['image'] = 'A profile picture is required.';
        }
        if ($errors) {
            return $this->editView('New team member', $data, $errors);
        }

        $upload = $this->uploadImage($request, $data['name']);
        if ($upload['error'] !== null) {
            return $this->editView('New team member', $data, ['image' => $upload['error']]);
        }
        $data['media_id'] = $upload['media_id'];

        $id = $this->db->insert("
            INSERT INTO team_members (name, position_title, bio, media_id, position, is_active)
            VALUES (:name, :position_title, :bio, :media_id, :position, :is_active)
        ", $data);
        $this->audit->log('team_member.create', 'team_members', $id, 'Created team member: ' . $data['name']);
        $this->flash('Team member added.');
        return Response::redirect('/admin/team/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $member = $this->db->selectOne("
            SELECT t.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM team_members t
            LEFT JOIN media m ON m.id = t.media_id
            WHERE t.id = ?
        ", [(int) $id]);
        if ($member === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        return $this->editView('Edit: ' . $member['name'], $member, [], $this->popFlash());
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM team_members WHERE id = ?', [$id]);
        if ($existing === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        $data = $this->collectInput($request);
        $errors = $this->validate($data);
        $mediaId = $existing['media_id'];
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->uploadImage($request, $data['name']);
            if ($upload['error'] !== null) {
                $errors['image'] = $upload['error'];
            } else {
                $mediaId = $upload['media_id'];
            }
        }
        if ($errors) {
            return $this->editView('Edit: ' . $existing['name'], array_merge($existing, $data), $errors);
        }

        $this->db->execute("
            UPDATE team_members
            SET name = :name, position_title = :position_title, bio = :bio,
                media_id = :media_id, position = :position, is_active = :is_active
            WHERE id = :id
        ", array_merge($data, ['media_id' => $mediaId, 'id' => $id]));
        $this->audit->log('team_member.update', 'team_members', $id, 'Updated team member: ' . $data['name']);
        $this->flash('Team member updated.');
        return Response::redirect('/admin/team/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $member = $this->db->selectOne('SELECT name FROM team_members WHERE id = ?', [$id]);
        if ($member === null) {
            return Response::html('<h1>404</h1>', 404);
        }

        $this->db->execute('DELETE FROM team_members WHERE id = ?', [$id]);
        $this->audit->log('team_member.delete', 'team_members', $id, 'Removed team member: ' . $member['name']);
        $this->flash('Team member removed.');
        return Response::redirect('/admin/team');
    }

    private function collectInput(Request $request): array
    {
        return [
            'name' => $request->string('name'),
            'position_title' => $request->string('position_title'),
            'bio' => $request->string('bio'),
            'position' => max(0, min(999, (int) $request->input('position', 0))),
            'is_active' => (int) (bool) $request->input('is_active'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($data['position_title'] === '') {
            $errors['position_title'] = 'Position is required.';
        }
        return $errors;
    }

    /** @return array{media_id:?int,error:?string} */
    private function uploadImage(Request $request, string $name): array
    {
        $result = $this->uploader->storeImage(
            $_FILES['image'],
            $request->string('image_alt') ?: $name . ' profile picture',
            null
        );
        if (!$result['ok']) {
            return ['media_id' => null, 'error' => $result['error'] ?? 'The profile picture could not be uploaded.'];
        }
        return ['media_id' => (int) $result['media']['id'], 'error' => null];
    }

    private function emptyMember(): array
    {
        return [
            'id' => null,
            'name' => '',
            'position_title' => '',
            'bio' => '',
            'media_id' => null,
            'media_filename' => null,
            'media_alt' => null,
            'position' => 0,
            'is_active' => 1,
        ];
    }

    private function editView(string $title, array $member, array $errors, ?string $flash = null): Response
    {
        return $this->render('admin/team/edit', [
            'title' => $title,
            'activeNav' => 'team',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Our Team', 'url' => '/admin/team'],
                ['label' => $title],
            ],
            'member' => array_merge($this->emptyMember(), $member),
            'errors' => $errors,
            'flash' => $flash,
        ]);
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }

    private function flash(string $message): void
    {
        $_SESSION['_flash'] = $message;
    }

    private function popFlash(): ?string
    {
        $message = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $message;
    }
}
