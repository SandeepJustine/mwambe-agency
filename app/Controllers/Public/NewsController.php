<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\NewsRepository;

final class NewsController
{
    private const PER_PAGE = 9;

    public function __construct(
        private readonly Application $app,
        private readonly NewsRepository $news,
    ) {}

    public function index(Request $request): Response
    {
        $page = max(1, (int) ($request->query['page'] ?? 1));
        $category = $request->string('category');
        $category = $category !== '' ? $category : null;
        $offset = ($page - 1) * self::PER_PAGE;

        $articles = $this->news->published(self::PER_PAGE, $offset, $category);
        $total = $this->news->countPublished($category);

        return $this->render('public/news/index', [
            'title'    => 'News & Activities | MWAMBE Agency',
            'articles' => $articles,
            'page'     => $page,
            'perPage'  => self::PER_PAGE,
            'total'    => $total,
            'category' => $category,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $article = $this->news->findBySlug($slug);
        if ($article === null) {
            return $this->notFound();
        }
        return $this->render('public/news/show', [
            'title'   => $article['meta_title'] ?: $article['title'] . ' | MWAMBE Agency',
            'article' => $article,
            'metaDescription' => $article['meta_description'] ?: $article['excerpt'],
            'ogImage' => !empty($article['media_filename']) ? asset('uploads/' . $article['media_filename']) : null,
        ]);
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app = $this->app;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }

    private function notFound(): Response
    {
        ob_start();
        require BASE_PATH . '/app/Views/errors/404.php';
        return Response::html((string) ob_get_clean(), 404);
    }
}