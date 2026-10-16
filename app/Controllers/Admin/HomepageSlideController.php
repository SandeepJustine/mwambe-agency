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

final class HomepageSlideController
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
        $slides = $this->db->select("
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM homepage_slides s
            LEFT JOIN media m ON m.id = s.media_id
            ORDER BY s.position ASC
        ");

        return $this->render('admin/homepage/slides/index', [
            'title'       => 'Homepage — Hero Slider',
            'activeNav'   => 'slides',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Hero Slider'],
            ],
            'slides' => $slides,
            'flash'  => $this->popFlash(),
        ]);
    }

    public function edit(Request $request, string $id): Response
    {
        $slide = $this->db->selectOne("
            SELECT s.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM homepage_slides s
            LEFT JOIN media m ON m.id = s.media_id
            WHERE s.id = ?
        ", [(int) $id]);

        if ($slide === null) {
            return Response::html('<h1>404 — Slide not found</h1>', 404);
        }

        return $this->render('admin/homepage/slides/edit', [
            'title'       => 'Edit slide ' . $slide['position'],
            'activeNav'   => 'slides',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Hero Slider', 'url' => '/admin/homepage/slides'],
                ['label' => 'Edit slide ' . $slide['position']],
            ],
            'slide'  => $slide,
            'errors' => [],
            'flash'  => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $slide = $this->db->selectOne('SELECT * FROM homepage_slides WHERE id = ?', [$id]);
        if ($slide === null) {
            return Response::html('<h1>404 — Slide not found</h1>', 404);
        }

        $data = [
            'eyebrow'             => $request->string('eyebrow'),
            'headline'            => $request->string('headline'),
            'supporting'          => $request->string('supporting'),
            'primary_cta_label'   => $request->string('primary_cta_label'),
            'primary_cta_url'     => $request->string('primary_cta_url'),
            'secondary_cta_label' => $request->string('secondary_cta_label'),
            'secondary_cta_url'   => $request->string('secondary_cta_url'),
            'layout'              => $request->string('layout'),
            'workflow_status'     => $request->string('workflow_status'),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            $slide = array_merge($slide, $data);
            return $this->render('admin/homepage/slides/edit', [
                'title'       => 'Edit slide ' . $slide['position'],
                'activeNav'   => 'slides',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Hero Slider', 'url' => '/admin/homepage/slides'],
                    ['label' => 'Edit slide ' . $slide['position']],
                ],
                'slide'  => $slide,
                'errors' => $errors,
                'flash'  => null,
            ]);
        }

        // Handle optional image upload
        $mediaId = $slide['media_id'];
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->uploader->storeImage(
                $_FILES['image'],
                $request->string('image_alt'),
                null
            );
            if (!$upload['ok']) {
                $errors['image'] = $upload['error'];
                $slide = array_merge($slide, $data);
                return $this->render('admin/homepage/slides/edit', [
                    'title'       => 'Edit slide ' . $slide['position'],
                    'activeNav'   => 'slides',
                    'breadcrumbs' => [
                        ['label' => 'Dashboard', 'url' => '/admin'],
                        ['label' => 'Hero Slider', 'url' => '/admin/homepage/slides'],
                        ['label' => 'Edit slide ' . $slide['position']],
                    ],
                    'slide'  => $slide,
                    'errors' => $errors,
                    'flash'  => null,
                ]);
            }
            $mediaId = (int) $upload['media']['id'];
        }

        $this->db->execute("
            UPDATE homepage_slides SET
                eyebrow = ?, headline = ?, supporting = ?,
                primary_cta_label = ?, primary_cta_url = ?,
                secondary_cta_label = ?, secondary_cta_url = ?,
                layout = ?, workflow_status = ?, media_id = ?
            WHERE id = ?
        ", [
            $data['eyebrow'],
            $data['headline'],
            $data['supporting'],
            $data['primary_cta_label'],
            $data['primary_cta_url'],
            $data['secondary_cta_label'],
            $data['secondary_cta_url'],
            $data['layout'],
            $data['workflow_status'],
            $mediaId,
            $id,
        ]);

        $this->audit->log('homepage.slide.update', 'homepage_slides', $id, 'Updated slide ' . $slide['position'], [
            'layout'          => $data['layout'],
            'workflow_status' => $data['workflow_status'],
        ]);

        $this->flash('Slide saved.');
        return Response::redirect('/admin/homepage/slides/' . $id . '/edit');
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['headline'] === '') {
            $errors['headline'] = 'Headline is required.';
        }
        if (mb_strlen($data['headline']) > 255) {
            $errors['headline'] = 'Headline must not exceed 255 characters.';
        }
        if ($data['supporting'] === '') {
            $errors['supporting'] = 'Supporting text is required.';
        }
        if (!in_array($data['layout'], ['editorial', 'split', 'image_led'], true)) {
            $errors['layout'] = 'Invalid layout selected.';
        }
        if (!in_array($data['workflow_status'], ['draft', 'published', 'archived'], true)) {
            $errors['workflow_status'] = 'Invalid status.';
        }
        foreach (['primary_cta_url', 'secondary_cta_url'] as $urlField) {
            $url = $data[$urlField];
            if ($url !== '' && !preg_match('#^(/|https?://)#', $url)) {
                $errors[$urlField] = 'URL must start with / or http(s)://';
            }
        }
        return $errors;
    }

    /* ---------------- helpers ---------------- */

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        extract($data, EXTR_SKIP);
        $app  = $this->app;
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
        $msg = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $msg;
    }
}