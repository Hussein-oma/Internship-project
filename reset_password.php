<?php
require_once 'config.php';
$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT email, token_created_at FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    die("Invalid reset token.");
}

// Expire after 15 mins
$createdAt = strtotime($row['token_created_at']);
if (time() - $createdAt > 900) {
    die("Reset token has expired.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="reset.css">
  <style>
    body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f8f8f8; }
    .form { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 400px; width: 100%; }
    .form h2 { margin-bottom: 20px; text-align: center; }
    .form label { margin-top: 10px; display: block; }
    .form input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; }
    .form button { width: 100%; padding: 10px; background: #666; color: #fff; border: none; cursor: pointer; }
  </style>
</head>
<body>
  <form class="form" method="POST" action="update_password.php">
    <h2>Reset Your Password</h2>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <label>New Password:</label>
    <input type="password" name="new_password" required>
    <button type="submit">Reset Password</button>
  </form>
</body>
</html>
