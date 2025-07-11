<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $intern_id = $_SESSION['user_id'] ?? null;

    if (!$intern_id) {
        die("Unauthorized");
    }

    // Fetch the report and intern info
    $stmt = $pdo->prepare("SELECT wr.*, u.name AS intern_name FROM weekly_reports wr 
        JOIN users u ON wr.intern_id = u.id 
        WHERE wr.id = ? AND wr.intern_id = ?");
    $stmt->execute([$report_id, $intern_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        die("Report not found");
    }

    // Prepare HTML content
    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Weekly Report - ' . htmlspecialchars($report['intern_name']) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            line-height: 1.6;
            background-color: #fff;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .header img {
            height: 60px;
        }
        h2 {
            margin-top: 0;
        }
        .report-section {
            margin-bottom: 20px;
        }
        .report-section ul {
            list-style: none;
            padding: 0;
        }
        .report-section li {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
        }
        .signature-box {
            margin-top: 40px;
        }
        hr {
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.jpeg" alt="Company Logo"><br>
        <h2>Out-West Internship Program</h2>
        <p><strong>Weekly Report</strong></p>
    </div>

    <div class="report-section">
        <p><span class="label">Intern Name:</span> ' . htmlspecialchars($report['intern_name']) . '</p>
        <p><span class="label">Week Ending:</span> ' . htmlspecialchars($report['week_ending']) . '</p>
        <ul>';

    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
        $lower = strtolower($day);
        $html .= '<li><span class="label">' . $day . ':</span> ' . nl2br(htmlspecialchars($report[$lower])) . '</li>';
    }

    $html .= '
        </ul>
    </div>

    <div class="report-section">
        <p><span class="label">Weekly Summary:</span><br>' . nl2br(htmlspecialchars($report['weekly_summary'])) . '</p>
    </div>

    <div class="signature-box">
        <p><span class="label">Intern Signature:</span> ' . htmlspecialchars($report['signature']) . '</p>
        <p><span class="label">Date:</span> ' . htmlspecialchars($report['report_date']) . '</p>
    </div>';

    if (!empty($report['supervisor_comments'])) {
        $html .= '
        <hr>
        <div class="report-section">
            <p><span class="label">Supervisor Comments:</span><br>' . nl2br(htmlspecialchars($report['supervisor_comments'])) . '</p>
            <p><span class="label">Supervisor Signature:</span> ' . htmlspecialchars($report['supervisor_signature']) . '</p>
            <p><span class="label">Date:</span> ' . htmlspecialchars($report['supervisor_date']) . '</p>
        </div>';
    }

    $html .= '
</body>
</html>';

    // Send headers for HTML file download
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=weekly_report_" . $report['week_ending'] . ".html");

    echo $html;
    exit;
}
?>
