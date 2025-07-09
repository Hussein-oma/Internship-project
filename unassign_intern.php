<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['intern_id'])) {
    $intern_id = intval($_POST['intern_id']);
    $stmt = $pdo->prepare("UPDATE users SET supervisor_id = NULL WHERE id = ?");
    $stmt->execute([$intern_id]);
}

header("Location: supervisor_dashboard.php");
exit();
