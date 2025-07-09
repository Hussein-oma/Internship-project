<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($id && in_array($action, ['approved', 'declined'])) {
        try {
            $stmt = $pdo->prepare("UPDATE internship_applications SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $action,
                ':id' => $id
            ]);
            header("Location: admin_dashboard.php");
            exit;
        } catch (PDOException $e) {
            die("Error updating status: " . $e->getMessage());
        }
    }
}
