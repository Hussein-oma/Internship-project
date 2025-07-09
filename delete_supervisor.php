<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $supervisor_id = intval($_POST['id']);

    try {
        // Optional: Check if the supervisor has assigned interns
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE supervisor_id = ?");
        $check_stmt->execute([$supervisor_id]);
        $intern_count = $check_stmt->fetchColumn();

        if ($intern_count > 0) {
            // Prevent deletion if supervisor still has interns
            echo "<script>alert('Cannot delete: Supervisor still has assigned interns.'); window.location.href = 'supervisor_dashboard.php';</script>";
            exit();
        }

        // Delete the supervisor
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'supervisor'");
        $stmt->execute([$supervisor_id]);

        header("Location: supervisor_dashboard.php");
        exit();

    } catch (PDOException $e) {
        echo "Error deleting supervisor: " . $e->getMessage();
    }

} else {
    // Redirect if accessed directly
    header("Location: supervisor_dashboard.php");
    exit();
}
