<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "internship_portal";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect and sanitize inputs
$full_name = $_POST['full_name'];
$dob = $_POST['dob'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$gender = $_POST['gender'];
$nationality = $_POST['nationality'];
$institution = $_POST['institution'];
$course = $_POST['course'];
$level = $_POST['level']; // From radio button
$year_study = $_POST['year_study'];
$grad_year = $_POST['grad_year'];
$department = isset($_POST['department']) ? implode(", ", $_POST['department']) : '';
$other_dept = $_POST['other_dept'];
$duration = $_POST['duration'];
if (isset($_POST['other_duration']) && !empty(trim($_POST['other_duration']))) {
    $duration .= " - " . trim($_POST['other_duration']);
}
$start_date = $_POST['start_date'];
$accommodation = $_POST['accommodation'] ?? 'No';
$paid = $_POST['paid'] ?? 'No';
$amount = $_POST['amount']; // corrected name
$skills = $_POST['skills'];
$company = $_POST['company'];
$position = $_POST['position'];
$work_duration = $_POST['work_duration'];
$responsibilities = $_POST['responsibilities'];

// Prepare and execute SQL
$sql = "INSERT INTO applications (
    full_name, dob, phone, email, gender, nationality, institution, course, level, 
    year_study, grad_year, department, other_dept, duration, start_date, accommodation, 
    paid, amount, skills, company, position, work_duration, responsibilities
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssssssssssssssss", 
    $full_name, $dob, $phone, $email, $gender, $nationality, $institution, $course, $level,
    $year_study, $grad_year, $department, $other_dept, $duration, $start_date, $accommodation, 
    $paid, $amount, $skills, $company, $position, $work_duration, $responsibilities
);

if ($stmt->execute()) {
    echo "✅ Application submitted successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
