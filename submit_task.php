<?php
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

// Get form data
$intern_id = $_POST['intern_id'] ?? null;
$task_description = trim($_POST['task_description'] ?? '');
$date_issued = $_POST['date_issued'] ?? '';
$submit_date = $_POST['submit_date'] ?? '';

// Set up upload directory
$uploadDir = 'uploads/tasks/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// Handle file upload
$file_path = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $tmpFile = $_FILES['attachment']['tmp_name'];
    $originalName = basename($_FILES['attachment']['name']);
    
    // Sanitize and make filename unique
    $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $uniqueName = time() . '_' . random_int(1000, 9999) . '_' . $safeName . '.' . $ext;
    $targetPath = $uploadDir . $uniqueName;

    // Move uploaded file
    if (move_uploaded_file($tmpFile, $targetPath)) {
        $file_path = $targetPath; // this relative path will be used in <a href="...">
    }
}

// Insert task(s)
try {
    if ($intern_id === 'all') {
        // Assign to all supervised interns
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'intern' AND supervisor_id = ?");
        $stmt->execute([$supervisor_id]);
        $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insert = $pdo->prepare("INSERT INTO tasks (intern_id, supervisor_id, task_description, file_path, date_issued, submit_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");

        foreach ($interns as $intern) {
            $insert->execute([
                $intern['id'],
                $supervisor_id,
                $task_description,
                $file_path,
                $date_issued,
                $submit_date
            ]);
        }

    } else {
        // Assign to a single intern
        $insert = $pdo->prepare("INSERT INTO tasks (intern_id, supervisor_id, task_description, file_path, date_issued, submit_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insert->execute([
            $intern_id,
            $supervisor_id,
            $task_description,
            $file_path,
            $date_issued,
            $submit_date
        ]);
    }

    header("Location: assign_task.php?success=1");
    exit();

} catch (PDOException $e) {
    error_log("Error assigning task: " . $e->getMessage());
    header("Location: assign_task.php?error=1");
    exit();
}
?>
