<?php
// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $db   = 'out-west';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        $name = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        // Check if email is approved in internship_applications
        $checkStmt = $pdo->prepare("SELECT * FROM internship_applications WHERE email = ? AND status = 'approved'");
        $checkStmt->execute([$email]);

        if ($checkStmt->rowCount() === 0) {
            $errorMessage = "Invalid or unapproved email.";
        } else {
            // Check if account already exists
            $existingUser = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $existingUser->execute([$email]);

            if ($existingUser->rowCount() > 0) {
                $errorMessage = "Account already exists.";
            } elseif ($password !== $confirm) {
                $errorMessage = "Passwords do not match.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'intern')");
                $stmt->execute([$name, $email, $hashedPassword]);

                $successMessage = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Your Account</title>
  <link rel="stylesheet" href="register1.css">
</head>
<body>
  <div class="background-image"></div>

  <div class="register-box">
    <h2>Create Your Account</h2>

    <?php if ($successMessage): ?>
      <div class="message success"><?= $successMessage ?></div>
    <?php elseif ($errorMessage): ?>
      <div class="message error"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label>Name:</label>
      <input type="text" name="username" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <label>Confirm Password:</label>
      <input type="password" name="confirm_password" required>

      <button type="submit">Register</button>
    </form>

    <p>If you have an account already, <a href="login.php" class="login-button">Login</a></p>
  </div>
</body>
</html>
