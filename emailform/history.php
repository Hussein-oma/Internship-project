<?php
require 'db.php';
$messages = $pdo->query("SELECT * FROM messages ORDER BY sent_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <h2 class="mb-4">Email History</h2>

    <?php foreach ($messages as $msg): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    <?php echo htmlspecialchars($msg['name']); ?> 
                    &lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;
                </h6>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                <small class="text-muted">Sent at: <?php echo $msg['sent_at']; ?></small>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
