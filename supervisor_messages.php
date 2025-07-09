<?php
require_once 'config.php';
session_start();

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

// Fetch assigned interns
$intern_stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'intern' AND supervisor_id = ?");
$intern_stmt->execute([$supervisor_id]);
$assigned_interns = $intern_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle message or notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_message'], $_POST['recipient_id'], $_POST['message_type'])) {
        $content = trim($_POST['new_message']);
        $recipient_id = $_POST['recipient_id'];
        $type = $_POST['message_type'] === 'notification' ? 'notification' : 'message';
        $group_id = uniqid('msg_', true);

        if (!empty($content)) {
            if ($recipient_id === 'all_users') {
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_group, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, 'all_users', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $group_id]);
            } elseif ($recipient_id === 'all_interns') {
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_group, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, 'all_interns', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $group_id]);
            } elseif ($recipient_id === 'admin') {
                $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $admin_stmt->execute();
                $admin_id = $admin_stmt->fetchColumn();
                if ($admin_id) {
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                           VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'admin', ?)");
                    $stmt->execute([$content, $supervisor_id, $type, $admin_id, $group_id]);
                }
            } elseif (strpos($recipient_id, 'intern_') === 0) {
                $intern_id = str_replace('intern_', '', $recipient_id);
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'intern', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $intern_id, $group_id]);
            }
        }
        header("Location: supervisor_messages.php");
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
                                   VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'reply', ?, ?)");
            $stmt->execute([$reply_text, $supervisor_id, $reply_to_id, $reply_to_role, $group_id, $reply_to_message_id]);
        }
        header("Location: supervisor_messages.php");
        exit();
    }
}

// Fetch all messages
$stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name
                       FROM messages m
                       LEFT JOIN users u ON m.user_id = u.id
                       WHERE (m.recipient_id = :id AND m.recipient_role = 'supervisor')
                             OR m.recipient_group IN ('all_users', 'all_interns')
                             OR m.user_id = :id
                       ORDER BY m.created_at DESC");
$stmt->execute(['id' => $supervisor_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group messages
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
<title>Supervisor Messages</title>
<style>
  * { box-sizing: border-box; font-family: Arial, sans-serif; }
  body { margin: 0; display: flex; height: 100vh; }
  .sidebar {
    width: 140px; background-color: #f0f0f0; border-right: 1px solid #333;
    padding-top: 30px; display: flex; flex-direction: column; align-items: center;
  }
  .sidebar button {
    width: 100px; margin: 10px 0; padding: 5px;
    border: 1px solid #444; background-color: #ddd; cursor: pointer;
  }
  .sidebar button.active { background-color: #999; color: white; }
  .sidebar img.logo { display: block; max-height: 65px; margin-bottom: 10px; }
  .logout-btn { margin-top: auto; margin-bottom: 20px; }
  .main-content { flex-grow: 1; padding: 20px; overflow-y: auto; }
  h2 {
    text-align: center; background-color: #e0e0e0; padding: 10px;
    font-size: 20px; border: 1px solid #aaa; width: fit-content;
    margin: 0 auto 20px auto;
  }
  .add-form { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
  .add-form textarea { width: 300px; height: 60px; padding: 6px; }
  .add-form select, .add-form button { padding: 6px; }
  .card {
    border: 1px solid #ccc; border-radius: 6px; padding: 15px; margin-bottom: 15px;
  }
  .message-card { background: #e8f0ff; }
  .notification-card { background: #fffbe5; }
  .reply-card {
    background: #f0f0ff; margin-left: 30px; border-left: 4px solid #007bff;
    margin-top: 10px;
  }
  .card small { display: block; color: #666; font-size: 12px; margin-top: 5px; }
  .reply-form textarea { width: 100%; height: 50px; margin-top: 8px; resize: none; }
  .reply-form button { margin-top: 5px; padding: 5px 10px; background-color: #444; color: white; border: none; }
</style>
</head>
<body>
<div class="sidebar">
  <img src="logo.jpeg" alt="Logo" class="logo" />
  <button onclick="location.href='supervisor_dashboard.php'">Dashboard</button>
  <button onclick="location.href='view_intern_report.php'">Reports</button>
  <button onclick="location.href='assign_task.php'">Assign Tasks</button>
  <button class="active" onclick="location.href='supervisor_messages.php'">Messages</button>
  <button class="logout-btn" onclick="location.href='logout.php'">Log out</button>
</div>

<div class="main-content">
  <h2>Messages & Notifications</h2>

  <form method="POST" class="add-form">
    <textarea name="new_message" placeholder="Write message..." required></textarea>
    <select name="recipient_id" required>
      <option value="" disabled selected>Choose Recipient</option>
      <option value="admin">Admin</option>
      <option value="all_users">All Users</option>
      <option value="all_interns">All Interns</option>
      <?php foreach ($assigned_interns as $intern): ?>
        <option value="intern_<?= $intern['id'] ?>"><?= htmlspecialchars($intern['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="message_type" required>
      <option value="message">Message</option>
      <option value="notification">Notification</option>
    </select>
    <button type="submit">Send</button>
  </form>

  <?php if (empty($parent_messages)): ?>
    <p style="text-align:center;">No messages or notifications found.</p>
  <?php endif; ?>

  <?php foreach ($parent_messages as $msg):
    $class = $msg['type'] === 'notification' ? 'notification-card' : 'message-card';
    $replies = $replies_map[$msg['id']] ?? [];

    $already_replied = false;
    foreach ($replies as $reply) {
        if ($reply['user_id'] == $supervisor_id) {
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

      <?php if (
        !$already_replied &&
        $msg['recipient_id'] == $supervisor_id &&
        $msg['type'] === 'message' &&
        $msg['user_id'] != $supervisor_id
      ): ?>
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
