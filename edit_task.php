<?php
// edit_task.php
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

// Fetch task details
$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    echo "Invalid task ID.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    echo "Task not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['task_description'];
    $date_issued = $_POST['date_issued'];
    $submit_date = $_POST['submit_date'];

    $stmt = $pdo->prepare("UPDATE tasks SET task_description = ?, date_issued = ?, submit_date = ? WHERE id = ?");
    $stmt->execute([$description, $date_issued, $submit_date, $task_id]);

    header("Location: assign_task.php?edited=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Task</title>
</head>
<body>
  <h2>Edit Task</h2>
  <form method="POST">
    <label>Task Description:</label><br>
    <textarea name="task_description" required><?= htmlspecialchars($task['task_description']) ?></textarea><br>

    <label>Date Issued:</label><br>
    <input type="date" name="date_issued" value="<?= htmlspecialchars($task['date_issued']) ?>" required><br>

    <label>Submit Date:</label><br>
    <input type="date" name="submit_date" value="<?= htmlspecialchars($task['submit_date']) ?>" required><br><br>

    <button type="submit">Update Task</button>
  </form>
</body>
</html>
