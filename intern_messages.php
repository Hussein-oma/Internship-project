<?php
require_once 'config.php';
session_start();

$intern_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$intern_id || $role !== 'intern') {
    header("Location: login.php");
    exit();
}

// Get assigned supervisor
$supervisor_stmt = $pdo->prepare("SELECT u.id, u.name FROM users u JOIN users i ON u.id = i.supervisor_id WHERE i.id = ?");
$supervisor_stmt->execute([$intern_id]);
$supervisor = $supervisor_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch fellow interns
$interns_stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'intern' AND id != ?");
$interns_stmt->execute([$intern_id]);
$fellow_interns = $interns_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle message or notification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_message'], $_POST['recipient_id'], $_POST['message_type'])) {
        $content = trim($_POST['new_message']);
        $recipient_ids = $_POST['recipient_id']; // array
        $type = $_POST['message_type'] === 'notification' ? 'notification' : 'message';
        $group_id = uniqid('msg_', true);

        if (!empty($content)) {
            foreach ($recipient_ids as $recipient_id) {
                if ($recipient_id === 'admin') {
                    $admin_stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                    $admin_id = $admin_stmt->fetchColumn();
                    if ($admin_id) {
                        $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                               VALUES (?, NOW(), ?, 'intern', ?, 'admin', ?)");
                        $stmt->execute([$content, $intern_id, $type, $admin_id, $group_id]);
                    }
                } elseif ($recipient_id === 'supervisor') {
                    if ($supervisor) {
                        $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                               VALUES (?, NOW(), ?, 'intern', ?, 'supervisor', ?)");
                        $stmt->execute([$content, $intern_id, $type, $supervisor['id'], $group_id]);
                    }
                } elseif (strpos($recipient_id, 'intern_') === 0) {
                    $receiver_id = str_replace('intern_', '', $recipient_id);
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                           VALUES (?, NOW(), ?, 'intern', ?, 'intern', ?)");
                    $stmt->execute([$content, $intern_id, $receiver_id, $type, $group_id]);
                }
            }
        }

        header("Location: intern_messages.php");
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
                                   VALUES (?, NOW(), ?, 'intern', ?, ?, 'reply', ?, ?)");
            $stmt->execute([$reply_text, $intern_id, $reply_to_id, $reply_to_role, $group_id, $reply_to_message_id]);
        }

        header("Location: intern_messages.php");
        exit();
    }
}

// Fetch all messages
$stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name FROM messages m
                       LEFT JOIN users u ON m.user_id = u.id
                       WHERE (m.recipient_id = :id AND m.recipient_role = 'intern')
                          OR m.recipient_group IN ('all_users', 'all_interns')
                          OR m.user_id = :id
                       ORDER BY m.created_at DESC");
$stmt->execute(['id' => $intern_id]);
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
  <title>Intern Messages</title>
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
    .main-content { flex-grow: 1; padding: 20px; overflow-y: auto; }
    h2 {
      text-align: center; background-color: #e0e0e0; padding: 10px;
      font-size: 20px; border: 1px solid #aaa; width: fit-content;
      margin: 0 auto 20px auto;
    }
    .send-form { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
    .send-form textarea { width: 300px; height: 60px; padding: 6px; }
    .send-form select, .send-form button { padding: 6px; }
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
  <button onclick="location.href='intern_dashboard.php'">Dashboard</button>
  <button onclick="location.href='weekly_report.php'">Weekly Report</button>
  <button onclick="location.href='intern_dashboard.php'">Tasks</button>
  <button class="active" onclick="location.href='intern_messages.php'">Messages</button>
  <button onclick="location.href='logout.php'">Logout</button>
</div>

<div class="main-content">
  <h2>Messages & Notifications</h2>

  <form method="POST" class="send-form">
    <textarea name="new_message" placeholder="Write message..." required></textarea>
    <select name="recipient_id[]" multiple size="5" required>
      <?php if ($supervisor): ?>
        <option value="supervisor">Supervisor - <?= htmlspecialchars($supervisor['name']) ?></option>
      <?php endif; ?>
      <option value="admin">Admin</option>
      <?php foreach ($fellow_interns as $int): ?>
        <option value="intern_<?= $int['id'] ?>"><?= htmlspecialchars($int['name']) ?></option>
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
        if ($reply['user_id'] == $intern_id) {
            $already_replied = true;
            break;
        }
    }
  ?>
    <div class="card <?= $class ?>">
      <p><?= htmlspecialchars($msg['content']) ?></p>
      <small>
        <?= $msg['type'] === 'notification' ? '🔔 Notification' : '💬 Message' ?>
        | From: <?= ucfirst($msg['user_role']) ?> - <?= htmlspecialchars($msg['sender_name']) ?>
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
        $msg['type'] === 'message' &&
        $msg['user_id'] != $intern_id &&
        (
          ($msg['recipient_id'] == $intern_id && $msg['recipient_role'] === 'intern') ||
          in_array($msg['recipient_group'], ['all_users', 'all_interns'])
        )
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
