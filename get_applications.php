<?php
$conn = new mysqli("localhost", "root", "", "internship_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get status from URL or default to 'All'
$status = isset($_GET['status']) ? $_GET['status'] : 'All';
$query = "SELECT * FROM applications";

// Prepare query based on status
if ($status !== 'All') {
    $query .= " WHERE status = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Institution</th>
                <th>Institution Address</th>
                <th>Course</th>
                <th>Reason</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['institution_name']}</td>
                <td>{$row['institution_address']}</td>
                <td>{$row['course']}</td>
                <td>{$row['internship_reason']}</td>
                <td>{$row['start_date']}</td>
                <td>{$row['end_date']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button onclick=\"updateStatus({$row['id']}, 'Approved')\">Approve</button>
                    <button onclick=\"updateStatus({$row['id']}, 'Declined')\">Decline</button>
                </td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p>No applications found.</p>";
}

$conn->close();
?>
