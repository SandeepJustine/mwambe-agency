<?php

declare(strict_types=1);

use Mwambe\Controllers\Public\DocumentController;
use Mwambe\Controllers\Public\EnquiryController;
use Mwambe\Controllers\Public\EventController;
use Mwambe\Controllers\Public\HomeController;
use Mwambe\Controllers\Public\NewsController;
use Mwambe\Controllers\Public\PageController;
use Mwambe\Controllers\Public\ProgrammeController;
use Mwambe\Controllers\Public\SearchController;
use Mwambe\Controllers\Public\SitemapController;
use Mwambe\Core\Http\Response;

return function (Mwambe\Core\Http\Router $router): void {
    // Homepage
    $router->get('/', [HomeController::class, 'index']);

    // Specific public detail routes — MUST come before generic page catch-alls
    $router->get('/our-work/programmes',         [ProgrammeController::class, 'index']);
    $router->get('/our-work/programmes/{slug}',  [ProgrammeController::class, 'show']);

    $router->get('/news',         [NewsController::class, 'index']);
    $router->get('/news/{slug}',  [NewsController::class, 'show']);

    $router->get('/events',        [EventController::class, 'index']);
    $router->get('/events/{slug}', [EventController::class, 'show']);

    $router->get('/documents',        [DocumentController::class, 'index']);
    $router->get('/documents/{slug}', [DocumentController::class, 'download']);

    // Enquiry forms
    $router->get('/contact',          [EnquiryController::class, 'showContact']);
    $router->post('/contact',         [EnquiryController::class, 'submitContact']);
    $router->get('/partner-with-us',  [EnquiryController::class, 'showPartnership']);
    $router->post('/partner-with-us', [EnquiryController::class, 'submitPartnership']);

    // Search
    $router->get('/search', [SearchController::class, 'index']);

    // SEO
    $router->get('/sitemap.xml', fn() => SitemapController::render());
    $router->get('/robots.txt', function () {
        return Response::text(
            "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin\n"
            . "Disallow: /search\n"
            . "Sitemap: " . url('/sitemap.xml') . "\n"
        );
    });

    // Health check
    $router->get('/health', function () {
        return Response::json(['status' => 'ok', 'app' => 'MWAMBE Agency', 'time' => date('c')]);
    });

    // CMS-driven static pages — MUST come last
    $router->get('/about',         [PageController::class, 'show']);
    $router->get('/our-work',      [PageController::class, 'show']);
    $router->get('/sectors',       [PageController::class, 'show']);
    $router->get('/impact',        [PageController::class, 'show']);
    $router->get('/privacy',       [PageController::class, 'show']);
    $router->get('/terms',         [PageController::class, 'show']);
    $router->get('/accessibility', [PageController::class, 'show']);
};