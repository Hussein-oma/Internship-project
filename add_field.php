<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['field_name'])) {
    $name = trim($_POST['field_name']);
    $stmt = $conn->prepare("INSERT INTO internship_fields (field_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
}

header("Location: internship_field.php");
exit();
?>
