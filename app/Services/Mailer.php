<?php

declare(strict_types=1);

namespace Mwambe\Services;

use Mwambe\Core\Application;
use Mwambe\Core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

final class Mailer
{
    public function __construct(private readonly Config $config, private readonly Application $app) {}

    /** @return array{ok:bool,error?:string} */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $replyToEmail = '', string $replyToName = ''): array
    {
        $host = (string) $this->config->get('MAIL_HOST', '');
        if ($host === '') {
            $this->app->log('warning', 'Mail not sent: MAIL_HOST is empty', ['subject' => $subject]);
            return ['ok' => false, 'error' => 'Mail host not configured.'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->Port       = (int) $this->config->get('MAIL_PORT', 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) $this->config->get('MAIL_USERNAME', '');
            $mail->Password   = (string) $this->config->get('MAIL_PASSWORD', '');
            $encryption       = (string) $this->config->get('MAIL_ENCRYPTION', 'tls');
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            $mail->CharSet = 'UTF-8';

            $fromEmail = (string) $this->config->get('MAIL_FROM_ADDRESS', 'noreply@localhost');
            $fromName  = (string) $this->config->get('MAIL_FROM_NAME', 'MWAMBE Agency');
            $mail->setFrom($fromEmail, $fromName);

            if ($replyToEmail !== '') {
                $mail->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $htmlBody));

            $mail->send();
            return ['ok' => true];
        } catch (MailerException $e) {
            $this->app->log('error', 'Mail send failed', ['error' => $mail->ErrorInfo ?: $e->getMessage(), 'to' => $toEmail]);
            return ['ok' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
        } catch (\Throwable $e) {
            $this->app->log('error', 'Mail send threw', ['error' => $e->getMessage(), 'to' => $toEmail]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}