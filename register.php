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

        $name = trim($_POST['username']);  // Captures the name input
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($password !== $confirm) {
            $errorMessage = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Use `name` to match your actual database column
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'intern')");
            $stmt->execute([$name, $email, $hashedPassword]);

            $successMessage = "Registration successful! Redirecting to login...";
            header("refresh:2;url=login.php"); // Redirect after 2 seconds
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
  <link rel="stylesheet" href="register.css">
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

      <label>Confirm password:</label>
      <input type="password" name="confirm_password" required>

      <button type="submit">Register</button>
    </form>

    <p>If you have an account already, <a href="login.php" class="login-button">Login</a></p>
  </div>
</body>
</html>
