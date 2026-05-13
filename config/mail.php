<?php
// config/mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../libs/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-6.9.1/src/SMTP.php';

/**
 * Send an email using PHPMailer
 * @param string $to Email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Email body (Plain text)
 * @return bool True on success, false on failure
 */
function send_mail($to, $subject, $body, $altBody = "") {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USER');
        $mail->Password   = getenv('MAIL_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('MAIL_PORT') ?: 587;

        // Recipients
        $mail->setFrom(getenv('MAIL_FROM') ?: 'no-reply@relational-lens.com', getenv('MAIL_FROM_NAME') ?: 'Relational Lens');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>