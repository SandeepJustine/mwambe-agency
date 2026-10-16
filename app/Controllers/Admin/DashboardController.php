<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class DashboardController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $stats = $this->gatherStats();
        $needsAttention = $this->gatherAttentionItems();
        $enquiriesByWeek = $this->enquiriesByWeek(8);
        $actionsByDay = $this->auditByDay(14);
        $contentMix = $this->contentMix();
        $recentContent = $this->recentContent(6);
        $recentEnquiries = $this->recentEnquiries(5);
        $upcomingEvents = $this->upcomingEvents(4);

        $viewPath = BASE_PATH . '/app/Views/admin/dashboard.php';

        $title       = 'Dashboard';
        $activeNav   = 'dashboard';
        $breadcrumbs = [
            ['label' => 'Dashboard'],
        ];

        $app  = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;

        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }

    /* ---------------------------------------------------------------
     * Data gathering
     * --------------------------------------------------------------- */

    private function gatherStats(): array
    {
        $slides = (int) $this->db->selectOne(
            'SELECT COUNT(*) AS c FROM homepage_slides WHERE is_active = 1'
        )['c'];

        $programmes = (int) $this->db->selectOne(
            "SELECT COUNT(*) AS c FROM programmes WHERE workflow_status = 'published'"
        )['c'];

        // Enquiries in the last 30 days (contact + partnership)
        $enquiries30 = (int) $this->db->selectOne("
            SELECT
                (SELECT COUNT(*) FROM contact_enquiries     WHERE created_at > (NOW() - INTERVAL 30 DAY)) +
                (SELECT COUNT(*) FROM partnership_enquiries WHERE created_at > (NOW() - INTERVAL 30 DAY))
                AS c
        ")['c'];

        // Same window, the 30 days before it — for the delta
        $enquiries60to30 = (int) $this->db->selectOne("
            SELECT
                (SELECT COUNT(*) FROM contact_enquiries
                    WHERE created_at > (NOW() - INTERVAL 60 DAY)
                      AND created_at <= (NOW() - INTERVAL 30 DAY)) +
                (SELECT COUNT(*) FROM partnership_enquiries
                    WHERE created_at > (NOW() - INTERVAL 60 DAY)
                      AND created_at <= (NOW() - INTERVAL 30 DAY))
                AS c
        ")['c'];

        $media = (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM media')['c'];

        $users = (int) $this->db->selectOne(
            'SELECT COUNT(*) AS c FROM users WHERE is_active = 1'
        )['c'];

        // Enquiries delta as a percentage (clamped, no division by zero)
        $delta = null;
        if ($enquiries60to30 > 0) {
            $delta = (int) round((($enquiries30 - $enquiries60to30) / $enquiries60to30) * 100);
        } elseif ($enquiries30 > 0) {
            $delta = 100;
        }

        return [
            'slides'      => $slides,
            'programmes'  => $programmes,
            'enquiries30' => $enquiries30,
            'enquiriesDelta' => $delta,
            'media'       => $media,
            'users'       => $users,
        ];
    }

    private function gatherAttentionItems(): array
    {
        $items = [];

        if ($this->auth->hasRole('super_admin', 'admin', 'enquiries')) {
            $n = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM contact_enquiries WHERE status = 'new'"
            )['c'];
            if ($n > 0) {
                $items[] = [
                    'label' => $n . ' new contact enquir' . ($n === 1 ? 'y' : 'ies') . ' to review',
                    'url'   => '/admin/enquiries/contact?status=new',
                ];
            }

            $n = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM partnership_enquiries WHERE status = 'new'"
            )['c'];
            if ($n > 0) {
                $items[] = [
                    'label' => $n . ' new partnership enquir' . ($n === 1 ? 'y' : 'ies'),
                    'url'   => '/admin/enquiries/partnership',
                ];
            }
        }

        if ($this->auth->hasRole('super_admin', 'admin', 'editor')) {
            $n = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM news_articles WHERE workflow_status = 'draft'"
            )['c'];
            if ($n > 0) {
                $items[] = [
                    'label' => $n . ' news draft' . ($n === 1 ? '' : 's') . ' awaiting publication',
                    'url'   => '/admin/news',
                ];
            }

            $n = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM programmes WHERE workflow_status = 'draft'"
            )['c'];
            if ($n > 0) {
                $items[] = [
                    'label' => $n . ' programme draft' . ($n === 1 ? '' : 's'),
                    'url'   => '/admin/programmes',
                ];
            }

            $n = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM impact_indicators WHERE verification_status = 'pending'"
            )['c'];
            if ($n > 0) {
                $items[] = [
                    'label' => $n . ' impact indicator' . ($n === 1 ? '' : 's') . ' awaiting verification',
                    'url'   => '/admin/impact',
                ];
            }
        }

        return $items;
    }

    /**
     * Returns [{ label: 'Wk 1', value: 4 }, ...] for the last N weeks.
     */
    private function enquiriesByWeek(int $weeks): array
    {
        $rows = $this->db->select("
            SELECT
                YEARWEEK(created_at, 3) AS yw,
                MIN(DATE(created_at)) AS week_start,
                COUNT(*) AS c
            FROM (
                SELECT created_at FROM contact_enquiries
                WHERE created_at > (NOW() - INTERVAL " . ($weeks * 7) . " DAY)
                UNION ALL
                SELECT created_at FROM partnership_enquiries
                WHERE created_at > (NOW() - INTERVAL " . ($weeks * 7) . " DAY)
            ) AS t
            GROUP BY YEARWEEK(created_at, 3)
            ORDER BY week_start ASC
        ");

        // Normalise to exactly $weeks buckets, filling gaps with zero
        $out = [];
        $buckets = [];
        foreach ($rows as $r) {
            $buckets[$r['week_start']] = (int) $r['c'];
        }

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime('-' . ($i * 7) . ' days'));
            $out[] = [
                'label' => date('j M', strtotime($date)),
                'value' => $buckets[$date] ?? 0,
            ];
        }

        return $out;
    }

    /**
     * Returns [{ label: '14 Oct', value: 6 }, ...] for the last N days.
     */
    private function auditByDay(int $days): array
    {
        $rows = $this->db->select("
            SELECT DATE(created_at) AS d, COUNT(*) AS c
            FROM audit_logs
            WHERE created_at > (NOW() - INTERVAL {$days} DAY)
            GROUP BY DATE(created_at)
        ");

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['d']] = (int) $r['c'];
        }

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $out[] = [
                'label' => date('j M', strtotime($d)),
                'value' => $byDate[$d] ?? 0,
            ];
        }
        return $out;
    }

    /**
     * Content by workflow status — feeds the donut chart.
     */
    private function contentMix(): array
    {
        $published = 0;
        $draft = 0;
        $archived = 0;

        foreach (['programmes', 'news_articles', 'events'] as $table) {
            $rows = $this->db->select("
                SELECT workflow_status, COUNT(*) AS c
                FROM {$table}
                GROUP BY workflow_status
            ");
            foreach ($rows as $r) {
                $status = $r['workflow_status'];
                if ($status === 'published') $published += (int) $r['c'];
                elseif ($status === 'draft')  $draft     += (int) $r['c'];
                elseif ($status === 'archived') $archived += (int) $r['c'];
            }
        }

        $total = $published + $draft + $archived;

        return [
            'published' => $published,
            'draft'     => $draft,
            'archived'  => $archived,
            'total'     => $total,
            'published_pct' => $total > 0 ? (int) round($published / $total * 100) : 0,
            'draft_pct'     => $total > 0 ? (int) round($draft / $total * 100) : 0,
            'archived_pct'  => $total > 0 ? (int) round($archived / $total * 100) : 0,
        ];
    }

    /**
     * Recently updated items across the three content types,
     * merged and sorted by updated_at.
     */
    private function recentContent(int $limit): array
    {
        $rows = $this->db->select("
            SELECT 'programme' AS type, id, title, workflow_status AS status, updated_at,
                   CONCAT('/admin/programmes/', id, '/edit') AS url
            FROM programmes
            UNION ALL
            SELECT 'article' AS type, id, title, workflow_status AS status, updated_at,
                   CONCAT('/admin/news/', id, '/edit') AS url
            FROM news_articles
            UNION ALL
            SELECT 'event' AS type, id, title, workflow_status AS status, updated_at,
                   CONCAT('/admin/events/', id, '/edit') AS url
            FROM events
            ORDER BY updated_at DESC
            LIMIT " . (int) $limit
    );
        return $rows;
    }

    private function recentEnquiries(int $limit): array
    {
        $rows = $this->db->select("
            SELECT 'contact' AS type, reference, name AS sender, subject AS topic, status, created_at,
                   CONCAT('/admin/enquiries/contact/', id) AS url
            FROM contact_enquiries
            UNION ALL
            SELECT 'partnership' AS type, reference, contact_person AS sender,
                   organisation_name AS topic, status, created_at,
                   CONCAT('/admin/enquiries/partnership/', id) AS url
            FROM partnership_enquiries
            ORDER BY created_at DESC
            LIMIT " . (int) $limit
        );
        return $rows;
    }

    private function upcomingEvents(int $limit): array
    {
        return $this->db->select("
            SELECT id, title, starts_at, location
            FROM events
            WHERE workflow_status = 'published' AND starts_at >= NOW()
            ORDER BY starts_at ASC
            LIMIT " . (int) $limit
        );
    }
}