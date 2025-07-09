<?php
// delete_task.php
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    echo "Invalid task ID.";
    exit();
}

// Optionally check if the task belongs to the supervisor before deleting
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? LIMIT 1");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    echo "Task not found.";
    exit();
}

// Delete task
$delete_stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
$delete_stmt->execute([$task_id]);

header("Location: assign_task.php?deleted=1");
exit();
?>
