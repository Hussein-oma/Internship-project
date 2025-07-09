<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['field_name'])) {
    $id = intval($_POST['id']);
    $old_name = htmlspecialchars($_POST['field_name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Field</title>
</head>
<body>
  <form method="post" action="edit_field.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <label>Edit Field Name:</label>
    <input type="text" name="new_name" value="<?= $old_name ?>" required>
    <button type="submit" name="update">Update</button>
  </form>
</body>
</html>

<?php
// Handle update after form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $new_name = trim($_POST['new_name']);
    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE internship_fields SET field_name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: internship_field.php");
    exit();
}
?>
