<?php
require_once 'config.php';

// Handle filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch applications
try {
    if ($status_filter === 'all') {
        $stmt = $pdo->query("SELECT * FROM internship_applications ORDER BY id DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM internship_applications WHERE status = ? ORDER BY id DESC");
        $stmt->execute([$status_filter]);
    }
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching applications: " . $e->getMessage());
}

// Count pending applications for badge
$pendingCount = 0;
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM internship_applications WHERE status = 'pending'");
    $pendingCount = $countStmt->fetchColumn();
} catch (PDOException $e) {
    $pendingCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Out-west Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css">
  <style>
    .badge {
      background: red;
      color: white;
      padding: 2px 6px;
      border-radius: 12px;
      font-size: 12px;
      margin-left: 6px;
    }
  </style>
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <img src="logo.jpeg" alt="Out West Logo" class="logo" />
    <nav>
      <button class="nav-button">Dashboard</button>

      <form action="http://localhost/Out-west/internship_field.php" method="get" style="margin: 0;">
        <button type="submit" class="nav-button">Internship field</button>
      </form>

      <button class="nav-button active">
        Applications 
        <?php if ($pendingCount > 0): ?>
          <span class="badge"><?= $pendingCount ?></span>
        <?php endif; ?>
      </button>

      <form action="http://localhost/out-west/interns_dashboard.php" method="get" style="margin: 0;">
        <button type="submit" class="nav-button">Interns</button>
      </form>

      <form action="http://localhost/out-west/supervisor_dashboard.php" method="get" style="margin: 0;">
        <button type="submit" class="nav-button">Supervisors</button>
      </form>

      <button onclick="location.href='admin_messages.php'" class="nav-button">Messages</button>
      
      <!-- ✅ Log out button added -->
      <button onclick="location.href='logout.php'" class="nav-button">Log out</button>
    </nav>
  </aside>

  <main class="main-content">
    <header class="dashboard-header">
      <h1>Out-west Internship Admin Dashboard</h1>

      <div class="dropdown">
        <form method="GET" id="statusForm">
          <select name="status" onchange="document.getElementById('statusForm').submit()" class="dropdown-btn">
            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="declined" <?= $status_filter === 'declined' ? 'selected' : '' ?>>Declined</option>
          </select>
        </form>
      </div>
    </header>

    <section class="applications-grid">
      <?php foreach ($applications as $app): ?>
        <div class="application-card">
          <p><strong>Name:</strong> <?= htmlspecialchars($app['fullname']) ?></p>
          <p><strong>Date of Birth:</strong> <?= $app['dob'] ?></p>
          <p><strong>Phone:</strong> <?= $app['phone'] ?></p>
          <p><strong>Email:</strong> <?= $app['email'] ?></p>
          <p><strong>Gender:</strong> <?= $app['gender'] ?></p>
          <p><strong>Nationality:</strong> <?= $app['nationality'] ?></p>
          <p><strong>Institution:</strong> <?= $app['institution'] ?></p>
          <p><strong>Course:</strong> <?= $app['course'] ?></p>
          <p><strong>Level:</strong> <?= $app['level'] ?></p>
          <p><strong>Year:</strong> <?= $app['year'] ?></p>
          <p><strong>Graduation:</strong> <?= $app['graduation'] ?></p>
          <p><strong>Department:</strong> <?= $app['department'] ?></p>
          <p><strong>Other Department:</strong> <?= $app['other_department'] ?></p>
          <p><strong>Duration:</strong> <?= $app['duration'] ?></p>
          <p><strong>Duration (Other):</strong> <?= $app['duration_other'] ?></p>
          <p><strong>Start Date:</strong> <?= $app['startdate'] ?></p>
          <p><strong>Accommodation:</strong> <?= $app['accommodation'] ?></p>
          <p><strong>Paid:</strong> <?= $app['paid'] ?></p>
          <p><strong>Amount:</strong> <?= $app['amount'] ?></p>
          <p><strong>Skills:</strong> <?= $app['skills'] ?></p>
          <p><strong>Company:</strong> <?= $app['company'] ?></p>
          <p><strong>Role:</strong> <?= $app['role'] ?></p>
          <p><strong>Experience Duration:</strong> <?= $app['exp_duration'] ?></p>
          <p><strong>Responsibilities:</strong> <?= $app['responsibilities'] ?></p>
          <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($app['status'])) ?></p>
          <div class="action-buttons">
            <form action="update_status.php" method="post">
              <input type="hidden" name="id" value="<?= $app['id'] ?>">
              <button type="submit" name="action" value="approved" class="approve">Approve</button>
              <button type="submit" name="action" value="declined" class="decline">Decline</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  </main>
</div>
</body>
</html>
