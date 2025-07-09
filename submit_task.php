<?php
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

$intern_id = $_POST['intern_id'];
$task = $_POST['task_description'];
$date_issued = $_POST['date_issued'];
$submit_date = $_POST['submit_date'];

// Handle file upload
$uploadedFile = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
    $fileTmp = $_FILES['attachment']['tmp_name'];
    $fileName = basename($_FILES['attachment']['name']);
    $uploadPath = 'uploads/' . $fileName;
    move_uploaded_file($fileTmp, $uploadPath);
    $uploadedFile = $uploadPath;
}

// Assign task(s)
if ($intern_id === 'all') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'intern' AND supervisor_id = ?");
    $stmt->execute([$supervisor_id]);
    $internList = $stmt->fetchAll();

    foreach ($internList as $intern) {
        $insert = $pdo->prepare("
            INSERT INTO tasks (intern_id, supervisor_id, task_description, file_path, date_issued, submit_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $intern['id'], $supervisor_id, $task, $uploadedFile, $date_issued, $submit_date
        ]);
    }
} else {
    $insert = $pdo->prepare("
        INSERT INTO tasks (intern_id, supervisor_id, task_description, file_path, date_issued, submit_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([
        $intern_id, $supervisor_id, $task, $uploadedFile, $date_issued, $submit_date
    ]);
}

header("Location: assign_task.php?success=1");
exit();
