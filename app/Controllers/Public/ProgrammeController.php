<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\ProgrammeRepository;

final class ProgrammeController
{
    public function __construct(
        private readonly Application $app,
        private readonly ProgrammeRepository $programmes,
    ) {}

    public function index(Request $request): Response
    {
        $kind = $request->string('kind');
        $filters = [];
        if (in_array($kind, ['programme', 'project'], true)) {
            $filters['kind'] = $kind;
        }
        return $this->render('public/programmes/index', [
            'title'      => 'Programmes & Projects',
            'programmes' => $this->programmes->published($filters, 24, 0),
            'currentKind' => $kind,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $programme = $this->programmes->findBySlug($slug);
        if ($programme === null) {
            return $this->notFound();
        }
        return $this->render('public/programmes/show', [
            'title'     => $programme['meta_title'] ?: $programme['title'] . ' | MWAMBE Agency',
            'programme' => $programme,
            'objectives' => $programme['objectives'] ? json_decode((string) $programme['objectives'], true) : [],
            'audience'   => $programme['audience']   ? json_decode((string) $programme['audience'],   true) : [],
            'activities' => $programme['activities'] ? json_decode((string) $programme['activities'], true) : [],
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