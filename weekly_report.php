<?php
require_once 'config.php';
session_start();

$intern_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$intern_id) {
    header("Location: login.php");
    exit();
}

// Fetch latest report for this intern
$latestReport = null;
$stmt = $pdo->prepare("SELECT * FROM weekly_reports WHERE intern_id = ? ORDER BY week_ending DESC LIMIT 1");
$stmt->execute([$intern_id]);
$latestReport = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weekly Report</title>
  <link rel="stylesheet" href="weekly-report.css">
</head>
<body>
  <div class="sidebar">
    <img src="logo.jpeg" alt="logo">
    <button>Dashboard</button>
    <button class="active">Reports</button>
    <button onclick="location.href='intern_dashboard.php'">Task assigned</button>
    <button onclick="location.href='intern_messages.php'">Messages</button>
    <button onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main">
    <h2>Intern Internship Dashboard</h2>

    <?php if ($latestReport): ?>
      <h3>Previous Week Report (Week Ending: <?= htmlspecialchars($latestReport['week_ending']) ?>)</h3>
      <table>
        <tr><th>Day</th><th>Description</th></tr>
        <tr><td>MON</td><td><?= nl2br(htmlspecialchars($latestReport['monday'])) ?></td></tr>
        <tr><td>TUE</td><td><?= nl2br(htmlspecialchars($latestReport['tuesday'])) ?></td></tr>
        <tr><td>WED</td><td><?= nl2br(htmlspecialchars($latestReport['wednesday'])) ?></td></tr>
        <tr><td>THUR</td><td><?= nl2br(htmlspecialchars($latestReport['thursday'])) ?></td></tr>
        <tr><td>FRI</td><td><?= nl2br(htmlspecialchars($latestReport['friday'])) ?></td></tr>
        <tr><td colspan="2"><strong>TRAINEE’S REPORT:</strong><br><?= nl2br(htmlspecialchars($latestReport['weekly_summary'])) ?></td></tr>
        <tr><td>Signature:</td><td><?= htmlspecialchars($latestReport['signature']) ?> on <?= htmlspecialchars($latestReport['report_date']) ?></td></tr>
        <?php if (!empty($latestReport['supervisor_comments'])): ?>
        <tr><td colspan="2"><strong>SUPERVISOR’S REPORT:</strong><br><?= nl2br(htmlspecialchars($latestReport['supervisor_comments'])) ?></td></tr>
        <tr><td>Supervisor Signature:</td><td><?= htmlspecialchars($latestReport['supervisor_signature']) ?> on <?= htmlspecialchars($latestReport['supervisor_date']) ?></td></tr>
        <?php endif; ?>
      </table>

      <!-- Download button -->
      <form method="POST" action="download_weekly_report.php" target="_blank">
        <input type="hidden" name="report_id" value="<?= $latestReport['id'] ?>">
        <button type="submit">Download Report</button>
      </form>
      <br>
    <?php endif; ?>

    <!-- Weekly submission form -->
    <form method="POST" action="submit_report.php">
      <input type="hidden" name="intern_id" value="<?= htmlspecialchars($intern_id) ?>">
      <label>WEEK ENDING:</label>
      <input type="date" name="week_ending" required>
      <table>
        <tr><th>Day</th><th>Description of work done and new skills learnt</th></tr>
        <?php
        $days = ['MON' => 'monday', 'TUE' => 'tuesday', 'WED' => 'wednesday', 'THUR' => 'thursday', 'FRI' => 'friday'];
        foreach ($days as $label => $name): ?>
          <tr>
            <td><?= $label ?></td>
            <td><textarea name="<?= $name ?>" required></textarea></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="2"><strong>TRAINEE’S WEEKLY REPORT</strong></td>
        </tr>
        <tr>
          <td colspan="2"><textarea name="weekly_summary" class="report-box" required></textarea></td>
        </tr>
        <tr>
          <td colspan="2" class="footer">
            <label>Student’s signature:
              <input type="text" name="signature" required>
            </label>
            <label>Date:
              <input type="date" name="report_date" required>
            </label>
          </td>
        </tr>
      </table>

      <button type="submit">Submit Report</button>
    </form>
  </div>
</body>
</html>
