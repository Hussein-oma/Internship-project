<?php
require_once 'db.php';

$status     = $_POST['status'] ?? 'closed';
$open_date  = $_POST['open_date'] ?? null;
$close_date = $_POST['close_date'] ?? null;

// Save to database
$stmt = $conn->prepare("REPLACE INTO application_dates (id, status, open_date, close_date) VALUES (1, ?, ?, ?)");
$stmt->bind_param("sss", $status, $open_date, $close_date);
$stmt->execute();

header("Location: internship_field.php");
exit();
?>
