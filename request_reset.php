<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        die("Email is required.");
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        die("No account found with that email.");
    }

    $token = bin2hex(random_bytes(32));
    $tokenCreatedAt = date('Y-m-d H:i:s');

    // Save the token to users table
    $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_created_at = ? WHERE email = ?");
    $update->execute([$token, $tokenCreatedAt, $email]);

    $resetLink = "http://yourdomain.com/reset_password.php?token=" . urlencode($token);
    $subject = "Password Reset Request";
    $message = "Hi,\n\nClick below to reset your password:\n$resetLink\n\nThis link expires in 15 minutes.";
    $headers = "From: no-reply@yourdomain.com";

    // Send email
    mail($email, $subject, $message, $headers);

    echo "✅ Reset link sent to your email.";
}
?>