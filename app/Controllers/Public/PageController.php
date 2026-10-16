<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\PageRepository;

final class PageController
{
    public function __construct(
        private readonly Application $app,
        private readonly PageRepository $pages,
    ) {}

    public function show(Request $request): Response
    {
        $slug = trim($request->uri, '/');
        $page = $this->pages->findBySlug($slug);
        if ($page === null) {
            return $this->notFound($request);
        }
        $sections = $this->pages->sectionsFor((int) $page['id']);
        return $this->render('public/page', [
            'title'    => $page['meta_title'] ?: $page['title'],
            'page'     => $page,
            'sections' => $sections,
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

    private function notFound(Request $request): Response
    {
        ob_start();
        require BASE_PATH . '/app/Views/errors/404.php';
        return Response::html((string) ob_get_clean(), 404);
    }
}