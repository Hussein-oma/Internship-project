<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "out-west";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Count interns without supervisor (for badge)
$no_supervisor_result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'intern' AND (supervisor_id IS NULL OR supervisor_id = '' OR supervisor_id = 0)");
$interns_without_supervisor = $no_supervisor_result->fetch_assoc()['total'];

// Reset password
if (isset($_POST['reset_password'])) {
    $id = intval($_POST['id']);
    $default_password = '1234';
    $hashed = password_hash($default_password, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hashed' WHERE id = $id");
}

// Delete intern
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: interns_dashboard.php");
    exit();
}

// Assign supervisor
if (isset($_POST['assign_supervisor'])) {
    $intern_id = intval($_POST['id']);
    $supervisor_id = intval($_POST['supervisor_id']);
    $conn->query("UPDATE users SET supervisor_id = '$supervisor_id' WHERE id = $intern_id");
}

// Search by email
$search_email = trim($_GET['search'] ?? '');
$where = $search_email ? "AND email LIKE '%$search_email%'" : "";

$interns = $conn->query("SELECT * FROM users WHERE role = 'intern' $where");
$supervisors = $conn->query("SELECT id, name FROM users WHERE role = 'supervisor'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interns Dashboard</title>
    <link rel="stylesheet" href="interns_dashboard1.css">
</head>
<body>
<div class="sidebar">
    <img src="logo.jpeg" alt="Out West Logo" class="logo" />
    <button onclick="location.href=''">Dashboard</button>
    <button onclick="location.href='internship_field.php'">Internship field</button>
    <button onclick="location.href='admin_dashboard.php'">Applications</button>
    <button class="active" onclick="location.href='interns_dashboard.php'">
        Interns
        <?php if ($interns_without_supervisor > 0): ?>
            <span class="badge"><?= $interns_without_supervisor ?></span>
        <?php endif; ?>
    </button>
    <button onclick="location.href='supervisor_dashboard.php'">Supervisors</button>
    <button onclick="location.href='admin_messages.php'">Messages</button>
    <button onclick="location.href='admin_reports.php'">Reports</button>
    <button class="logout" onclick="location.href='logout.php'">Log out</button>
</div>

<div class="content">
    <h2>Internship Admin Dashboard</h2>

    <form method="GET" class="search-form">
        <label>Search by Email</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search_email) ?>">
        <button type="submit">Submit</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date Created</th>
            <th>Password Reset</th>
            <th>Assign Supervisor</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        if ($interns && $interns->num_rows > 0):
            while ($row = $interns->fetch_assoc()):
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>1234</td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <select name="supervisor_id" onchange="this.form.submit()">
                            <option value="">↓</option>
                            <?php
                            mysqli_data_seek($supervisors, 0);
                            while ($sup = $supervisors->fetch_assoc()):
                                $selected = ($row['supervisor_id'] == $sup['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $sup['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="hidden" name="assign_supervisor" value="1">
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="reset_password" class="action-btn">Reset</button>
                    </form>
                    <a href="edit_intern.php?id=<?= $row['id'] ?>" class="action-btn">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="action-btn">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="7">No interns found<?= $search_email ? " for '$search_email'" : "" ?>.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
