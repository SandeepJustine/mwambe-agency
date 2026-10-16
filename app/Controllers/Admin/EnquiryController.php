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
use Mwambe\Services\Mailer;

final class EnquiryController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly Audit $audit,
        private readonly Mailer $mailer,
        private readonly Application $app,
    ) {}

    public function contact(Request $request): Response
    {
        $status = $request->string('status');
        $sql = "SELECT * FROM contact_enquiries";
        $params = [];
        if ($status !== '') { $sql .= " WHERE status = :s"; $params['s'] = $status; }
        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $rows = $this->db->select($sql, $params);

        return $this->render('admin/enquiries/contact_index', [
            'title'       => 'Contact enquiries',
            'activeNav'   => 'enquiries',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Contact enquiries'],
            ],
            'enquiries'  => $rows,
            'statusFilter' => $status,
            'flash'      => $this->popFlash(),
        ]);
    }

    public function contactShow(Request $request, string $id): Response
    {
        $enquiry = $this->db->selectOne('SELECT * FROM contact_enquiries WHERE id = ?', [(int) $id]);
        if ($enquiry === null) return Response::html('<h1>404</h1>', 404);

        $notes = $this->db->select("
            SELECT n.*, u.name AS user_name
            FROM enquiry_notes n
            LEFT JOIN users u ON u.id = n.user_id
            WHERE n.enquiry_type = 'contact' AND n.enquiry_id = ?
            ORDER BY n.created_at DESC
        ", [(int) $id]);

        return $this->render('admin/enquiries/contact_show', [
            'title'       => 'Enquiry ' . $enquiry['reference'],
            'activeNav'   => 'enquiries',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Contact enquiries', 'url' => '/admin/enquiries/contact'],
                ['label' => $enquiry['reference']],
            ],
            'enquiry' => $enquiry,
            'notes'   => $notes,
            'flash'   => $this->popFlash(),
        ]);
    }

    public function contactUpdate(Request $request, string $id): Response
    {
        $id = (int) $id;
        $enquiry = $this->db->selectOne('SELECT * FROM contact_enquiries WHERE id = ?', [$id]);
        if ($enquiry === null) return Response::html('<h1>404</h1>', 404);

        $status = $request->string('status');
        if (!in_array($status, ['new','in_review','assigned','responded','resolved','archived'], true)) {
            $status = $enquiry['status'];
        }

        $this->db->execute('UPDATE contact_enquiries SET status = ? WHERE id = ?', [$status, $id]);
        $this->audit->log('enquiry.update', 'contact_enquiries', $id, 'Status: ' . $status);

        $note = $request->string('note');
        if ($note !== '') {
            $this->db->execute("
                INSERT INTO enquiry_notes (enquiry_type, enquiry_id, user_id, body)
                VALUES ('contact', ?, ?, ?)
            ", [$id, $this->auth->id(), $note]);
        }

        $this->flash('Enquiry updated.');
        return Response::redirect('/admin/enquiries/contact/' . $id);
    }

    public function contactRetryEmail(Request $request, string $id): Response
    {
        $id = (int) $id;
        $enquiry = $this->db->selectOne('SELECT * FROM contact_enquiries WHERE id = ?', [$id]);
        if ($enquiry === null) return Response::html('<h1>404</h1>', 404);

        $html = "<h2>Contact enquiry {$enquiry['reference']}</h2>"
            . "<p><strong>Name:</strong> " . htmlspecialchars($enquiry['name'], ENT_QUOTES) . "</p>"
            . "<p><strong>Email:</strong> " . htmlspecialchars($enquiry['email'], ENT_QUOTES) . "</p>"
            . "<p>" . nl2br(htmlspecialchars($enquiry['message'], ENT_QUOTES)) . "</p>";

        $result = $this->mailer->send(
            'mwambeagency@gmail.com',
            'MWAMBE Agency',
            "[Retry] Contact enquiry {$enquiry['reference']}",
            $html,
            $enquiry['email'],
            $enquiry['name']
        );

        if ($result['ok']) {
            $this->db->execute('UPDATE contact_enquiries SET email_sent_at = NOW(), email_error = NULL WHERE id = ?', [$id]);
            $this->flash('Email re-sent.');
        } else {
            $this->db->execute('UPDATE contact_enquiries SET email_error = ? WHERE id = ?', [
                substr($result['error'] ?? 'unknown', 0, 500), $id,
            ]);
            $this->flash('Email failed again: ' . ($result['error'] ?? 'unknown'));
        }

        return Response::redirect('/admin/enquiries/contact/' . $id);
    }

    public function partnership(Request $request): Response
    {
        $rows = $this->db->select("SELECT * FROM partnership_enquiries ORDER BY created_at DESC LIMIT 100");
        return $this->render('admin/enquiries/partnership_index', [
            'title'       => 'Partnership enquiries',
            'activeNav'   => 'enquiries',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Partnership enquiries'],
            ],
            'enquiries' => $rows,
            'flash'     => $this->popFlash(),
        ]);
    }

    public function partnershipShow(Request $request, string $id): Response
    {
        $enquiry = $this->db->selectOne('SELECT * FROM partnership_enquiries WHERE id = ?', [(int) $id]);
        if ($enquiry === null) return Response::html('<h1>404</h1>', 404);

        $notes = $this->db->select("
            SELECT n.*, u.name AS user_name
            FROM enquiry_notes n
            LEFT JOIN users u ON u.id = n.user_id
            WHERE n.enquiry_type = 'partnership' AND n.enquiry_id = ?
            ORDER BY n.created_at DESC
        ", [(int) $id]);

        return $this->render('admin/enquiries/partnership_show', [
            'title'       => 'Enquiry ' . $enquiry['reference'],
            'activeNav'   => 'enquiries',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Partnership enquiries', 'url' => '/admin/enquiries/partnership'],
                ['label' => $enquiry['reference']],
            ],
            'enquiry' => $enquiry,
            'notes'   => $notes,
            'flash'   => $this->popFlash(),
        ]);
    }

    public function partnershipUpdate(Request $request, string $id): Response
    {
        $id = (int) $id;
        $enquiry = $this->db->selectOne('SELECT * FROM partnership_enquiries WHERE id = ?', [$id]);
        if ($enquiry === null) return Response::html('<h1>404</h1>', 404);

        $status = $request->string('status');
        if (!in_array($status, ['new','in_review','assigned','responded','resolved','archived'], true)) {
            $status = $enquiry['status'];
        }
        $this->db->execute('UPDATE partnership_enquiries SET status = ? WHERE id = ?', [$status, $id]);
        $this->audit->log('enquiry.update', 'partnership_enquiries', $id, 'Status: ' . $status);

        $note = $request->string('note');
        if ($note !== '') {
            $this->db->execute("
                INSERT INTO enquiry_notes (enquiry_type, enquiry_id, user_id, body)
                VALUES ('partnership', ?, ?, ?)
            ", [$id, $this->auth->id(), $note]);
        }

        $this->flash('Enquiry updated.');
        return Response::redirect('/admin/enquiries/partnership/' . $id);
    }

    /* helpers */

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
}