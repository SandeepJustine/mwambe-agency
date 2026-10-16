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

final class DocumentController
{
    private const ALLOWED_MIME = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $docs = $this->db->select("
            SELECT d.*, u.name AS uploaded_by_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.uploaded_by
            ORDER BY d.created_at DESC
        ");
        return $this->render('admin/documents/index', [
            'title'       => 'Documents',
            'activeNav'   => 'documents',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Documents'],
            ],
            'documents' => $docs,
            'flash'     => $this->popFlash(),
        ]);
    }

    public function store(Request $request): Response
    {
        $title = $request->string('title');
        $description = $request->string('description');
        $category = $request->string('category');
        $isPublic = (int) (bool) $request->input('is_public', 1);

        if ($title === '') {
            $this->flash('Title is required.');
            return Response::redirect('/admin/documents');
        }

        $file = $_FILES['document'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('Please select a file to upload.');
            return Response::redirect('/admin/documents');
        }

        $maxBytes = (int) config('UPLOAD_MAX_BYTES', 5242880) * 4; // 20 MB for docs
        if ($file['size'] > $maxBytes) {
            $this->flash('File is too large.');
            return Response::redirect('/admin/documents');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            $this->flash('Unsupported file type. Allowed: PDF, DOC, DOCX, XLS, XLSX.');
            return Response::redirect('/admin/documents');
        }

        $dir = BASE_PATH . '/storage/documents';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            $this->flash('Cannot create documents directory.');
            return Response::redirect('/admin/documents');
        }

        $basename = date('Y/m') . '/' . bin2hex(random_bytes(12)) . '.' . self::ALLOWED_MIME[$mime];
        $target = $dir . '/' . $basename;
        if (!is_dir(dirname($target)) && !@mkdir(dirname($target), 0755, true)) {
            $this->flash('Cannot create documents directory.');
            return Response::redirect('/admin/documents');
        }

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $this->flash('Failed to store file.');
            return Response::redirect('/admin/documents');
        }

        $slug = $this->slugify($title);
        if ($this->db->selectOne('SELECT id FROM documents WHERE slug = ?', [$slug])) {
            $slug .= '-' . bin2hex(random_bytes(3));
        }

        $id = $this->db->insert("
            INSERT INTO documents (title, slug, description, category, filename, original_name,
                                   mime_type, size_bytes, uploaded_by, is_public)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $title,
            $slug,
            $description ?: null,
            $category ?: null,
            $basename,
            mb_substr((string) $file['name'], 0, 250),
            $mime,
            (int) $file['size'],
            $this->auth->id(),
            $isPublic,
        ]);

        $this->audit->log('document.upload', 'documents', $id, 'Uploaded document: ' . $title);
        $this->flash('Document uploaded.');
        return Response::redirect('/admin/documents');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $doc = $this->db->selectOne('SELECT * FROM documents WHERE id = ?', [$id]);
        if ($doc === null) return Response::html('<h1>404</h1>', 404);

        $path = BASE_PATH . '/storage/documents/' . $doc['filename'];
        if (is_file($path)) @unlink($path);

        $this->db->execute('DELETE FROM documents WHERE id = ?', [$id]);
        $this->audit->log('document.delete', 'documents', $id, 'Deleted document: ' . $doc['title']);
        $this->flash('Document deleted.');
        return Response::redirect('/admin/documents');
    }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9-]+/', '-', $s) ?? '';
        return trim($s, '-') ?: bin2hex(random_bytes(4));
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