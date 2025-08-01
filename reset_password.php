<?php
require_once 'config.php';
$token = $_GET['token'] ?? '';

$conn = new mysqli("localhost", "root", "", "out-west");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT email, token_created_at FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Invalid reset token.");
}

// Expire after 15 mins
$createdAt = strtotime($row['token_created_at']);
if (time() - $createdAt > 900) {
    die("Reset token has expired.");
}
$conn->close();
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
    .error { color: red; display: none; }
  </style>
</head>
<body>
  <form class="form" method="POST" action="update_password.php" onsubmit="return validatePasswords()">
    <h2>Reset Your Password</h2>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <label>New Password:</label>
    <input type="password" name="new_password" id="new_password" required>
    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    <p id="password-error" class="error">Passwords do not match!</p>
    <button type="submit">Reset Password</button>
  </form>

  <script>
    function validatePasswords() {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const errorElement = document.getElementById('password-error');
      
      if (newPassword !== confirmPassword) {
        errorElement.style.display = 'block';
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
