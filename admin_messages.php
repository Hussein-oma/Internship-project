<?php
session_start();
require_once 'config.php';
require_once 'email_notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch supervisors and interns
$supervisors = $pdo->query("SELECT id, name FROM users WHERE role = 'supervisor'")->fetchAll(PDO::FETCH_ASSOC);
$interns = $pdo->query("SELECT id, name FROM users WHERE role = 'intern'")->fetchAll(PDO::FETCH_ASSOC);

// Handle sending messages and replies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_text'], $_POST['recipients'], $_POST['type']) && is_array($_POST['recipients'])) {
        $message = trim($_POST['message_text']);
        $recipients = $_POST['recipients'];
        $type = $_POST['type'];

        if (!empty($message) && !empty($recipients) && in_array($type, ['message', 'notification'])) {
            $group_id = uniqid('msg_', true);

            // Get admin name for email notifications
            $admin_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'admin'");
            $admin_stmt->execute([$admin_id]);
            $admin_name = $admin_stmt->fetchColumn() ?: 'Admin';
            
            foreach ($recipients as $recipient) {
                if (in_array($recipient, ['all_interns', 'all_supervisors', 'all_users'])) {
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_group, type, group_id)
                                           VALUES (?, NOW(), ?, 'admin', ?, ?, ?)");
                    $stmt->execute([$message, $admin_id, $recipient, $type, $group_id]);
                    
                    // Send group email notification if checkbox is checked
                    if (isset($_POST['send_email'])) {
                        sendGroupEmailNotification($recipient, $message, $admin_name, $type);
                    }
                } else {
                    list($id, $role) = explode('_', $recipient);
                    $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_id, recipient_role, type, group_id)
                                           VALUES (?, NOW(), ?, 'admin', ?, ?, ?, ?)");
                    $stmt->execute([$message, $admin_id, $id, $role, $type, $group_id]);
                    
                    // Send individual email notification if checkbox is checked
                    if (isset($_POST['send_email'])) {
                        sendEmailNotification($id, $role, $message, $admin_name, $type);
                    }
                }
            }
        }
        header("Location: admin_messages.php");
        exit();
    }

    if (isset($_POST['reply_text'], $_POST['reply_to_id'], $_POST['reply_to_role'], $_POST['group_id'], $_POST['reply_to_message_id'])) {
        $reply_text = trim($_POST['reply_text']);
        if (!empty($reply_text)) {
            $stmt = $pdo->prepare("INSERT INTO messages (content, created_at, user_id, user_role, recipient_id, recipient_role, type, group_id, reply_to_message_id)
                                   VALUES (?, NOW(), ?, 'admin', ?, ?, 'reply', ?, ?)");
            // Get admin name for email notifications
            $admin_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'admin'");
            $admin_stmt->execute([$admin_id]);
            $admin_name = $admin_stmt->fetchColumn() ?: 'Admin';
            
            $stmt->execute([
                $reply_text,
                $admin_id,
                $_POST['reply_to_id'],
                $_POST['reply_to_role'],
                $_POST['group_id'],
                $_POST['reply_to_message_id']
            ]);
        
            // Send email notification for reply if checkbox is checked
            if (isset($_POST['send_email'])) {
                sendEmailNotification($_POST['reply_to_id'], $_POST['reply_to_role'], $reply_text, $admin_name, 'reply');
            }
        }
        header("Location: admin_messages.php");
        exit();
    }
}

// Fetch messages
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
  <title>Admin Messages</title>
  <style>
    body {
      margin: 0;
      display: flex;
      font-family: Arial, sans-serif;
    }

    .sidebar {
      width: 140px;
      background-color: #95cb48;
      border-right: 1px solid #333;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 30px;
    }

    .sidebar button {
      width: 100px;
      margin: 8px 0;
      padding: 8px;
      border: 1px solid #444;
      background-color: transparent;
      color: white;
      cursor: pointer;
      border-radius: 4px;
    }

    .sidebar button.active {
      background-color: #dc1511;
      color: white;
      font-weight: bold;
    }

    .sidebar img.logo {
      max-height: 65px;
      margin-bottom: 20px;
    }

    .main {
      flex-grow: 1;
      padding: 20px;
      background-color: #f9f9f9;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .send-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 30px;
      background: #fff;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .send-form textarea {
      width: 300px;
      height: 60px;
      padding: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .send-form select, .send-form button {
      padding: 6px;
      font-size: 14px;
    }

    .send-form button {
      background-color: #95cb48;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .send-form button:hover {
      background-color: #7da640;
    }

    .card {
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #fff;
    }

    .message-card {
      background-color: #e8f0ff;
    }

    .notification-card {
      background-color: #fffbe5;
    }

    .reply-card {
      background: #f0f0ff;
      margin-left: 30px;
      border-left: 4px solid #007bff;
    }

    .reply-form textarea {
      width: 100%;
      height: 50px;
      margin-top: 8px;
      padding: 6px;
      border: 1px solid #ccc;
      resize: none;
    }

    .reply-form button {
      margin-top: 5px;
      padding: 6px 12px;
      background-color: #95cb48;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .reply-form button:hover {
      background-color: #7da640;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <img src="logo.jpeg" alt="Logo" class="logo">
  <button onclick="location.href='admin_dashboard.php'">Dashboard</button>
  <button onclick="location.href='interns_dashboard.php'">Internship Field</button>
  <button onclick="location.href='admin_dashboard.php'">Applications</button>
  <button onclick="location.href='interns_dashboard.php'">Interns</button>
  <button onclick="location.href='supervisor_dashboard.php'">Supervisors</button>
  <button class="active">Messages</button>
  <button onclick="location.href='admin_reports.php'">Reports</button>
  <button onclick="location.href='logout.php'">Logout</button>
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
    
    <div style="display: flex; align-items: center; margin: 5px 0;">
      <input type="checkbox" id="send_email" name="send_email" value="1" checked>
      <label for="send_email" style="margin-left: 5px;">Send via email</label>
    </div>

    <button type="submit">Send</button>
  </form>

  <?php foreach ($parent_messages as $msg):
    $class = $msg['type'] === 'notification' ? 'notification-card' : 'message-card';
    $replies = $replies_map[$msg['id']] ?? [];
    $already_replied = false;
    foreach ($replies as $r) {
        if ($r['user_id'] == $admin_id) $already_replied = true;
    }
  ?>
    <div class="card <?= $class ?>">
      <p><?= htmlspecialchars($msg['content']) ?></p>
      <small>
        <?= $msg['type'] === 'notification' ? '🔔 Notification' : '💬 Message' ?>
        | From: <?= ucfirst($msg['user_role']) ?> - <?= htmlspecialchars($msg['sender_name'] ?? 'Unknown') ?>
        | <?= $msg['created_at'] ?>
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
          <div style="display: flex; align-items: center; margin: 5px 0;">
            <input type="checkbox" id="send_email_reply_<?= $msg['id'] ?>" name="send_email" value="1" checked>
            <label for="send_email_reply_<?= $msg['id'] ?>" style="margin-left: 5px;">Send via email</label>
          </div>
          <button type="submit">Reply</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
