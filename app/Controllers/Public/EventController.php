<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class EventController
{
    public function __construct(
        private readonly Application $app,
        private readonly Database $db,
    ) {}

    public function show(Request $request, string $slug): Response
    {
        $event = $this->db->selectOne("
            SELECT e.*, m.filename AS media_filename, m.alt_text AS media_alt
            FROM events e
            LEFT JOIN media m ON m.id = e.media_id
            WHERE e.slug = ? AND e.workflow_status = 'published'
            LIMIT 1
        ", [$slug]);

        if ($event === null) {
            ob_start();
            require BASE_PATH . '/app/Views/errors/404.php';
            return Response::html((string) ob_get_clean(), 404);
        }

        $viewPath = BASE_PATH . '/app/Views/public/events/show.php';
        extract([
            'title'   => $event['title'] . ' | MWAMBE Agency',
            'event'   => $event,
        ], EXTR_SKIP);
        $app = $this->app;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }
}