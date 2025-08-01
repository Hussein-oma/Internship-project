<?php
// Handle form submission
$successMessage = '';
$errorMessage = '';
$token = $_GET['token'] ?? '';
$applicant_id = $_GET['id'] ?? '';
$validToken = false;
$applicantEmail = '';

// Database connection
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
    
    // Create registration_tokens table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS registration_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        applicant_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        used TINYINT(1) DEFAULT 0,
        INDEX (token),
        INDEX (applicant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Verify token if provided
    if ($token && $applicant_id) {
        // Verify the token without using applicant_id column
        $tokenStmt = $pdo->prepare("SELECT * FROM registration_tokens 
                                  WHERE token = ? AND expires_at > NOW() AND used = 0");
        $tokenStmt->execute([$token]);
        $tokenData = $tokenStmt->fetch();
        
        if ($tokenData) {
            // Get applicant email
            $applicantStmt = $pdo->prepare("SELECT email, fullname FROM internship_applications WHERE id = ? AND status = 'approved'");
            $applicantStmt->execute([$applicant_id]);
            $applicantData = $applicantStmt->fetch();
            
            if ($applicantData) {
                $validToken = true;
                $applicantEmail = $applicantData['email'];
            }
        }
    }
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $registrationToken = $_POST['token'] ?? '';
        $registrationId = $_POST['applicant_id'] ?? '';
        
        // Verify token again for security
        $validRegistration = false;
        
        if ($registrationToken && $registrationId) {
            // Get the email from internship_applications
            $emailStmt = $pdo->prepare("SELECT email FROM internship_applications WHERE id = ? AND status = 'approved'");
            $emailStmt->execute([$registrationId]);
            $applicantData = $emailStmt->fetch();
            
            if ($applicantData && $applicantData['email'] === $email) {
                // Verify the token without using applicant_id
                $verifyStmt = $pdo->prepare("SELECT * FROM registration_tokens 
                                          WHERE token = ? AND expires_at > NOW() AND used = 0");
                $verifyStmt->execute([$registrationToken]);
                
                if ($verifyStmt->rowCount() > 0) {
                    $validRegistration = true;
                }
            }
        }
        
        // Check if email is approved in internship_applications (fallback for direct access)
        if (!$validRegistration) {
            $checkStmt = $pdo->prepare("SELECT * FROM internship_applications WHERE email = ? AND status = 'approved'");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $validRegistration = true;
            }
        }
        
        if (!$validRegistration) {
            $errorMessage = "Invalid or unapproved registration link.";
        } else {
            // Check if account already exists
            $existingUser = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $existingUser->execute([$email]);
            $existingUserData = $existingUser->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUserData) {
                $errorMessage = "Account already exists.";
            } elseif ($password !== $confirm) {
                $errorMessage = "Passwords do not match.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Create a new user with intern role
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'intern')");
                $stmt->execute([$name, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();
                
                // Get the application data to link with the user
                if ($registrationId) {
                    $appStmt = $pdo->prepare("SELECT * FROM internship_applications WHERE id = ?");
                    $appStmt->execute([$registrationId]);
                    $appData = $appStmt->fetch();
                } else {
                    $appStmt = $pdo->prepare("SELECT * FROM internship_applications WHERE email = ?");
                    $appStmt->execute([$email]);
                    $appData = $appStmt->fetch();
                }
                
                // Mark token as used if registration was via token
                if ($registrationToken) {
                    // Update the token to mark it as used
                    $updateTokenStmt = $pdo->prepare("UPDATE registration_tokens SET used = 1 WHERE token = ?");
                    $updateTokenStmt->execute([$registrationToken]);
                }

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
      <?php if ($validToken && $token && $applicant_id): ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="applicant_id" value="<?= htmlspecialchars($applicant_id) ?>">
      <?php endif; ?>
      
      <label>Name:</label>
      <input type="text" name="username" required>

      <label>Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($applicantEmail) ?>" <?= $validToken ? 'readonly' : '' ?> required>

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
