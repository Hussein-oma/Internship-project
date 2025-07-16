<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $intern_id = $_SESSION['user_id'] ?? null;

    if (!$intern_id) {
        die("Unauthorized");
    }

    // Fetch report
    $stmt = $pdo->prepare("SELECT wr.*, u.name AS intern_name FROM weekly_reports wr 
        JOIN users u ON wr.intern_id = u.id 
        WHERE wr.id = ? AND wr.intern_id = ?");
    $stmt->execute([$report_id, $intern_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        die("Report not found");
    }

    // Convert logo image to Base64
    $logoPath = 'logo.jpeg';
    $logoData = '';
    $logoType = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoType = mime_content_type($logoPath);
    }

    // Prepare downloadable HTML
    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Weekly Report - ' . htmlspecialchars($report['intern_name']) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #fff;
        }
        h2 {
            margin-bottom: 10px;
        }
        h3 {
            margin-top: 30px;
        }
        img {
            height: 60px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        th:first-child,
        td:first-child {
            width: 80px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="text-align: center;">';

    if ($logoData) {
        $html .= '<img src="data:' . $logoType . ';base64,' . $logoData . '" alt="Logo"><br>';
    }

    $html .= '
        <h2>Out-west Internship Dashboard</h2>
        <p><strong>Weekly Report</strong></p>
    </div>

    <h3>Previous Week Report (Week Ending: ' . htmlspecialchars($report['week_ending']) . ')</h3>
    <table>
        <tr><th>Day</th><th>Description</th></tr>';

    $days = [
        'MON' => 'monday',
        'TUE' => 'tuesday',
        'WED' => 'wednesday',
        'THUR' => 'thursday',
        'FRI' => 'friday'
    ];
    foreach ($days as $label => $field) {
        $html .= '<tr><td>' . $label . '</td><td>' . nl2br(htmlspecialchars($report[$field])) . '</td></tr>';
    }

    $html .= '
        <tr><td colspan="2"><strong>TRAINEE’S REPORT:</strong><br>' . nl2br(htmlspecialchars($report['weekly_summary'])) . '</td></tr>
        <tr><td>Signature:</td><td>' . htmlspecialchars($report['signature']) . ' on ' . htmlspecialchars($report['report_date']) . '</td></tr>';

    if (!empty($report['supervisor_comments'])) {
        $html .= '
        <tr><td colspan="2"><strong>SUPERVISOR’S REPORT:</strong><br>' . nl2br(htmlspecialchars($report['supervisor_comments'])) . '</td></tr>
        <tr><td>Supervisor Signature:</td><td>' . htmlspecialchars($report['supervisor_signature']) . ' on ' . htmlspecialchars($report['supervisor_date']) . '</td></tr>';
    }

    $html .= '
    </table>
</body>
</html>';

    // Send as downloadable HTML file
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=weekly_report_" . $report['week_ending'] . ".html");

    echo $html;
    exit;
}
?>
