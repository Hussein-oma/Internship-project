<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "out-west";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    header("Location: interns_dashboard.php");
    exit();
}

$id = intval($_GET['id']);
$successMessage = '';
$errorMessage = '';

// Fetch existing intern details
$result = $conn->query("SELECT * FROM users WHERE id = $id AND role = 'intern'");
if ($result->num_rows !== 1) {
    $errorMessage = "Intern not found.";
} else {
    $intern = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'] ?? 'inactive';
    $new_password = $_POST['new_password'];

    if ($name === '' || $email === '') {
        $errorMessage = "Name and email cannot be empty.";
    } else {
        // Update name, email, and status
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, account_status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $status, $id);

        if ($stmt->execute()) {
            $successMessage = "Intern updated successfully!";
            $intern['name'] = $name;
            $intern['email'] = $email;
            $intern['account_status'] = $status;
        } else {
            $errorMessage = "Error updating intern.";
        }
        $stmt->close();

        // If new password is entered, update it
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed' WHERE id = $id");
            $successMessage .= " Password updated.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Intern</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
        }

        .container {
            width: 450px;
            margin: 60px auto;
            padding: 30px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #009fd4;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        button[type="submit"] {
            margin-top: 20px;
            background-color: #009fd4;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #007bb0;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-align: center;
            width: 100%;
            background-color: #666;
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #95cb48;
            color: white;
        }

        .message.error {
            background-color: #dc1511;
            color: white;
        }

        .status-select.active {
            border: 2px solid #95cb48;
            background-color: #f0f9eb;
            color: #2e7d32;
        }

        .status-select.inactive {
            border: 2px solid #f44336;
            background-color: #fdecea;
            color: #c62828;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Intern Details</h2>

    <?php if ($successMessage): ?>
        <div class="message success"><?= $successMessage ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="message error"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($intern['name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($intern['email']) ?>" required>

        <label>Account Status:</label>
        <select name="status" class="status-select <?= $intern['account_status'] === 'inactive' ? 'inactive' : 'active' ?>">
            <option value="active" <?= $intern['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $intern['account_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <label>New Password: <small>(leave blank to keep current)</small></label>
        <input type="password" name="new_password">

        <button type="submit">Update Intern</button>
        <a href="interns_dashboard.php" class="back-link">Back</a>
    </form>
</div>
</body>
</html>
