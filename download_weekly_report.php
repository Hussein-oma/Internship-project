<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $intern_id = $_SESSION['user_id'] ?? null;

    if (!$intern_id) {
        die("Unauthorized");
    }

    $stmt = $pdo->prepare("SELECT wr.*, u.name AS intern_name FROM weekly_reports wr JOIN users u ON wr.intern_id = u.id WHERE wr.id = ? AND wr.intern_id = ?");
    $stmt->execute([$report_id, $intern_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        die("Report not found");
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=weekly_report_{$report['week_ending']}.html");

    echo "<h2>Weekly Report - " . htmlspecialchars($report['intern_name']) . "</h2>";
    echo "<p><strong>Week Ending:</strong> " . htmlspecialchars($report['week_ending']) . "</p>";
    echo "<ul>";
    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
        $lower = strtolower($day);
        echo "<li><strong>$day:</strong> " . nl2br(htmlspecialchars($report[$lower])) . "</li>";
    }
    echo "</ul>";
    echo "<p><strong>Weekly Summary:</strong><br>" . nl2br(htmlspecialchars($report['weekly_summary'])) . "</p>";
    echo "<p><strong>Signature:</strong> " . htmlspecialchars($report['signature']) . "</p>";
    echo "<p><strong>Date:</strong> " . htmlspecialchars($report['report_date']) . "</p>";

    if (!empty($report['supervisor_comments'])) {
        echo "<hr>";
        echo "<p><strong>Supervisor Comments:</strong><br>" . nl2br(htmlspecialchars($report['supervisor_comments'])) . "</p>";
        echo "<p><strong>Supervisor Signature:</strong> " . htmlspecialchars($report['supervisor_signature']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($report['supervisor_date']) . "</p>";
    }
    exit;
}
?>
