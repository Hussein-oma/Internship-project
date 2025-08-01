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
            $account_status = $user['account_status'] ?? 'inactive';

            // DENY ACCESS if account is not active
            if ($account_status !== 'active') {
                $message = "Access denied. Your account is not active. Contact Admin.";
            } else {
                // INTERN LOGIN CHECK with session end validation
                if ($user['role'] === 'intern') {
                    $intern_email = $conn->real_escape_string($user['email']);
                    $app_query = $conn->query("SELECT duration FROM internship_applications WHERE email = '$intern_email' ORDER BY id DESC LIMIT 1");

                    $duration = 0;
                    if ($app_row = $app_query->fetch_assoc()) {
                        $duration = (int)$app_row['duration'];
                    }

                    $created_at = strtotime($user['created_at']);
                    $end_date = strtotime("+{$duration} months", $created_at);
                    $today = time();
                    $is_active = $duration > 0 && $end_date >= $today;

                    if (!$is_active) {
                        $message = "Access denied. Your internship period has ended.";
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        header("Location: weekly_report.php");
                        exit();
                    }

                } elseif ($user['role'] === 'supervisor') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: view_intern_report.php");
                    exit();

                } elseif ($user['role'] === 'admin') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: internship_field.php");
                    exit();

                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: dashboard.php");
                    exit();
                }
            }
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Internship Portal</title>
  <link rel="stylesheet" href="login.css">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#009fd4">
  <link rel="apple-touch-icon" href="icons/icon-192x192.png">
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

    .message {
      text-align: center;
      padding: 10px;
      margin-bottom: 15px;
      font-size: 14px;
      border-radius: 4px;
    }

    .message.error {
      background-color: #ffe6e6;
      border: 1px solid #dc1511;
      color: #dc1511;
    }

    .message.success {
      background-color: #e6f4ea;
      border: 1px solid #28a745;
      color: #28a745;
    }
    
    /* Add install prompt styles */
    #installPrompt {
      display: none;
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #009fd4;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      z-index: 1000;
    }
    
    #installButton {
      background-color: white;
      color: #009fd4;
      border: none;
      padding: 5px 10px;
      margin-left: 10px;
      border-radius: 3px;
      cursor: pointer;
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
        <a href="reset.html">Forgot Password?</a>
      </div>

      <button type="submit">Login</button>
    </form>

    <p>If you don't have an account already,
      <a href="register.php" class="register-link">Register</a>
    </p>
  </div>
  
  <!-- Install prompt for PWA -->
  <div id="installPrompt">
    Install this app on your device
    <button id="installButton">Install</button>
  </div>
  
  <script>
    // Register service worker for PWA functionality
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker.js')
          .then(registration => {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          })
          .catch(error => {
            console.log('ServiceWorker registration failed: ', error);
          });
      });
    }
    
    // Handle PWA installation
    let deferredPrompt;
    const installPrompt = document.getElementById('installPrompt');
    const installButton = document.getElementById('installButton');
    
    window.addEventListener('beforeinstallprompt', (e) => {
      // Prevent Chrome 67 and earlier from automatically showing the prompt
      e.preventDefault();
      // Stash the event so it can be triggered later
      deferredPrompt = e;
      // Show the install prompt
      installPrompt.style.display = 'block';
    });
    
    installButton.addEventListener('click', () => {
      // Hide the app provided install promotion
      installPrompt.style.display = 'none';
      // Show the install prompt
      deferredPrompt.prompt();
      // Wait for the user to respond to the prompt
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('User accepted the install prompt');
        } else {
          console.log('User dismissed the install prompt');
        }
        deferredPrompt = null;
      });
    });
  </script>
</body>
</html>
