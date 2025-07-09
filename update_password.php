<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($token) || empty($new_password)) {
        die("All fields are required.");
    }

    // Find user with token
    $stmt = $pdo->prepare("SELECT id, token_created_at FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Invalid or expired token.");
    }

    $created = strtotime($user['token_created_at']);
    if (time() - $created > 900) {
        die("Reset token expired. Request a new one.");
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and clear token
    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_created_at = NULL WHERE id = ?");
    $update->execute([$hashed, $user['id']]);

    echo "<script>alert('✅ Password updated. You can now log in.'); window.location.href='login.php';</script>";
    exit();
}
?>