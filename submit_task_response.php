<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_FILES['submission_file'])) {
    $task_id = $_POST['task_id'];
    $upload_dir = 'submissions/';
    $filename = basename($_FILES['submission_file']['name']);
    $target_path = $upload_dir . time() . '_' . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_path)) {
        $stmt = $pdo->prepare("UPDATE tasks SET submission_file = ?, submission_date = NOW(), status = 'completed' WHERE id = ?");
        $stmt->execute([$target_path, $task_id]);
        header("Location: intern_dashboard.php?success=1");
        exit();
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "Invalid request.";
}
?>
