<?php
require_once 'config.php';

$searchResults = [];
$searched = false;
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $searched = true;
    $email = $_GET['email'];
    $stmt = $pdo->prepare("SELECT fullname, email, course, status FROM internship_applications WHERE email LIKE ?");
    $stmt->execute(["%$email%"]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Application Status</title>
  <link rel="stylesheet" href="status.css">
  <style>
    .error-message {
      color: red;
      text-align: center;
      margin: 10px 0;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-container">
      <img src="logo.jpeg" alt="Out-west Logo" class="logo-img">
    </div>

    <h2>Check the status of your application</h2>

    <form method="get" class="search-bar">
      <label for="email">Search by Email:</label>
      <input
        type="text"
        name="email"
        id="email"
        value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>"
      />
      <button type="submit">Search</button>
    </form>

    <?php if ($searched && empty($searchResults)): ?>
      <div class="error-message">Invalid email or no record found.</div>
    <?php endif; ?>

    <?php if (!empty($searchResults)): ?>
      <table class="result-table">
        <thead>
          <tr>
            <th>Name:</th>
            <th>Email:</th>
            <th>Course:</th>
            <th>Status:</th>
          </tr>
        </thead>
        <tbody id="resultBody">
          <?php foreach ($searchResults as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['fullname']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['course']) ?></td>
              <td>
                <?= htmlspecialchars($row['status']) ?>
                <?php if (strtolower($row['status']) === 'approved'): ?>
                  <br>
                  <a href="register.php">Click here to register</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
