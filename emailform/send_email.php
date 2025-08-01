<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';      // ✅ Gmail SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'husseinadanomar18@gmail.com'; // Gmail address
    $mail->Password   = 'Haski8167';   // Gmail Password
    $mail->SMTPSecure = 'tls';                 // Can also use PHPMailer::ENCRYPTION_STARTTLS
    $mail->Port       = 587;                   // TLS port

    // Recipients
    $mail->setFrom('husseinadanomar18@gmail.com', 'Internship Portal');
    $mail->addAddress('recipient@example.com', 'Recipient Name');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Subject Here';
    $mail->Body    = 'Your HTML message body <b>in bold!</b>';
    $mail->AltBody = 'Plain text message body for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

?>
