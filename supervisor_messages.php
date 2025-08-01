<?php
session_start();
require_once 'config.php';
require_once 'email_notification.php';

$supervisor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

if (!$supervisor_id || $role !== 'supervisor') {
    header("Location: login.php");
    exit();
}

$intern_stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'intern' AND supervisor_id = ?");
$intern_stmt->execute([$supervisor_id]);
$assigned_interns = $intern_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_message'], $_POST['recipient_id'], $_POST['message_type'])) {
        $content = trim($_POST['new_message']);
        $recipient_id = $_POST['recipient_id'];
        $type = $_POST['message_type'] === 'notification' ? 'notification' : 'message';
        $group_id = uniqid('msg_', true);

        if (!empty($content)) {
            // Get supervisor name for email notifications
            $sup_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'supervisor'");
            $sup_stmt->execute([$supervisor_id]);
            $supervisor_name = $sup_stmt->fetchColumn() ?: 'Supervisor';
            
            if ($recipient_id === 'all_users') {
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_group, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, 'all_users', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $group_id]);
                
                // Send email notifications to all users
                sendGroupEmailNotification('all_users', $content, $supervisor_name, $type);
            } elseif ($recipient_id === 'all_interns') {
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_group, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, 'all_interns', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $group_id]);
                
                // Send email notifications to all interns
                sendGroupEmailNotification('all_interns', $content, $supervisor_name, $type);
            } elseif ($recipient_id === 'admin') {
                $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $admin_stmt->execute();
                $admin_id = $admin_stmt->fetchColumn();
                if ($admin_id) {
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                           VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'admin', ?)");
                    $stmt->execute([$content, $supervisor_id, $type, $admin_id, $group_id]);
                    
                    // Send email notification to admin
                    sendEmailNotification($admin_id, 'admin', $content, $supervisor_name, $type);
                }
            } elseif (strpos($recipient_id, 'intern_') === 0) {
                $intern_id = str_replace('intern_', '', $recipient_id);
                $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, type, recipient_id, recipient_role, group_id)
                                       VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'intern', ?)");
                $stmt->execute([$content, $supervisor_id, $type, $intern_id, $group_id]);
                
                // Send email notification to intern
                sendEmailNotification($intern_id, 'intern', $content, $supervisor_name, $type);
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
            // Get supervisor name for email notifications
            $sup_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'supervisor'");
            $sup_stmt->execute([$supervisor_id]);
            $supervisor_name = $sup_stmt->fetchColumn() ?: 'Supervisor';
            
            $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_id, recipient_role, type, group_id, reply_to_message_id)
                                   VALUES (?, NOW(), ?, 'supervisor', ?, ?, 'reply', ?, ?)");
            $stmt->execute([$reply_text, $supervisor_id, $reply_to_id, $reply_to_role, $group_id, $reply_to_message_id]);
            
            // Send email notification for reply
            sendEmailNotification($reply_to_id, $reply_to_role, $reply_text, $supervisor_name, 'reply');
        }
        header("Location: supervisor_messages.php");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name
                       FROM messages m
                       LEFT JOIN users u ON m.user_id = u.id
                       WHERE (m.recipient_id = :id AND m.recipient_role = 'supervisor')
                             OR m.recipient_group IN ('all_users', 'all_interns')
                             OR m.user_id = :id
                       ORDER BY m.created_at DESC");
$stmt->execute(['id' => $supervisor_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
      width: 160px;
      background-color: #95cb48;
      padding-top: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
    }

    .sidebar img.logo {
      display: block;
      max-height: 65px;
      margin-bottom: 20px;
    }

    .sidebar button {
      width: 100%;
      padding: 10px 20px;
      margin-bottom: 10px;
      background-color: transparent;
      color: white;
      border: none;
      text-align: left;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .sidebar button:hover {
      background-color: #7eb537;
    }

    .sidebar button.active {
      background-color: red;
      color: white;
    }

    .main-content {
      flex-grow: 1;
      padding: 20px;
      margin-left: 160px;
      background-color: #f9f9f9;
      overflow-y: auto;
    }

    h2 {
      text-align: center;
      background-color: #95cb48;
      color: white;
      padding: 10px;
      font-size: 20px;
      border: none;
      width: fit-content;
      margin: 0 auto 20px auto;
    }

    .add-form {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 30px;
    }

    .add-form textarea {
      width: 300px;
      height: 60px;
      padding: 6px;
    }

    .add-form select,
    .add-form button {
      padding: 6px;
    }

    .add-form button {
      background-color: #95cb48;
      color: white;
      border: none;
      cursor: pointer;
    }

    .add-form button:hover {
      background-color: #7eb537;
    }

    .card {
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 15px;
      margin-bottom: 15px;
    }

    .message-card { background: #e8f0ff; }
    .notification-card { background: #fffbe5; }

    .reply-card {
      background: #f0f0ff;
      margin-left: 30px;
      border-left: 4px solid #007bff;
      margin-top: 10px;
    }

    .card small {
      display: block;
      color: #666;
      font-size: 12px;
      margin-top: 5px;
    }

    .reply-form textarea {
      width: 100%;
      height: 50px;
      margin-top: 8px;
      resize: none;
    }

    .reply-form button {
      margin-top: 5px;
      padding: 5px 10px;
      background-color: #444;
      color: white;
      border: none;
      cursor: pointer;
    }
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
