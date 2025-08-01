<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Find user with token
    $conn = new mysqli("localhost", "root", "", "out-west");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, token_created_at FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("Invalid or expired token.");
    }

    $created = strtotime($user['token_created_at']);
    if (time() - $created > 900) {
        die("Reset token expired. Request a new one.");
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and clear token
    $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_created_at = NULL WHERE id = ?");
    $update->bind_param("si", $hashed, $user['id']);
    $update->execute();

    echo "<script>alert('✅ Password updated. You can now log in.'); window.location.href='login.php';</script>";
    exit();
}
?>