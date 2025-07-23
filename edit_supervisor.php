<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: supervisor_dashboard.php");
    exit();
}

$id = $_GET['id'];

// Fetch current supervisor data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supervisor) {
    echo "Supervisor not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $account_status = $_POST['account_status'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, account_status = ? WHERE id = ?");
        $update->execute([$name, $email, $password, $account_status, $id]);
    } else {
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, account_status = ? WHERE id = ?");
        $update->execute([$name, $email, $account_status, $id]);
    }

    header("Location: supervisor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Supervisor</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background-color: #f9f9f9;
    }

    form {
      max-width: 400px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      margin-top: 20px;
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <form method="POST">
    <h2>Edit Supervisor</h2>
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($supervisor['name']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($supervisor['email']) ?>" required>

    <label>Password (leave blank to keep unchanged)</label>
    <input type="password" name="password">

    <label>Account Status</label>
    <select name="account_status" required>
      <option value="active" <?= $supervisor['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="inactive" <?= $supervisor['account_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>

    <button type="submit">Update Supervisor</button>
  </form>
</body>
</html>
