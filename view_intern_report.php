<?php
require_once 'config.php';
session_start();

// Supervisor must be logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: login.php');
    exit();
}

$supervisor_id = $_SESSION['user_id'];

// Fetch all weekly reports submitted by assigned interns
$stmt = $pdo->prepare("SELECT wr.*, u.name AS intern_name FROM weekly_reports wr
    JOIN users u ON wr.intern_id = u.id
    WHERE u.supervisor_id = ?
    ORDER BY wr.week_ending DESC");
$stmt->execute([$supervisor_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count pending weekly reports
$pending_report_stmt = $pdo->prepare("SELECT COUNT(*) FROM weekly_reports wr
    JOIN users u ON wr.intern_id = u.id
    WHERE u.supervisor_id = ? AND wr.status = 'pending'");
$pending_report_stmt->execute([$supervisor_id]);
$pending_reports = $pending_report_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Supervisor View - Weekly Reports</title>
  <link rel="stylesheet" href="weekly-report.css">
  <style>
    .badge {
      background-color: red;
      color: white;
      font-size: 12px;
      font-weight: bold;
      padding: 2px 6px;
      border-radius: 50%;
      position: absolute;
      right: 10px;
      top: 10px;
    }
    .sidebar button {
      position: relative;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <img src="logo.jpeg" alt="logo">
    <button>Dashboard</button>
    <button class="active">
      Reports
      <?php if ($pending_reports > 0): ?>
        <span class="badge"><?= $pending_reports ?></span>
      <?php endif; ?>
    </button>
    <button onclick="location.href='assign_task.php'">Assign Task</button>
    <button onclick="location.href='supervisor_messages.php'">Messages</button>
    <button onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main">
    <h2>Out-west Internship Dashboard</h2>

    <?php foreach ($reports as $report): ?>
    <form method="POST" action="submit_supervisor_review.php">
      <input type="hidden" name="report_id" value="<?= $report['id'] ?>">

      <label><strong>Trainee:</strong> <?= htmlspecialchars($report['intern_name']) ?></label><br>
      <label><strong>Week Ending:</strong> <?= $report['week_ending'] ?></label>

      <table>
        <tr><th>Day</th><th>Description</th></tr>
        <tr><td>MON</td><td><?= nl2br(htmlspecialchars($report['monday'])) ?></td></tr>
        <tr><td>TUE</td><td><?= nl2br(htmlspecialchars($report['tuesday'])) ?></td></tr>
        <tr><td>WED</td><td><?= nl2br(htmlspecialchars($report['wednesday'])) ?></td></tr>
        <tr><td>THUR</td><td><?= nl2br(htmlspecialchars($report['thursday'])) ?></td></tr>
        <tr><td>FRI</td><td><?= nl2br(htmlspecialchars($report['friday'])) ?></td></tr>

        <tr><td colspan="2"><strong>TRAINEE’S WEEKLY REPORT</strong></td></tr>
        <tr><td colspan="2"><?= nl2br(htmlspecialchars($report['weekly_summary'])) ?></td></tr>
        <tr>
          <td>Student’s signature: <?= htmlspecialchars($report['signature']) ?></td>
          <td>Date: <?= $report['report_date'] ?></td>
        </tr>

        <tr><td colspan="2"><strong>SUPERVISOR’S WEEKLY COMMENTS</strong></td></tr>
        <tr>
          <td colspan="2">
            <textarea name="supervisor_comments" class="report-box" required><?= htmlspecialchars($report['supervisor_comments']) ?></textarea>
          </td>
        </tr>
        <tr>
          <td>
            Supervisor’s signature:
            <input type="text" name="supervisor_signature" value="<?= htmlspecialchars($report['supervisor_signature']) ?>" required>
          </td>
          <td>
            Date:
            <input type="date" name="supervisor_date" value="<?= $report['supervisor_date'] ?>" required>
          </td>
        </tr>
      </table>

      <button type="submit">Submit Supervisor Feedback</button>
    </form>
    <hr><br>
    <?php endforeach; ?>
  </div>
</body>
</html>
