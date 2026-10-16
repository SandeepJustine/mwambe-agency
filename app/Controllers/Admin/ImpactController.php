<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Admin;

use Mwambe\Core\Application;
use Mwambe\Core\Auth;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Services\Audit;

final class ImpactController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Application $app,
    ) {}

    public function index(Request $request): Response
    {
        $indicators = $this->db->select("
            SELECT i.*, u.name AS reviewer_name
            FROM impact_indicators i
            LEFT JOIN users u ON u.id = i.reviewer_id
            ORDER BY i.position ASC, i.id ASC
        ");

        return $this->render('admin/impact/index', [
            'title'       => 'Impact indicators',
            'activeNav'   => 'impact',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Impact'],
            ],
            'indicators' => $indicators,
            'flash'      => $this->popFlash(),
        ]);
    }

    public function edit(Request $request, string $id): Response
    {
        $indicator = $this->db->selectOne('SELECT * FROM impact_indicators WHERE id = ?', [(int) $id]);
        if ($indicator === null) return Response::html('<h1>404</h1>', 404);

        return $this->render('admin/impact/edit', [
            'title'       => 'Edit indicator',
            'activeNav'   => 'impact',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Impact', 'url' => '/admin/impact'],
                ['label' => $indicator['label']],
            ],
            'indicator' => $indicator,
            'errors'    => [],
            'flash'     => $this->popFlash(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        $existing = $this->db->selectOne('SELECT * FROM impact_indicators WHERE id = ?', [$id]);
        if ($existing === null) return Response::html('<h1>404</h1>', 404);

        // Only Super Admin / Admin may verify. Editor cannot.
        $canVerify = $this->auth->hasRole('super_admin', 'admin');

        $data = [
            'label'             => $request->string('label'),
            'value'             => $request->string('value'),
            'unit'              => $request->string('unit'),
            'description'       => $request->string('description'),
            'reporting_period'  => $request->string('reporting_period'),
            'evidence_reference'=> $request->string('evidence_reference'),
            'verification_status' => $request->string('verification_status', 'pending'),
            'is_public'         => (int) (bool) $request->input('is_public'),
            'position'          => (int) $request->input('position', 0),
        ];

        // Block non-admins from changing verification status
        if (!$canVerify && $data['verification_status'] !== $existing['verification_status']) {
            $data['verification_status'] = $existing['verification_status'];
        }

        $errors = [];
        if ($data['label'] === '') $errors['label'] = 'Label is required.';
        if ($data['value'] === '') $errors['value'] = 'Value is required.';
        if (!in_array($data['verification_status'], ['pending', 'verified', 'rejected'], true)) {
            $errors['verification_status'] = 'Invalid status.';
        }
        if ($data['verification_status'] === 'verified' && $data['evidence_reference'] === '') {
            $errors['evidence_reference'] = 'Evidence reference is required before verifying a figure.';
        }

        if ($errors) {
            return $this->render('admin/impact/edit', [
                'title'       => 'Edit indicator',
                'activeNav'   => 'impact',
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/admin'],
                    ['label' => 'Impact', 'url' => '/admin/impact'],
                    ['label' => $existing['label']],
                ],
                'indicator' => array_merge($existing, $data),
                'errors'    => $errors,
            ]);
        }

        // Determine reviewer / approval timestamp
        $reviewerId = null;
        $approvedAt = null;
        if ($data['verification_status'] === 'verified' && $existing['verification_status'] !== 'verified') {
            $reviewerId = $this->auth->id();
            $approvedAt = date('Y-m-d H:i:s');
        } elseif ($data['verification_status'] === 'verified') {
            $reviewerId = $existing['reviewer_id'] ?? $this->auth->id();
            $approvedAt = $existing['approved_at'] ?? date('Y-m-d H:i:s');
        }

        $this->db->execute("
            UPDATE impact_indicators SET
                label = ?, value = ?, unit = ?, description = ?,
                reporting_period = ?, evidence_reference = ?,
                verification_status = ?, is_public = ?, position = ?,
                reviewer_id = ?, approved_at = ?
            WHERE id = ?
        ", [
            $data['label'], $data['value'], $data['unit'], $data['description'],
            $data['reporting_period'], $data['evidence_reference'],
            $data['verification_status'], $data['is_public'], $data['position'],
            $reviewerId, $approvedAt, $id,
        ]);

        $this->audit->log('impact.update', 'impact_indicators', $id, 'Updated indicator: ' . $data['label'], [
            'verification_status' => $data['verification_status'],
            'is_public' => $data['is_public'],
        ]);

        $this->flash('Indicator saved.');
        return Response::redirect('/admin/impact/' . $id . '/edit');
    }

    public function create(Request $request): Response
    {
        return $this->render('admin/impact/edit', [
            'title'       => 'New indicator',
            'activeNav'   => 'impact',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Impact', 'url' => '/admin/impact'],
                ['label' => 'New indicator'],
            ],
            'indicator' => [
                'id' => null, 'key_name' => '', 'label' => '', 'value' => '', 'unit' => '',
                'description' => '', 'reporting_period' => '', 'evidence_reference' => '',
                'verification_status' => 'pending', 'is_public' => 1, 'position' => 0,
            ],
            'errors' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $keyName = $request->string('key_name') ?: $this->slugify($request->string('label'));
        $id = $this->db->insert("
            INSERT INTO impact_indicators
                (key_name, label, value, unit, description, reporting_period, evidence_reference,
                 verification_status, is_public, position)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 1, ?)
        ", [
            $keyName,
            $request->string('label'),
            $request->string('value'),
            $request->string('unit'),
            $request->string('description'),
            $request->string('reporting_period'),
            $request->string('evidence_reference'),
            (int) $request->input('position', 0),
        ]);

        $this->audit->log('impact.create', 'impact_indicators', $id, 'Created indicator', ['key_name' => $keyName]);
        $this->flash('Indicator created (pending verification).');
        return Response::redirect('/admin/impact/' . $id . '/edit');
    }

    private function render(string $view, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app  = $this->app;
        $auth = $this->auth;
        $csrf = $this->csrf;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/admin.php';
        return Response::html((string) ob_get_clean());
    }

    private function flash(string $m): void { $_SESSION['_flash'] = $m; }
    private function popFlash(): ?string { $m = $_SESSION['_flash'] ?? null; unset($_SESSION['_flash']); return $m; }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9_]+/', '_', $s) ?? '';
        return trim($s, '_') ?: ('indicator_' . bin2hex(random_bytes(3)));
    }
}