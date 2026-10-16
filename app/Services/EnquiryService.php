<?php

declare(strict_types=1);

namespace Mwambe\Services;

use Mwambe\Core\Database;

final class EnquiryService
{
    public function __construct(
        private readonly Database $db,
        private readonly Mailer $mailer,
    ) {}

    public function generateReference(): string
    {
        return strtoupper(bin2hex(random_bytes(5)));
    }

    /**
     * Persist a contact enquiry first, then attempt email. Email failure is
     * recorded but never rolls back the DB commit.
     */
    public function submitContact(array $data): array
    {
        $reference = $this->generateReference();

        $id = $this->db->insert("
            INSERT INTO contact_enquiries
                (reference, name, email, phone, subject, message, privacy_ack, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $reference,
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['subject'] ?? null,
            $data['message'],
            !empty($data['privacy_ack']) ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250),
        ]);

        $notifyTo = (string) \Mwambe\Core\Config::class; // placeholder, replaced below
        $adminEmail = 'mwambeagency@gmail.com';

        $html = $this->renderContactNotification($reference, $data);
        $result = $this->mailer->send(
            $adminEmail,
            'MWAMBE Agency',
            "[Website Enquiry {$reference}] " . ($data['subject'] ?? 'New contact enquiry'),
            $html,
            $data['email'],
            $data['name']
        );

        if ($result['ok']) {
            $this->db->execute('UPDATE contact_enquiries SET email_sent_at = NOW() WHERE id = ?', [$id]);
        } else {
            $this->db->execute('UPDATE contact_enquiries SET email_error = ? WHERE id = ?', [
                substr($result['error'] ?? 'unknown', 0, 500),
                $id,
            ]);
        }

        return ['ok' => true, 'reference' => $reference, 'email_sent' => $result['ok']];
    }

    public function submitPartnership(array $data): array
    {
        $reference = $this->generateReference();

        $id = $this->db->insert("
            INSERT INTO partnership_enquiries
                (reference, organisation_name, contact_person, email, phone, organisation_type,
                 partnership_interest, proposed_collaboration, additional_message, privacy_ack,
                 ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $reference,
            $data['organisation_name'],
            $data['contact_person'],
            $data['email'],
            $data['phone'] ?? null,
            $data['organisation_type'] ?? null,
            $data['partnership_interest'] ?? null,
            $data['proposed_collaboration'],
            $data['additional_message'] ?? null,
            !empty($data['privacy_ack']) ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250),
        ]);

        $adminEmail = 'mwambeagency@gmail.com';
        $html = $this->renderPartnershipNotification($reference, $data);
        $result = $this->mailer->send(
            $adminEmail,
            'MWAMBE Agency',
            "[Partnership Enquiry {$reference}] " . $data['organisation_name'],
            $html,
            $data['email'],
            $data['contact_person']
        );

        if ($result['ok']) {
            $this->db->execute('UPDATE partnership_enquiries SET email_sent_at = NOW() WHERE id = ?', [$id]);
        } else {
            $this->db->execute('UPDATE partnership_enquiries SET email_error = ? WHERE id = ?', [
                substr($result['error'] ?? 'unknown', 0, 500),
                $id,
            ]);
        }

        return ['ok' => true, 'reference' => $reference, 'email_sent' => $result['ok']];
    }

    private function renderContactNotification(string $ref, array $d): string
    {
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        return "<h2>New contact enquiry — {$e($ref)}</h2>"
            . "<p><strong>Name:</strong> {$e($d['name'])}</p>"
            . "<p><strong>Email:</strong> {$e($d['email'])}</p>"
            . ($d['phone'] ?? null ? "<p><strong>Phone:</strong> {$e($d['phone'])}</p>" : '')
            . "<p><strong>Subject:</strong> {$e($d['subject'] ?? '—')}</p>"
            . "<hr><p>" . nl2br($e($d['message'])) . "</p>"
            . "<p style='color:#888;font-size:12px'>Reference: {$e($ref)} · " . date('c') . "</p>";
    }

    private function renderPartnershipNotification(string $ref, array $d): string
    {
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        return "<h2>New partnership enquiry — {$e($ref)}</h2>"
            . "<p><strong>Organisation:</strong> {$e($d['organisation_name'])}</p>"
            . "<p><strong>Contact:</strong> {$e($d['contact_person'])} &lt;{$e($d['email'])}&gt;</p>"
            . ($d['phone'] ?? null ? "<p><strong>Phone:</strong> {$e($d['phone'])}</p>" : '')
            . "<p><strong>Type:</strong> {$e($d['organisation_type'] ?? '—')}</p>"
            . "<p><strong>Interest:</strong> {$e($d['partnership_interest'] ?? '—')}</p>"
            . "<hr><p>" . nl2br($e($d['proposed_collaboration'])) . "</p>"
            . ($d['additional_message'] ?? null ? "<hr><p>" . nl2br($e($d['additional_message'])) . "</p>" : '')
            . "<p style='color:#888;font-size:12px'>Reference: {$e($ref)} · " . date('c') . "</p>";
    }
}