<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Models\EventRepository;

final class EventController
{
    public function __construct(
        private readonly Application $app,
        private readonly Database $db,
        private readonly EventRepository $events,
    ) {}

    public function index(Request $request): Response
    {
        return $this->render('public/events/index', [
            'title' => 'Events & Activities | MWAMBE Agency',
            'upcoming' => $this->events->upcoming(50),
            'past' => $this->events->past(50),
        ]);
    }

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

        return $this->render('public/events/show', [
            'title'   => $event['title'] . ' | MWAMBE Agency',
            'event'   => $event,
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
}