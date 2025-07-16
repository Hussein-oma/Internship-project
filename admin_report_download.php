<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

// Fetch interns
$interns_stmt = $pdo->query("
    SELECT 
        u.id, u.name, u.email, u.created_at, u.supervisor_id, 
        s.name AS supervisor_name, 
        a.course, a.level, a.duration,
        COUNT(t.id) AS task_count,
        SUM(CASE WHEN t.submission_file IS NOT NULL AND t.submission_file != '' THEN 1 ELSE 0 END) AS submitted_tasks
    FROM users u
    LEFT JOIN users s ON u.supervisor_id = s.id
    LEFT JOIN internship_applications a ON u.email = a.email
    LEFT JOIN tasks t ON u.id = t.intern_id
    WHERE u.role = 'intern'
    GROUP BY u.id
");
$interns = $interns_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch supervisors
$supervisors_stmt = $pdo->query("
    SELECT 
        s.id, s.name, s.email, s.created_at,
        COUNT(t.id) AS assigned_tasks
    FROM users s
    LEFT JOIN users i ON i.supervisor_id = s.id AND i.role = 'intern'
    LEFT JOIN tasks t ON t.intern_id = i.id
    WHERE s.role = 'supervisor'
    GROUP BY s.id
");
$supervisors = $supervisors_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group interns by supervisor
$interns_by_supervisor_stmt = $pdo->query("SELECT id, name, supervisor_id FROM users WHERE role = 'intern'");
$interns_by_supervisor = [];
foreach ($interns_by_supervisor_stmt as $row) {
    $interns_by_supervisor[$row['supervisor_id']][] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Report PDF</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      font-size: 14px;
    }
    .header-logo {
      text-align: center;
      margin: 10px 0 20px;
    }
    .header-logo img {
      width: 120px;
    }
    h2 {
      text-align: center;
      margin-bottom: 10px;
    }
    .report-date-below {
      text-align: right;
      font-size: 15px;
      font-weight: bold;
      margin-bottom: 15px;
      color: #333;
    }
    .section-title {
      font-size: 18px;
      margin: 30px 0 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #999;
      padding: 5px 8px;
      text-align: left;
    }
    th {
      background-color: #eee;
    }
  </style>
</head>
<body>
<div id="report">

  <!-- Logo Centered -->
  <div class="header-logo">
    <img src="logo.jpeg" alt="Company Logo">
  </div>

  <!-- Report Title -->
  <h2>Admin Internship Report</h2>

  <!-- Date below heading and aligned right -->
  <div class="report-date-below"><?= date("F d, Y") ?></div>

  <!-- Intern Section -->
  <div class="section-title">Intern Task Summary</div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Name</th><th>Email</th><th>Course</th><th>Level</th><th>Duration</th>
        <th>Tasks Assigned</th><th>Tasks Submitted</th><th>Supervisor</th><th>Created At</th><th>End Date</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($interns as $index => $intern):
        $created_at = $intern['created_at'];
        $duration = (int)$intern['duration'];
        $end_date = $duration > 0 ? date('Y-m-d', strtotime("+$duration months", strtotime($created_at))) : 'N/A';
        $status = ($end_date !== 'N/A' && strtotime($end_date) >= time()) ? 'Ongoing' : (($end_date === 'N/A') ? 'N/A' : 'Ended');
      ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= htmlspecialchars($intern['name']) ?></td>
          <td><?= htmlspecialchars($intern['email']) ?></td>
          <td><?= htmlspecialchars($intern['course'] ?? '-') ?></td>
          <td><?= htmlspecialchars($intern['level'] ?? '-') ?></td>
          <td><?= htmlspecialchars($intern['duration'] ?? '-') ?> months</td>
          <td><?= $intern['task_count'] ?></td>
          <td><?= $intern['submitted_tasks'] ?? 0 ?></td>
          <td><?= htmlspecialchars($intern['supervisor_name'] ?? 'Unassigned') ?></td>
          <td><?= date('Y-m-d', strtotime($created_at)) ?></td>
          <td><?= $end_date ?></td>
          <td><?= $status ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Supervisor Section -->
  <div class="section-title">Supervisor Assignment Summary</div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Name</th><th>Email</th><th>Assigned Interns</th><th>Tasks Assigned</th><th>Created At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($supervisors as $index => $supervisor): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= htmlspecialchars($supervisor['name']) ?></td>
          <td><?= htmlspecialchars($supervisor['email']) ?></td>
          <td><?= isset($interns_by_supervisor[$supervisor['id']]) ? htmlspecialchars(implode(', ', $interns_by_supervisor[$supervisor['id']])) : 'None' ?></td>
          <td><?= $supervisor['assigned_tasks'] ?></td>
          <td><?= date('Y-m-d', strtotime($supervisor['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Auto-generate PDF on load (without page numbers) -->
<script>
  window.onload = () => {
    const element = document.getElementById("report");

    const opt = {
      margin: [0.3, 0.3, 0.3, 0.3],
      filename: 'Internship_Report.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: {
        unit: 'in',
        format: 'a4',
        orientation: 'landscape'
      },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(opt).from(element).save();
  };
</script>
</body>
</html>
