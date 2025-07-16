<?php
require_once 'config.php';

// Fetch all supervisors
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'supervisor'");
$stmt->execute();
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all interns with supervisor assignments
$intern_stmt = $pdo->prepare("SELECT id, name, supervisor_id FROM users WHERE role = 'intern' AND supervisor_id IS NOT NULL");
$intern_stmt->execute();
$interns = $intern_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group interns by supervisor_id
$interns_by_supervisor = [];
foreach ($interns as $intern) {
    $sid = $intern['supervisor_id'];
    if (!isset($interns_by_supervisor[$sid])) {
        $interns_by_supervisor[$sid] = [];
    }
    $interns_by_supervisor[$sid][] = $intern;
}

// Count supervisors without interns
$supervisors_without_interns = 0;
foreach ($supervisors as $sup) {
    if (empty($interns_by_supervisor[$sup['id']])) {
        $supervisors_without_interns++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Supervisor Dashboard</title>
  <link rel="stylesheet" href="supervisor.css">
  <style>
    .badge {
      background-color: red;
      color: white;
      border-radius: 50%;
      padding: 2px 7px;
      font-size: 12px;
      position: absolute;
      right: 10px;
      top: 8px;
      z-index: 999;
    }
    .sidebar button {
      position: relative;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <img src="logo.jpeg" alt="Out West Logo" class="logo" />
    <button onclick="location.href=''">Dashboard</button>
    <button onclick="location.href='internship_field.php'">Internship field</button>
    <button onclick="location.href='admin_dashboard.php'">Applications</button>
    <button onclick="location.href='interns_dashboard.php'">Interns</button>
    <button class="active" onclick="location.href='supervisor_dashboard.php'">
      Supervisors
      <?php if ($supervisors_without_interns > 0): ?>
        <span class="badge"><?= $supervisors_without_interns ?></span>
      <?php endif; ?>
    </button>
    <button onclick="location.href='admin_messages.php'">Messages</button>
    <button onclick="location.href='admin_reports.php'">Reports</button>
    <button class="logout-btn" onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main-content">
    <h2>Out-west Internship Admin Dashboard</h2>

    <!-- Add Supervisor Form -->
    <form class="add-form" action="add_supervisor.php" method="POST">
      <input type="text" name="name" placeholder="Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">ADD</button>
    </form>

    <!-- Supervisor Table -->
    <table class="supervisor-table">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Date Created</th>
        <th>Password</th>
        <th>Check interns assigned to</th>
        <th>Action</th>
      </tr>
      <?php foreach ($supervisors as $index => $row): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>********</td>
        <td>
          <?php if (!empty($interns_by_supervisor[$row['id']])): ?>
            <?php foreach ($interns_by_supervisor[$row['id']] as $intern): ?>
              <div>
                <?= htmlspecialchars($intern['name']) ?>
                <form method="POST" action="unassign_intern.php" style="display:inline;" onsubmit="return confirm('Unassign this intern from the supervisor?');">
                  <input type="hidden" name="intern_id" value="<?= $intern['id'] ?>">
                  <button type="submit">Delete</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <em>No interns assigned</em>
          <?php endif; ?>
        </td>
        <td>
          <!-- Reset Password -->
          <form method="POST" action="reset_supervisor_password.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit">Reset Password</button>
          </form>

          <!-- Edit -->
          <a href="edit_supervisor.php?id=<?= $row['id'] ?>"><button>Edit</button></a>

          <!-- Delete with Confirmation -->
          <form method="POST" action="delete_supervisor.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this supervisor? This action cannot be undone.');">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>
