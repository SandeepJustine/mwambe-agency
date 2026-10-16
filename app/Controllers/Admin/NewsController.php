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

final class NewsController
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
        $articles = $this->db->select("
            SELECT a.*, c.name AS category_name, m.filename AS media_filename
            FROM news_articles a
            LEFT JOIN news_categories c ON c.id = a.category_id
            LEFT JOIN media m ON m.id = a.media_id
            ORDER BY a.updated_at DESC
        ");
        return $this->render('admin/news/index', [
            'title'       => 'News & Insights',
            'activeNav'   => 'news',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'News & Insights'],
            ],
            'articles' => $articles,
            'flash'    => $this->popFlash(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/news/edit', [
            'title'       => 'New article',
            'activeNav'   => 'news',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'News & Insights', 'url' => '/admin/news'],
                ['label' => 'New article'],
            ],
            'article'    => $this->emptyArticle(),
            'categories' => $this->categories(),
            'errors'     => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->collectInput($request);
        $errors = $this->validate($data, null);
        if ($errors) {
            return $this->render('admin/news/edit', [
                'title'       => 'New article',
                'activeNav'   => 'news',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'News & Insights', 'url' => '/admin/news'],
                    ['label' => 'New article'],
                ],
                'article'    => array_merge($this->emptyArticle(), $data),
                'categories' => $this->categories(),
                'errors'     => $errors,
            ]);
        }

        $data['media_id'] = $this->maybeUploadImage($request);
        $data['author_id'] = $this->auth->id();

        $id = $this->db->insert("
            INSERT INTO news_articles
                (slug, title, category_id, excerpt, body, media_id, author_id,
                 published_at, workflow_status, meta_title, meta_description)
            VALUES
                (:slug, :title, :category_id, :excerpt, :body, :media_id, :author_id,
                 :published_at, :workflow_status, :meta_title, :meta_description)
        ", $data);

        $this->audit->log('news.create', 'news_articles', $id, 'Created article: ' . $data['title']);
        $this->flash('Article created.');
        return Response::redirect('/admin/news/' . $id . '/edit');
    }

    public function edit(Request $request, string $id): Response
    {
        $article = $this->db->selectOne("
            SELECT a.*, m.filename AS media_filename, m.alt_text AS media_alt, u.name AS author_name
            FROM news_articles a
            LEFT JOIN media m ON m.id = a.media_id
            LEFT JOIN users u ON u.id = a.author_id
            WHERE a.id = ?
        ", [(int) $id]);
        if ($article === null) return Response::html('<h1>404</h1>', 404);

        return $this->render('admin/news/edit', [
            'title'       => 'Edit: ' . $article['title'],
            'activeNav'   => 'news',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'News & Insights', 'url' => '/admin/news'],
                ['label' => $article['title']],
            ],
            'article'    => $article,
            'categories' => $this->categories(),
            'errors'     => [],
            'flash'      => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM news_articles WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        $data = $this->collectInput($request);
        $errors = $this->validate($data, $id);
        if ($errors) {
            return $this->render('admin/news/edit', [
                'title'       => 'Edit: ' . $existing['title'],
                'activeNav'   => 'news',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'News & Insights', 'url' => '/admin/news'],
                    ['label' => $existing['title']],
                ],
                'article'    => array_merge($existing, $data),
                'categories' => $this->categories(),
                'errors'     => $errors,
            ]);
        }

        $mediaId = $this->maybeUploadImage($request);
        if ($mediaId === null) $mediaId = $existing['media_id'];
        $data['media_id'] = $mediaId;

        $this->db->execute("
            UPDATE news_articles SET
                slug = :slug, title = :title, category_id = :category_id,
                excerpt = :excerpt, body = :body, media_id = :media_id,
                published_at = :published_at, workflow_status = :workflow_status,
                meta_title = :meta_title, meta_description = :meta_description
            WHERE id = :id
        ", array_merge($data, ['id' => $id]));

        $this->audit->log('news.update', 'news_articles', $id, 'Updated article: ' . $data['title']);
        $this->flash('Article saved.');
        return Response::redirect('/admin/news/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $article = $this->db->selectOne('SELECT title FROM news_articles WHERE id = ?', [$id]);
        if ($article === null) return Response::html('<h1>404</h1>', 404);

        $this->db->execute('DELETE FROM news_articles WHERE id = ?', [$id]);
        $this->audit->log('news.delete', 'news_articles', $id, 'Deleted article: ' . $article['title']);
        $this->flash('Article deleted.');
        return Response::redirect('/admin/news');
    }

    /* ---------- Categories ---------- */

    public function categoriesIndex(Request $request): Response
    {
        $categories = $this->db->select("
            SELECT c.*, COUNT(a.id) AS article_count
            FROM news_categories c
            LEFT JOIN news_articles a ON a.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        return $this->render('admin/news/categories', [
            'title'       => 'News categories',
            'activeNav'   => 'news',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'News & Insights', 'url' => '/admin/news'],
                ['label' => 'Categories'],
            ],
            'categories' => $categories,
            'flash'      => $this->popFlash(),
        ]);
    }

    public function categoriesStore(Request $request): Response
    {
        $name = $request->string('name');
        if ($name === '') {
            $this->flash('Category name is required.');
            return Response::redirect('/admin/news/categories');
        }
        $slug = $this->slugify($name);
        if ($this->db->selectOne('SELECT id FROM news_categories WHERE slug = ?', [$slug])) {
            $this->flash('A category with that name already exists.');
            return Response::redirect('/admin/news/categories');
        }
        $id = $this->db->insert('INSERT INTO news_categories (slug, name) VALUES (?, ?)', [$slug, $name]);
        $this->audit->log('news.category.create', 'news_categories', $id, 'Created category: ' . $name);
        $this->flash('Category created.');
        return Response::redirect('/admin/news/categories');
    }

    public function categoriesDelete(Request $request, string $id): Response
    {
        $id = (int) $id;
        $this->db->execute('DELETE FROM news_categories WHERE id = ?', [$id]);
        $this->audit->log('news.category.delete', 'news_categories', $id, 'Deleted category');
        $this->flash('Category deleted. Articles that used it are now uncategorised.');
        return Response::redirect('/admin/news/categories');
    }

    /* ---------- Helpers ---------- */

    private function collectInput(Request $request): array
    {
        $publishedAt = $request->string('published_at');
        if ($publishedAt === '') {
            $publishedAt = date('Y-m-d H:i:s');
        } else {
            // datetime-local gives "YYYY-MM-DDTHH:MM"; convert to SQL format
            $publishedAt = str_replace('T', ' ', $publishedAt);
            if (strlen($publishedAt) === 16) $publishedAt .= ':00';
        }
        $categoryId = (int) $request->input('category_id', 0);
        return [
            'slug'             => $this->slugify($request->string('slug') ?: $request->string('title')),
            'title'            => $request->string('title'),
            'category_id'      => $categoryId > 0 ? $categoryId : null,
            'excerpt'          => $request->string('excerpt'),
            'body'             => $request->string('body'),
            'published_at'     => $publishedAt,
            'workflow_status'  => $request->string('workflow_status', 'draft'),
            'meta_title'       => $request->string('meta_title'),
            'meta_description' => $request->string('meta_description'),
            'media_id'         => null,
            'author_id'        => null,
        ];
    }

    private function validate(array $d, ?int $excludeId): array
    {
        $errors = [];
        if ($d['title'] === '')   $errors['title'] = 'Title is required.';
        if ($d['slug'] === '')    $errors['slug'] = 'Slug is required.';
        if ($d['excerpt'] === '') $errors['excerpt'] = 'Excerpt is required.';
        if (!in_array($d['workflow_status'], ['draft','published','archived'], true)) {
            $errors['workflow_status'] = 'Invalid status.';
        }
        $sql = 'SELECT id FROM news_articles WHERE slug = ?';
        $params = [$d['slug']];
        if ($excludeId !== null) { $sql .= ' AND id <> ?'; $params[] = $excludeId; }
        if ($this->db->selectOne($sql, $params)) $errors['slug'] = 'That slug is already in use.';
        return $errors;
    }

    private function categories(): array
    {
        return $this->db->select('SELECT id, name FROM news_categories ORDER BY name ASC');
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

    private function emptyArticle(): array
    {
        return [
            'id' => null, 'slug' => '', 'title' => '', 'category_id' => null,
            'excerpt' => '', 'body' => '', 'media_id' => null,
            'published_at' => date('Y-m-d H:i:s'),
            'workflow_status' => 'draft',
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