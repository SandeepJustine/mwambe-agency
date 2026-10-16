<?php

declare(strict_types=1);

use Mwambe\Core\Http\Response;

return function (Mwambe\Core\Http\Router $router): void {
    $router->get('/', function () {
        return Response::html(
            '<!doctype html><html><head><meta charset="utf-8">'
            . '<title>MWAMBE Agency</title></head><body>'
            . '<h1>MWAMBE Agency</h1>'
            . '<p>Connecting People, Skills, Enterprises and Opportunities for Resilient Communities.</p>'
            . '<p><em>Stage 1 boot check — public site will be built in Stage 2.</em></p>'
            . '</body></html>'
        );
    });

    $router->get('/health', function () {
        return Response::json([
            'status' => 'ok',
            'app'    => 'MWAMBE Agency',
            'time'   => date('c'),
        ]);
    });
};