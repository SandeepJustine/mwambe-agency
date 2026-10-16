<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Application;
use Mwambe\Core\Csrf;
use Mwambe\Core\Database;
use Mwambe\Core\Http\Request;
use Mwambe\Core\Http\Response;
use Mwambe\Services\EnquiryService;

final class EnquiryController
{
    public function __construct(
        private readonly Application $app,
        private readonly Csrf $csrf,
        private readonly Database $db,
        private readonly EnquiryService $enquiries,
    ) {}

    public function showContact(Request $request): Response
    {
        return $this->renderForm('public/forms/contact', 'Contact MWAMBE', [
            'success' => null,
            'errors'  => [],
            'old'     => [],
        ]);
    }

    public function submitContact(Request $request): Response
    {
        // Honeypot: bots fill this hidden field
        if ($request->string('website') !== '') {
            // Pretend success — don't reveal
            return $this->renderForm('public/forms/contact', 'Contact MWAMBE', [
                'success' => 'Thank you. We will be in touch.',
                'errors'  => [],
                'old'     => [],
            ]);
        }

        if (!$this->underRateLimit('contact')) {
            return $this->renderForm('public/forms/contact', 'Contact MWAMBE', [
                'success' => null,
                'errors'  => ['_form' => 'Too many submissions. Please try again later.'],
                'old'     => $request->body,
            ]);
        }

        $data = [
            'name'        => $request->string('name'),
            'email'       => $request->string('email'),
            'phone'       => $request->string('phone'),
            'subject'     => $request->string('subject'),
            'message'     => $request->string('message'),
            'privacy_ack' => $request->input('privacy_ack'),
        ];

        $errors = [];
        if ($data['name'] === '')    $errors['name'] = 'Please enter your name.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
        if ($data['message'] === '') $errors['message'] = 'Please enter a message.';
        if (empty($data['privacy_ack'])) $errors['privacy_ack'] = 'Please acknowledge the privacy notice.';

        if ($errors) {
            return $this->renderForm('public/forms/contact', 'Contact MWAMBE', [
                'success' => null,
                'errors'  => $errors,
                'old'     => $request->body,
            ]);
        }

        $result = $this->enquiries->submitContact($data);

        return $this->renderForm('public/forms/contact', 'Contact MWAMBE', [
            'success' => "Thank you. Your message has been received. Reference: {$result['reference']}",
            'errors'  => [],
            'old'     => [],
        ]);
    }

    public function showPartnership(Request $request): Response
    {
        return $this->renderForm('public/forms/partnership', 'Partner With Us', [
            'success' => null,
            'errors'  => [],
            'old'     => [],
        ]);
    }

    public function submitPartnership(Request $request): Response
    {
        if ($request->string('website') !== '') {
            return $this->renderForm('public/forms/partnership', 'Partner With Us', [
                'success' => 'Thank you. We will be in touch.',
                'errors'  => [],
                'old'     => [],
            ]);
        }

        if (!$this->underRateLimit('partnership')) {
            return $this->renderForm('public/forms/partnership', 'Partner With Us', [
                'success' => null,
                'errors'  => ['_form' => 'Too many submissions. Please try again later.'],
                'old'     => $request->body,
            ]);
        }

        $data = [
            'organisation_name'      => $request->string('organisation_name'),
            'contact_person'         => $request->string('contact_person'),
            'email'                  => $request->string('email'),
            'phone'                  => $request->string('phone'),
            'organisation_type'      => $request->string('organisation_type'),
            'partnership_interest'   => $request->string('partnership_interest'),
            'proposed_collaboration' => $request->string('proposed_collaboration'),
            'additional_message'     => $request->string('additional_message'),
            'privacy_ack'            => $request->input('privacy_ack'),
        ];

        $errors = [];
        if ($data['organisation_name'] === '') $errors['organisation_name'] = 'Please enter your organisation name.';
        if ($data['contact_person'] === '')    $errors['contact_person'] = 'Please enter a contact name.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
        if ($data['proposed_collaboration'] === '') $errors['proposed_collaboration'] = 'Please describe the proposed collaboration.';
        if (empty($data['privacy_ack'])) $errors['privacy_ack'] = 'Please acknowledge the privacy notice.';

        if ($errors) {
            return $this->renderForm('public/forms/partnership', 'Partner With Us', [
                'success' => null, 'errors' => $errors, 'old' => $request->body,
            ]);
        }

        $result = $this->enquiries->submitPartnership($data);

        return $this->renderForm('public/forms/partnership', 'Partner With Us', [
            'success' => "Thank you. Your partnership enquiry has been received. Reference: {$result['reference']}",
            'errors'  => [],
            'old'     => [],
        ]);
    }

    /* ------------- helpers ------------- */

    private function underRateLimit(string $type): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $table = $type === 'contact' ? 'contact_enquiries' : 'partnership_enquiries';
        $count = (int) $this->db->selectOne(
            "SELECT COUNT(*) AS c FROM {$table} WHERE ip_address = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)",
            [$ip]
        )['c'];
        return $count < 5;
    }

    private function renderForm(string $view, string $title, array $data): Response
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        $app  = $this->app;
        $csrf = $this->csrf;
        ob_start();
        require BASE_PATH . '/app/Views/layouts/public.php';
        return Response::html((string) ob_get_clean());
    }
}