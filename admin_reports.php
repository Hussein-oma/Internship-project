<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
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

// Interns grouped by supervisor
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
  <title>Admin Reports</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      height: 100vh;
    }

    .sidebar {
      width: 140px;
      height: 100vh;
      background-color: #95cb48;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
      color: white;
      border-right: 1px solid #7ca63c;
    }

    .sidebar .logo {
      width: 80px;
      height: auto;
      margin-bottom: 25px;
    }

    .sidebar button {
      width: 100px;
      margin: 8px 0;
      padding: 7px 10px;
      background-color: transparent;
      border: none;
      color: white;
      font-weight: bold;
      cursor: pointer;
      text-align: center;
      border-radius: 4px;
      transition: background-color 0.2s ease;
    }

    .sidebar button:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar button.active {
      background-color: #dc1511;
      color: white;
    }

    .main {
      flex-grow: 1;
      margin-left: 140px;
      padding: 20px;
      overflow-x: auto;
      background-color: #f6fdf3;
    }

    h2 {
      margin-top: 0;
      color: #4a7c12;
    }

    .section-title {
      font-size: 20px;
      margin: 30px 0 10px;
      color: #4a7c12;
    }

    .search-box {
      margin: 10px 0 20px;
    }

    .search-box input {
      padding: 6px;
      width: 300px;
      font-size: 14px;
      border: 1px solid #aaa;
      border-radius: 4px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      font-size: 14px;
      background-color: #fff;
    }

    th, td {
      border: 1px solid #b2d59d;
      padding: 8px 10px;
      white-space: nowrap;
    }

    th {
      background-color: #d9eacb;
      color: #2d5c08;
    }

    .download-button {
      margin-top: 10px;
      padding: 8px 14px;
      background-color: #6c9f2e;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
      border-radius: 4px;
    }

    .download-button:hover {
      background-color: #558321;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="logo.jpeg" alt="Logo" class="logo">
  <button onclick="location.href='admin_dashboard.php'">Dashboard</button>
  <button onclick="location.href='interns_dashboard.php'">Internship Field</button>
  <button onclick="location.href='admin_dashboard.php'">Applications</button>
  <button onclick="location.href='interns_dashboard.php'">Interns</button>
  <button onclick="location.href='supervisor_dashboard.php'">Supervisors</button>
  <button onclick="location.href='admin_messages.php'">Messages</button>
  <button class="active">Reports</button>
  <button onclick="location.href='logout.php'">Log out</button>
</div>

<!-- Main content -->
<div class="main">
  <h2>Admin Reports</h2>

  <!-- Search -->
  <div class="search-box">
    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by intern email...">
  </div>

  <!-- Intern Report Table -->
  <div class="section-title">Intern Task Summary</div>
  <table id="internTable">
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
          <td><?= htmlspecialchars(date('Y-m-d', strtotime($created_at))) ?></td>
          <td><?= $end_date ?></td>
          <td><?= $status ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Supervisor Report Table -->
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
          <td><?= htmlspecialchars(date('Y-m-d', strtotime($supervisor['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Download Button -->
  <form method="get" action="admin_report_download.php" target="_blank">
    <button type="submit" class="download-button">Download Report as PDF</button>
  </form>
</div>

<!-- Search Script -->
<script>
  function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll("#internTable tbody tr");

    rows.forEach(row => {
      const emailCell = row.cells[2]; // email column
      if (emailCell) {
        const emailText = emailCell.textContent.toLowerCase();
        row.style.display = emailText.includes(filter) ? "" : "none";
      }
    });
  }
</script>

</body>
</html>
