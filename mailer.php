<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function sendHotelMail($to, $subject, $body, $attachmentPath = null)
{
    try {

        $mail = new PHPMailer(true);

        /* SMTP */
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->Port       = (int) $_ENV['MAIL_PORT'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        /* Performance */
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = false;

        /* Sender */
        $mail->setFrom(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME']
        );

        $mail->addAddress($to);

        $mail->addReplyTo(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME']
        );

        /* Content */
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = html_entity_decode(strip_tags($body));

        /* Attachment */
        if (!empty($attachmentPath) && file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }

        $mail->send();
        return true;

    } catch (Exception $e) {

        file_put_contents(
            __DIR__ . '/mail_error_log.txt',
            date('Y-m-d H:i:s') .
            ' | To: ' . $to .
            ' | Subject: ' . $subject .
            ' | Error: ' . $mail->ErrorInfo .
            PHP_EOL,
            FILE_APPEND
        );

        return false;
    }
}
?>