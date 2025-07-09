<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "out-west");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'intern') {
                header("Location: weekly_report.php");
            } elseif ($user['role'] === 'supervisor') {
                header("Location: view_intern_report.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: internship_field.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "Email not found.";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="login.css">
  <style>
    .forgot-password {
      display: block;
      margin-top: 10px;
      text-align: right;
    }
    .forgot-password a {
      color: #007BFF;
      text-decoration: none;
    }
    .forgot-password a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="background-image"></div>
  <div class="login-box">
    <h2>Log in to your account</h2>

    <?php if ($message): ?>
      <div class="message error"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="email">Email:</label>
      <input type="email" name="email" required>

      <label for="password">Password:</label>
      <input type="password" name="password" required>

      <div class="forgot-password">
        <a href="reset_password.php">Forgot Password?</a>
      </div>

      <button type="submit">Login</button>
    </form>

    <p>If you don’t have an account already,
      <a href="register.php" class="register-link">Register</a>
    </p>
  </div>
</body>
</html>
