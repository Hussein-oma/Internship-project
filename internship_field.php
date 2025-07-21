<?php
include 'db.php';
session_start();

$admin_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$admin_id || $role !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin profile
$admin_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="internship_field1.css">
  <style>
    .application-dates-box {
      margin-bottom: 25px;
      background: #f8f8f8;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .application-dates-box h3 {
      margin-bottom: 10px;
    }
    .application-dates-box label {
      display: block;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .application-dates-box input[type="date"],
    .application-dates-box select {
      padding: 5px;
      margin-left: 10px;
    }
    .application-dates-box button {
      margin-top: 10px;
      padding: 6px 12px;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <img src="logo.jpeg" alt="Out West Logo" class="logo" />
    <button>Dashboard</button>
    <button class="active">Internship field</button>
    <form action="http://localhost/out-west/admin_dashboard.php" method="get" style="margin: 0;">
     <button type="submit">Applications</button>
    </form>
    <button onclick="location.href='interns_dashboard.php'">Interns</button>
    <form action="http://localhost/out-west/supervisor_dashboard.php" method="get" style="margin: 0;">
        <button type="submit" class="nav-button">Supervisors</button>
    </form>
    <button onclick="location.href='admin_messages.php'">Messages</button>
    <button onclick="location.href='admin_reports.php'">Reports</button>
    <button onclick="location.href='logout.php'">Log out</button>
  </div>

  <div class="main-content">
    <div class="header">Internship Admin dashboard</div>

    <!-- ✅ Admin Profile Section -->
    <div class="application-dates-box">
      <h3>Admin Profile</h3>
      <p><strong>Name:</strong> <?= htmlspecialchars($admin['name']) ?></p>
      <p><strong>Role:</strong> <?= htmlspecialchars($admin['role']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
    </div>

    <!-- ✅ Application Status Control -->
    <div class="application-dates-box">
      <h3>Applications Status</h3>
      <form action="update_application_dates.php" method="POST" id="appDatesForm">
        <label>Status:
          <select name="status" id="appStatus" onchange="toggleDateInputs()" required>
            <option value="closed">Closed</option>
            <option value="open">Open</option>
          </select>
        </label>

        <div id="dateRange" style="margin-top: 10px; display: none;">
          <label>Open Date: <input type="date" name="open_date" /></label>
          <label>Close Date: <input type="date" name="close_date" /></label>
        </div>

        <button type="submit">Update</button>
      </form>
    </div>

    <!-- ✅ Internship Fields Table -->
    <div class="sub-header-row">
      <div class="sub-header">Open fields for internship</div>
      <button class="add-btn" onclick="addField()">Add</button>
    </div>

    <table class="field-table">
      <?php
      $sql = "SELECT * FROM internship_fields";
      $result = $conn->query($sql);

      while ($row = $result->fetch_assoc()):
      ?>
        <tr>
          <td><?= htmlspecialchars($row['field_name']) ?></td>
          <td class="actions">
            <form method="post" action="edit_field.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="hidden" name="field_name" value="<?= htmlspecialchars($row['field_name']) ?>">
              <button type="submit">Edit</button>
            </form>
            <form method="post" action="delete_field.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>

    
  </div>

  <script>
    function toggleDateInputs() {
      const status = document.getElementById("appStatus").value;
      const dateRange = document.getElementById("dateRange");
      dateRange.style.display = (status === "open") ? "block" : "none";
    }

    function addField() {
      const name = prompt("Enter new internship field name:");
      if (name) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "add_field.php";

        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "field_name";
        input.value = name;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html>
