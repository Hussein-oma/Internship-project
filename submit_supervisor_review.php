<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $comments = $_POST['supervisor_comments'];
    $signature = $_POST['supervisor_signature'];
    $date = $_POST['supervisor_date'];

    $stmt = $pdo->prepare("UPDATE weekly_reports 
        SET supervisor_comments = ?, supervisor_signature = ?, supervisor_date = ? 
        WHERE id = ?");
    $stmt->execute([$comments, $signature, $date, $report_id]);

    header("Location: view_intern_report.php?updated=1");
    exit();
}
?>
