<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intern_id = $_SESSION['user_id'];
    $week_ending = $_POST['week_ending'];
    $monday = $_POST['monday'];
    $tuesday = $_POST['tuesday'];
    $wednesday = $_POST['wednesday'];
    $thursday = $_POST['thursday'];
    $friday = $_POST['friday'];
    $weekly_summary = $_POST['weekly_summary'];
    $signature = $_POST['signature'];
    $report_date = $_POST['report_date'];

    // New supervisor fields
    $supervisor_comments = $_POST['supervisor_comments'];
    $supervisor_signature = $_POST['supervisor_signature'];
    $supervisor_date = $_POST['supervisor_date'];

    // Insert including supervisor fields
    $stmt = $pdo->prepare("INSERT INTO weekly_reports 
        (intern_id, week_ending, monday, tuesday, wednesday, thursday, friday, 
         weekly_summary, signature, report_date, supervisor_comments, supervisor_signature, supervisor_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $intern_id,
        $week_ending,
        $monday,
        $tuesday,
        $wednesday,
        $thursday,
        $friday,
        $weekly_summary,
        $signature,
        $report_date,
        $supervisor_comments,
        $supervisor_signature,
        $supervisor_date
    ]);

    header("Location: weekly_report.php?success=1");
    exit();
}
?>
