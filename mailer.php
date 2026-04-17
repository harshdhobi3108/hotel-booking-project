<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| HotelLux - mailer.php (Updated Production Version)
|--------------------------------------------------------------------------
| Features:
| ✅ Secure SMTP setup
| ✅ Better error handling
| ✅ HTML email support
| ✅ Attachment support
| ✅ Reusable function
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
| Replace these with your real Gmail details
| Use Gmail App Password (not normal password)
|--------------------------------------------------------------------------
*/

function sendHotelMail($to, $subject, $body, $attachmentPath = null)
{
    try {

        $mail = new PHPMailer(true);

        /* ================= SMTP ================= */

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'harshdhobi31@gmail.com';
        $mail->Password   = 'usuh xaur xhxb qeul';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        /* ================= MAIL INFO ================= */

        $mail->setFrom('harshdhobi31@gmail.com', 'HotelLux');
        $mail->addAddress($to);

        $mail->addReplyTo('harshdhobi31@gmail.com', 'HotelLux Support');

        /* ================= CONTENT ================= */

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->AltBody = strip_tags($body);

        /* ================= ATTACHMENT ================= */

        if (!empty($attachmentPath) && file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }

        /* ================= SEND ================= */

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log("Mail Error: " . $mail->ErrorInfo);

        return false;
    }
}
?>