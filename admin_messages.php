<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch supervisors and interns
$supervisors = $pdo->query("SELECT id, name FROM users WHERE role = 'supervisor'")->fetchAll(PDO::FETCH_ASSOC);
$interns = $pdo->query("SELECT id, name FROM users WHERE role = 'intern'")->fetchAll(PDO::FETCH_ASSOC);

// Handle new message or notification to multiple recipients
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_text'], $_POST['recipients'], $_POST['type']) && is_array($_POST['recipients'])) {
        $message = trim($_POST['message_text']);
        $recipients = $_POST['recipients'];
        $type = $_POST['type'];

        if (!empty($message) && !empty($recipients) && in_array($type, ['message', 'notification'])) {
            $group_id = uniqid('msg_', true);

            foreach ($recipients as $recipient) {
                if (in_array($recipient, ['all_interns', 'all_supervisors', 'all_users'])) {
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_group, type, group_id)
                                           VALUES (?, NOW(), ?, 'admin', ?, ?, ?)");
                    $stmt->execute([$message, $admin_id, $recipient, $type, $group_id]);
                } else {
                    list($id, $role) = explode('_', $recipient);
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_id, recipient_role, type, group_id)
                                           VALUES (?, NOW(), ?, 'admin', ?, ?, ?, ?)");
                    $stmt->execute([$message, $admin_id, $id, $role, $type, $group_id]);
                }
            }
        }
        header("Location: admin_messages.php");
        exit();
    }

    if (isset($_POST['reply_text'], $_POST['reply_to_id'], $_POST['reply_to_role'], $_POST['group_id'], $_POST['reply_to_message_id'])) {
        $reply_text = trim($_POST['reply_text']);
        $reply_to_id = $_POST['reply_to_id'];
        $reply_to_role = $_POST['reply_to_role'];
        $group_id = $_POST['group_id'];
        $reply_to_message_id = $_POST['reply_to_message_id'];

        if (!empty($reply_text)) {
            $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_id, recipient_role, type, group_id, reply_to_message_id)
                                   VALUES (?, NOW(), ?, 'admin', ?, ?, 'reply', ?, ?)");
            $stmt->execute([$reply_text, $admin_id, $reply_to_id, $reply_to_role, $group_id, $reply_to_message_id]);
        }
        header("Location: admin_messages.php");
        exit();
    }
}

// Only show messages where admin is sender, recipient, or sending to a group
$stmt = $pdo->prepare("
    SELECT m.*, sender.name AS sender_name
    FROM messages m
    LEFT JOIN users sender ON m.user_id = sender.id
    WHERE 
        m.user_id = :admin_id
        OR m.recipient_id = :admin_id
        OR (m.recipient_group IN ('all_interns', 'all_supervisors', 'all_users') AND m.user_id = :admin_id)
    ORDER BY m.created_at DESC
");
$stmt->execute(['admin_id' => $admin_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group messages by parent and replies
$parent_messages = [];
$replies_map = [];

foreach ($messages as $msg) {
    if ($msg['reply_to_message_id']) {
        $replies_map[$msg['reply_to_message_id']][] = $msg;
    } else {
        $parent_messages[] = $msg;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Messages</title>
  <style>
    body { margin: 0; display: flex; font-family: Arial, sans-serif; }
    .sidebar {
      width: 140px; background-color: #f0f0f0; border-right: 1px solid #333;
      display: flex; flex-direction: column; align-items: center; padding-top: 30px;
    }
    .sidebar button {
      width: 100px; margin: 10px 0; padding: 6px;
      border: 1px solid #444; background-color: #ddd; cursor: pointer;
    }
    .sidebar button.active { background-color: #999; color: white; }
    .sidebar img.logo { max-height: 65px; margin-bottom: 10px; }
    .main { flex-grow: 1; padding: 20px; }
    .card { border: 1px solid #ccc; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
    .message-card { background: #e8f0ff; }
    .notification-card { background: #fffbe5; }
    .reply-card {
      background: #f0f0ff; margin-left: 30px; border-left: 4px solid #007bff;
    }
    .reply-form textarea { width: 100%; height: 50px; margin-top: 8px; resize: none; }
    .reply-form button { margin-top: 5px; padding: 5px 10px; background-color: #444; color: white; border: none; }
    .send-form { display: flex; gap: 10px; margin-bottom: 30px; }
    .send-form textarea { width: 300px; height: 60px; padding: 6px; }
    .send-form select, .send-form button { padding: 6px; }
  </style>
</head>
<body>
<div class="sidebar">
  <img src="logo.jpeg" alt="Logo" class="logo">
  <button onclick="location.href='admin_dashboard.php'">Dashboard</button>
  <button onclick="location.href='interns_dashboard.php'">Internship field</button>
  <button onclick="location.href='admin_dashboard.php'">Applications</button>
  <button onclick="location.href='interns_dashboard.php'">Interns</button>
  <button onclick="location.href='supervisor_dashboard.php'">Supervisors</button>
  <button class="active">Messages</button>
  <button onclick="location.href='admin_reports.php'">Report</button>
  <button onclick="location.href='logout.php'">Log out</button>
</div>

<div class="main">
  <h2>Send Message</h2>
  <form class="send-form" method="POST">
    <textarea name="message_text" placeholder="Type a message..." required></textarea>
    <select name="recipients[]" multiple required size="6">
      <option value="all_interns">All Interns</option>
      <option value="all_supervisors">All Supervisors</option>
      <option value="all_users">All Users</option>
      <optgroup label="Interns">
        <?php foreach ($interns as $intern): ?>
          <option value="<?= $intern['id'] ?>_intern"><?= htmlspecialchars($intern['name']) ?></option>
        <?php endforeach; ?>
      </optgroup>
      <optgroup label="Supervisors">
        <?php foreach ($supervisors as $sup): ?>
          <option value="<?= $sup['id'] ?>_supervisor"><?= htmlspecialchars($sup['name']) ?></option>
        <?php endforeach; ?>
      </optgroup>
    </select>
    <select name="type" required>
      <option value="message">Message</option>
      <option value="notification">Notification</option>
    </select>
    <button type="submit">Send to Selected Users</button>
  </form>

  <?php foreach ($parent_messages as $msg):
    $class = $msg['type'] === 'notification' ? 'notification-card' : 'message-card';
    $replies = $replies_map[$msg['id']] ?? [];
    $already_replied = false;
    foreach ($replies as $r) {
        if ($r['user_id'] == $admin_id) {
            $already_replied = true;
            break;
        }
    }
  ?>
    <div class="card <?= $class ?>">
      <p><?= htmlspecialchars($msg['content']) ?></p>
      <small>
        <?= $msg['type'] === 'notification' ? '🔔 Notification' : '💬 Message' ?>
        | From: <?= ucfirst($msg['user_role']) ?> - <?= htmlspecialchars($msg['sender_name'] ?? 'Unknown') ?>
        | <?= $msg['created_at'] ?> | ID: <?= $msg['id'] ?>
      </small>

      <?php foreach ($replies as $reply): ?>
        <div class="card reply-card">
          <p><?= htmlspecialchars($reply['content']) ?></p>
          <small>↪ From: <?= ucfirst($reply['user_role']) ?> - <?= htmlspecialchars($reply['sender_name']) ?> | <?= $reply['created_at'] ?></small>
        </div>
      <?php endforeach; ?>

      <?php if (!$already_replied && $msg['recipient_id'] == $admin_id && $msg['type'] === 'message' && $msg['user_id'] != $admin_id): ?>
        <form class="reply-form" method="POST">
          <textarea name="reply_text" placeholder="Write your reply..." required></textarea>
          <input type="hidden" name="reply_to_id" value="<?= $msg['user_id'] ?>">
          <input type="hidden" name="reply_to_role" value="<?= $msg['user_role'] ?>">
          <input type="hidden" name="group_id" value="<?= $msg['group_id'] ?>">
          <input type="hidden" name="reply_to_message_id" value="<?= $msg['id'] ?>">
          <button type="submit">Reply</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
