<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;

final class AuditLogController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $page  = max(1, (int) ($request->query['page'] ?? 1));
        $per   = 50;
        $offset = ($page - 1) * $per;

        $total = (int) $this->db->selectOne('SELECT COUNT(*) AS c FROM audit_logs')['c'];
        $rows = $this->db->select("
            SELECT a.*, u.name AS user_name, u.email AS user_email
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC
            LIMIT {$per} OFFSET {$offset}
        ");

        $viewPath = BASE_PATH . '/app/Views/admin/system/audit.php';
        extract([
            'title'       => 'Audit log',
            'activeNav'   => 'audit',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Audit log'],
            ],
            'rows'  => $rows,
            'page'  => $page,
            'per'   => $per,
            'total' => $total,
        ], EXTR_SKIP);

        $app  = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;

        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }
}