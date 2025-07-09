<?php 
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

// Fetch supervisor info
$supervisor_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$supervisor_stmt->execute([$supervisor_id]);
$supervisor = $supervisor_stmt->fetch();

// Fetch assigned interns
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'intern' AND supervisor_id = ?");
$stmt->execute([$supervisor_id]);
$interns = $stmt->fetchAll();
$intern_names = array_column($interns, 'name');

// Count pending submitted tasks
$pending_tasks_stmt = $pdo->prepare("
  SELECT COUNT(*) FROM tasks 
  WHERE intern_id IN (SELECT id FROM users WHERE supervisor_id = ?) 
  AND status = 'pending' AND submission_file IS NOT NULL
");
$pending_tasks_stmt->execute([$supervisor_id]);
$pending_tasks_count = $pending_tasks_stmt->fetchColumn();

// Count pending weekly reports
$pending_reports_stmt = $pdo->prepare("
  SELECT COUNT(*) FROM weekly_reports 
  WHERE intern_id IN (SELECT id FROM users WHERE supervisor_id = ?) 
  AND status = 'pending'
");
$pending_reports_stmt->execute([$supervisor_id]);
$pending_reports_count = $pending_reports_stmt->fetchColumn();

// Handle filters
$filter_intern = $_GET['filter_intern'] ?? null;
$filter_status = $_GET['filter_status'] ?? null;

if ($filter_intern && $filter_status) {
    $task_stmt = $pdo->prepare("
        SELECT t.*, u.name AS intern_name 
        FROM tasks t 
        JOIN users u ON t.intern_id = u.id 
        WHERE u.supervisor_id = ? AND u.id = ? AND t.status = ?
        ORDER BY t.date_issued DESC
    ");
    $task_stmt->execute([$supervisor_id, $filter_intern, $filter_status]);
} elseif ($filter_intern) {
    $task_stmt = $pdo->prepare("
        SELECT t.*, u.name AS intern_name 
        FROM tasks t 
        JOIN users u ON t.intern_id = u.id 
        WHERE u.supervisor_id = ? AND u.id = ?
        ORDER BY t.date_issued DESC
    ");
    $task_stmt->execute([$supervisor_id, $filter_intern]);
} elseif ($filter_status) {
    $task_stmt = $pdo->prepare("
        SELECT t.*, u.name AS intern_name 
        FROM tasks t 
        JOIN users u ON t.intern_id = u.id 
        WHERE u.supervisor_id = ? AND t.status = ?
        ORDER BY t.date_issued DESC
    ");
    $task_stmt->execute([$supervisor_id, $filter_status]);
} else {
    $task_stmt = $pdo->prepare("
        SELECT t.*, u.name AS intern_name 
        FROM tasks t 
        JOIN users u ON t.intern_id = u.id 
        WHERE u.supervisor_id = ?
        ORDER BY t.date_issued DESC
    ");
    $task_stmt->execute([$supervisor_id]);
}
$previous_tasks = $task_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Task</title>
  <link rel="stylesheet" href="weekly-report.css">
  <style>
    body {
      display: flex;
      min-height: 100vh;
      font-family: Arial, sans-serif;
    }
    .sidebar {
      width: 140px;
      background-color: #f0f0f0;
      border-right: 1px solid #333;
      padding-top: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .sidebar img {
      max-height: 65px;
      margin-bottom: 15px;
    }
    .sidebar button {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      background-color: #ccc;
      border: none;
      text-align: left;
      padding-left: 20px;
      font-weight: bold;
      cursor: pointer;
      position: relative;
    }
    .sidebar .active {
      background-color: #999;
      color: white;
    }
    .badge {
      background-color: red;
      color: white;
      border-radius: 50%;
      padding: 2px 7px;
      font-size: 12px;
      position: absolute;
      right: 10px;
      top: 9px;
    }
    .main {
      flex: 1;
      padding: 30px;
    }
    .profile-box, .task-box, .previous-tasks {
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background-color: #f9f9f9;
    }
    textarea {
      width: 100%;
      height: 100px;
      margin-bottom: 10px;
    }
    input[type="file"] {
      margin: 10px 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #333;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f0f0f0;
    }
    .filter-form {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <img src="logo.jpeg" alt="logo">
    <button>Dashboard</button>
    <button onclick="location.href='view_intern_report.php'">
      Reports
      <?php if ($pending_reports_count > 0): ?>
        <span class="badge"><?= $pending_reports_count ?></span>
      <?php endif; ?>
    </button>
    <button class="active">
      Assign task
      <?php if ($pending_tasks_count > 0): ?>
        <span class="badge"><?= $pending_tasks_count ?></span>
      <?php endif; ?>
    </button>
    <button onclick="location.href='supervisor_messages.php'">messages</button>
    <button onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main">
    <h2>Out-west Internship Dashboard</h2>

    <div class="profile-box">
      <h3>Profile</h3>
      <p><strong>Name:</strong> <?= htmlspecialchars($supervisor['name']) ?></p>
      <p><strong>Role:</strong> Supervisor</p>
      <p><strong>Email:</strong> <?= htmlspecialchars($supervisor['email']) ?></p>
      <p><strong>Assigned Interns:</strong>
        <?= count($intern_names) > 0 ? implode(', ', array_map('htmlspecialchars', $intern_names)) : 'None' ?>
      </p>
    </div>

    <div class="task-box">
      <h3>Assign Task</h3>
      <form method="POST" action="submit_task.php" enctype="multipart/form-data">
        <label>Intern to be assigned work:</label>
        <select name="intern_id" required>
          <option value="">-- Select Intern --</option>
          <?php foreach ($interns as $intern): ?>
            <option value="<?= $intern['id'] ?>"><?= htmlspecialchars($intern['name']) ?></option>
          <?php endforeach; ?>
          <option value="all">All Interns</option>
        </select>

        <div style="float:right; text-align:right;">
          <label>Date Issued:</label>
          <input type="date" name="date_issued" required><br>
          <label>Submit Date:</label>
          <input type="date" name="submit_date" required>
        </div>

        <textarea name="task_description" placeholder="Write task details here..." required></textarea>
        <input type="file" name="attachment">
        <button type="submit">Submit</button>
      </form>
    </div>

    <form method="GET" class="filter-form">
      <label>Filter by Intern:</label>
      <select name="filter_intern">
        <option value="">-- All Interns --</option>
        <?php foreach ($interns as $intern): ?>
          <option value="<?= $intern['id'] ?>" <?= $filter_intern == $intern['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($intern['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Filter by Status:</label>
      <select name="filter_status">
        <option value="">-- All Statuses --</option>
        <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
      </select>

      <button type="submit">Apply Filters</button>
    </form>

    <div class="previous-tasks">
      <h3>Previously Assigned Tasks</h3>
      <?php if (count($previous_tasks) > 0): ?>
        <table>
          <tr>
            <th>#</th>
            <th>Intern</th>
            <th>Task Description</th>
            <th>Date Issued</th>
            <th>Submit Date</th>
            <th>Attachments</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          <?php foreach ($previous_tasks as $index => $task): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($task['intern_name']) ?></td>
              <td><?= nl2br(htmlspecialchars($task['task_description'])) ?></td>
              <td><?= htmlspecialchars($task['date_issued']) ?></td>
              <td><?= htmlspecialchars($task['submit_date']) ?></td>
              <td>
                <strong>Assigned:</strong><br>
                <?= $task['file_path'] ? "<a href='" . htmlspecialchars($task['file_path']) . "' target='_blank' download>Download</a>" : "<em>None</em>" ?>
                <br><br>
                <strong>Submitted:</strong><br>
                <?= $task['submission_file'] ? "<a href='" . htmlspecialchars($task['submission_file']) . "' target='_blank' download>Download</a>" : "<em>Not submitted</em>" ?>
              </td>
              <td><?= htmlspecialchars($task['status'] ?? 'N/A') ?></td>
              <td>
                <a href="edit_task.php?id=<?= $task['id'] ?>">Edit</a> |
                <a href="delete_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>No tasks assigned yet.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
