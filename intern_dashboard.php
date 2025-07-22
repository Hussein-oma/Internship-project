<?php
require_once 'config.php';
session_start();

$intern_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$intern_id || $role !== 'intern') {
    header("Location: login.php");
    exit();
}

// Fetch intern profile
$intern_stmt = $pdo->prepare("SELECT name, role, email, supervisor_id FROM users WHERE id = ?");
$intern_stmt->execute([$intern_id]);
$intern = $intern_stmt->fetch();

// Fetch supervisor name
$supervisor_name = 'N/A';
if ($intern['supervisor_id']) {
    $sup_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $sup_stmt->execute([$intern['supervisor_id']]);
    $supervisor = $sup_stmt->fetch();
    $supervisor_name = $supervisor['name'] ?? 'N/A';
}

// Fetch assigned tasks
$task_stmt = $pdo->prepare("SELECT * FROM tasks WHERE intern_id = ? ORDER BY date_issued DESC");
$task_stmt->execute([$intern_id]);
$tasks = $task_stmt->fetchAll();

// Count unsubmitted tasks
$unsubmitted_count = 0;
foreach ($tasks as $t) {
    if (empty($t['submission_file'])) {
        $unsubmitted_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Intern Dashboard</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      display: flex;
      font-family: Arial, sans-serif;
      min-height: 100vh;
    }

    .sidebar {
      width: 140px;
      background-color: #95cb48;
      padding-top: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: fixed;
      height: 100vh;
      left: 0;
      top: 0;
    }

    .sidebar img.logo {
      max-height: 65px;
      margin-bottom: 20px;
    }

    .sidebar button {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      background-color: transparent;
      border: none;
      cursor: pointer;
      text-align: left;
      padding-left: 20px;
      font-weight: bold;
      color: white;
      transition: background-color 0.3s ease;
      position: relative;
    }

    .sidebar button:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar .active {
      background-color: red;
      color: white;
    }

    .badge {
      background-color: white;
      color: red;
      border-radius: 50%;
      padding: 2px 7px;
      font-size: 12px;
      position: absolute;
      right: 10px;
      top: 9px;
    }

    .main {
      margin-left: 140px;
      flex: 1;
      padding: 30px;
    }

    h2 {
      text-align: center;
      background-color: #95cb48;
      color: white;
      padding: 10px;
      margin-bottom: 20px;
      font-size: 20px;
      border-radius: 5px;
      width: fit-content;
      margin-left: auto;
      margin-right: auto;
    }

    .profile-box {
      background: #f9f9f9;
      padding: 15px;
      border: 1px solid #ddd;
      margin-bottom: 20px;
      border-radius: 8px;
    }

    .task-card {
      border: 1px solid #000;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 8px;
      background-color: #fff;
    }

    .task-card textarea {
      width: 100%;
      height: 100px;
      margin-top: 10px;
    }

    .task-card input[type="file"] {
      margin: 10px 0;
    }

    .task-card button {
      background-color: #95cb48;
      color: #fff;
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .task-card button:hover {
      background-color: #7fa940;
    }

    .submitted {
      color: green;
      font-weight: bold;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <img src="logo.jpeg" alt="logo" class="logo">
    <button>Dashboard</button>
    <button onclick="location.href='weekly_report.php'">Reports</button>
    <button onclick="location.href='intern_dashboard.php'" class="active">
      Assigned task
      <?php if ($unsubmitted_count > 0): ?>
        <span class="badge"><?= $unsubmitted_count ?></span>
      <?php endif; ?>
    </button>
    <button onclick="location.href='intern_messages.php'">Messages</button>
    <button onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main">
    <h2>Out-west Internship Dashboard</h2>

    <div class="profile-box">
      <h3>PROFILE</h3>
      <p><strong>Name:</strong> <?= htmlspecialchars($intern['name']) ?></p>
      <p><strong>Role:</strong> <?= htmlspecialchars($intern['role']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($intern['email']) ?></p>
      <p><strong>Assigned Supervisor:</strong> <?= htmlspecialchars($supervisor_name) ?></p>
    </div>

    <h3>Assigned Task</h3>
    <?php if (count($tasks) > 0): ?>
      <?php foreach ($tasks as $task): ?>
        <div class="task-card">
          <form method="POST" action="submit_task_response.php" enctype="multipart/form-data">
            <p><strong>Date Issued:</strong> <?= htmlspecialchars($task['date_issued']) ?></p>
            <p><strong>Submit Date:</strong> <?= htmlspecialchars($task['submit_date']) ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($task['task_description'])) ?></p>

            <?php if (!empty($task['file_path'])): ?>
              <p><strong>Attachment:</strong> <a href="<?= htmlspecialchars($task['file_path']) ?>" target="_blank" download>Download</a></p>
            <?php endif; ?>

            <?php if (!empty($task['submission_file'])): ?>
              <p class="submitted">✔ Submitted on <?= htmlspecialchars($task['submission_date']) ?></p>
              <p><strong>Submitted File:</strong> <a href="<?= htmlspecialchars($task['submission_file']) ?>" target="_blank" download>Download</a></p>
            <?php else: ?>
              <p><strong>Submit task:</strong></p>
              <input type="file" name="submission_file" required>
              <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
              <button type="submit">Submit</button>
            <?php endif; ?>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No tasks assigned yet.</p>
    <?php endif; ?>
  </div>

</body>
</html>
