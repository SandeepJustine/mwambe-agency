<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\ImpactRepository;
use Mwambe\Models\NewsRepository;
use Mwambe\Models\PageRepository;
use Mwambe\Models\SectorRepository;
use Mwambe\Models\ServiceRepository;
use Mwambe\Models\TeamRepository;

final class PageController
{
    public function __construct(
        private readonly Application $app,
        private readonly PageRepository $pages,
        private readonly ImpactRepository $impact,
        private readonly NewsRepository $news,
        private readonly SectorRepository $sectors,
        private readonly ServiceRepository $services,
        private readonly TeamRepository $team,
    ) {}

    public function show(Request $request): Response
    {
        $slug = trim($request->uri, '/');
        $page = $this->pages->findBySlug($slug);
        if ($page === null) {
            return $this->notFound($request);
        }
        $sections = $this->pages->sectionsFor((int) $page['id']);
        $pageIntro = null;
        if (in_array($slug, ['about', 'our-work', 'impact', 'sectors'], true)) {
            foreach ($sections as $index => $section) {
                if ($section['section_type'] === 'rich_text') {
                    $pageIntro = $section;
                    unset($sections[$index]);
                    break;
                }
            }
            $sections = array_values($sections);
        }

        $data = [
            'title'    => $page['meta_title'] ?: $page['title'],
            'page'     => $page,
            'sections' => $sections,
            'pageIntro' => $pageIntro,
        ];

        if ($slug === 'impact') {
            $data['extraViewPath'] = BASE_PATH . '/app/Views/public/impact.php';
            $data['indicators'] = $this->impact->publicIndicators();
            $data['impactStories'] = $this->news->published(6, 0, 'impact-story');
        } elseif ($slug === 'about') {
            $data['extraViewPath'] = BASE_PATH . '/app/Views/public/team.php';
            $data['teamMembers'] = $this->team->active();
        } elseif ($slug === 'sectors') {
            $data['extraViewPath'] = BASE_PATH . '/app/Views/public/sectors.php';
            $data['sectors'] = $this->sectors->published();
        } elseif ($slug === 'our-work') {
            $data['services'] = $this->services->published();
        }

        return $this->render('public/page', $data);
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