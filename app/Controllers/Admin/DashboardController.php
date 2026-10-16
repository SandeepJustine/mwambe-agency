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
        $stats = [
            'slides'      => (int) $this->db->selectOne(
                'SELECT COUNT(*) AS c FROM homepage_slides WHERE is_active = 1'
            )['c'],
            'media'       => (int) $this->db->selectOne(
                'SELECT COUNT(*) AS c FROM media'
            )['c'],
            'documents'   => (int) $this->db->selectOne(
                'SELECT COUNT(*) AS c FROM documents'
            )['c'],
            'users'       => (int) $this->db->selectOne(
                'SELECT COUNT(*) AS c FROM users WHERE is_active = 1'
            )['c'],
            'audit_today' => (int) $this->db->selectOne(
                'SELECT COUNT(*) AS c FROM audit_logs WHERE DATE(created_at) = CURDATE()'
            )['c'],
        ];

        $needsAttention = [];

        if ($this->auth->hasRole('super_admin', 'admin', 'enquiries')) {
            $newContact = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM contact_enquiries WHERE status = 'new'"
            )['c'];
            if ($newContact > 0) {
                $needsAttention[] = [
                    'label' => $newContact . ' new contact enquir' . ($newContact === 1 ? 'y' : 'ies'),
                    'url'   => '/admin/enquiries/contact?status=new',
                ];
            }

            $newPartnership = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM partnership_enquiries WHERE status = 'new'"
            )['c'];
            if ($newPartnership > 0) {
                $needsAttention[] = [
                    'label' => $newPartnership . ' new partnership enquir' . ($newPartnership === 1 ? 'y' : 'ies'),
                    'url'   => '/admin/enquiries/partnership',
                ];
            }
        }

        if ($this->auth->hasRole('super_admin', 'admin', 'editor')) {
            $draftProgrammes = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM programmes WHERE workflow_status = 'draft'"
            )['c'];
            if ($draftProgrammes > 0) {
                $needsAttention[] = [
                    'label' => $draftProgrammes . ' programme draft' . ($draftProgrammes === 1 ? '' : 's'),
                    'url'   => '/admin/programmes',
                ];
            }

            $draftNews = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM news_articles WHERE workflow_status = 'draft'"
            )['c'];
            if ($draftNews > 0) {
                $needsAttention[] = [
                    'label' => $draftNews . ' news draft' . ($draftNews === 1 ? '' : 's'),
                    'url'   => '/admin/news',
                ];
            }

            $pendingImpact = (int) $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM impact_indicators WHERE verification_status = 'pending'"
            )['c'];
            if ($pendingImpact > 0) {
                $needsAttention[] = [
                    'label' => $pendingImpact . ' impact indicator' . ($pendingImpact === 1 ? '' : 's') . ' awaiting verification',
                    'url'   => '/admin/impact',
                ];
            }
        }

        $recentAudit = $this->db->select("
            SELECT a.action, a.entity_type, a.entity_id, a.summary, a.created_at,
                   u.name AS user_name
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC
            LIMIT 10
        ");

        // ---- Render via the admin layout ----
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
}