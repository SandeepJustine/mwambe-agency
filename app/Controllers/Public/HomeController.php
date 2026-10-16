<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\EventRepository;
use Mwambe\Models\HomepageSlide;
use Mwambe\Models\ImpactRepository;
use Mwambe\Models\NewsRepository;
use Mwambe\Models\ProgrammeRepository;

final class HomeController
{
    public function __construct(
        private readonly Application $app,
        private readonly HomepageSlide $slides,
        private readonly ProgrammeRepository $programmes,
        private readonly NewsRepository $news,
        private readonly EventRepository $events,
        private readonly ImpactRepository $impact,
    ) {}

    public function index(Request $request): Response
    {
        $slides = $this->slides->active();

        $viewPath = BASE_PATH . '/app/Views/public/home.php';
        extract([
            'title'             => 'MWAMBE Agency — Connecting People, Skills, Enterprises and Opportunities',
            'slides'            => $slides,
            'featuredPrograms'  => $this->programmes->featured(3),
            'latestNews'        => $this->news->published(3),
            'upcomingEvents'    => $this->events->upcoming(3),
            'impactIndicators'  => $this->impact->publicIndicators(),
        ], EXTR_SKIP);
        $app = $this->app;

        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }
}