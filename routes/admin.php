<?php

declare(strict_types=1);

use Mwambe\Core\Http\Response;

return function (Mwambe\Core\Http\Router $router): void {
    $router->get('/admin', function () {
        return Response::html(
            '<!doctype html><html><head><meta charset="utf-8">'
            . '<title>MWAMBE Admin</title></head><body>'
            . '<h1>MWAMBE Admin</h1>'
            . '<p>Stage 1 boot check — login and CMS will be built in Stage 3.</p>'
            . '</body></html>'
        );
    });
};