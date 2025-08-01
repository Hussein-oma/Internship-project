<?php
require_once 'config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        die("Email is required.");
    }

    // Check if user exists
    $conn = new mysqli("localhost", "root", "", "out-west");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('No account found with that email.'); window.location.href='reset.html';</script>";
        exit();
    }

    $token = bin2hex(random_bytes(32));
    $tokenCreatedAt = date('Y-m-d H:i:s');

    // Save the token to users table
    $update = $conn->prepare("UPDATE users SET reset_token = ?, token_created_at = ? WHERE email = ?");
    $update->bind_param("sss", $token, $tokenCreatedAt, $email);
    $update->execute();

    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/internship-portal/reset_password.php?token=" . urlencode($token);
    
    // Use PHPMailer to send email
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke';
        $mail->Password   = 'nrmv bqpz kmms pcia';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPDebug  = 0;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('omar.hussein2022@students.jkuat.ac.ke', 'Internship Portal');
        $mail->addAddress($email, $user['name'] ?? 'User');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        
        // Email body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>Password Reset Request</h2>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p>You have requested to reset your password.</p>
                    <p>Please click the button below to reset your password:</p>
                </div>
                <p><a href='{$resetLink}' style='display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Reset Password</a></p>
                <p>Or copy and paste this link in your browser:</p>
                <p>{$resetLink}</p>
                <p>This link will expire in 15 minutes.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
            </div>
        ";
        
        $mail->AltBody = "You have requested to reset your password.\n\nPlease visit the following link to reset your password:\n\n{$resetLink}\n\nThis link will expire in 15 minutes.\n\nIf you did not request a password reset, please ignore this email.";
        
        $mail->send();
        echo "<script>alert('✅ Reset link sent to your email.'); window.location.href='login.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email. Please try again. Error: " . addslashes($mail->ErrorInfo) . "'); window.location.href='reset.html';</script>";
    }
    
    $conn->close();
}
?>