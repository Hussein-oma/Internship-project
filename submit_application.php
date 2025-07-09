<?php
require_once 'config.php';

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $fullname     = trim($_POST['fullname'] ?? '');
    $dob          = date('Y-m-d', strtotime($_POST['dob'] ?? ''));
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $gender       = $_POST['gender'] ?? '';
    $nationality  = trim($_POST['nationality'] ?? '');

    $institution  = trim($_POST['institution'] ?? '');
    $course       = trim($_POST['course'] ?? '');
    $level        = $_POST['level'] ?? '';
    $year         = trim($_POST['year'] ?? '');
    $graduation   = trim($_POST['graduation'] ?? '');

    $departments = $_POST['department'] ?? [];
    $department  = is_array($departments) ? implode(", ", $departments) : $departments;
    $other_department = trim($_POST['other_department'] ?? '');

    $duration         = $_POST['duration'] ?? '';
    $duration_other   = trim($_POST['duration_other'] ?? '');
    $startdate        = date('Y-m-d', strtotime($_POST['startdate'] ?? ''));
    $accommodation    = $_POST['accommodation'] ?? '';
    $paid             = $_POST['paid'] ?? '';
    $amount           = is_numeric($_POST['amount']) ? $_POST['amount'] : 0;

    $skills           = trim($_POST['skills'] ?? '');
    $company          = trim($_POST['company'] ?? '');
    $role             = trim($_POST['role'] ?? '');
    $exp_duration     = trim($_POST['exp_duration'] ?? '');
    $responsibilities = trim($_POST['responsibilities'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO internship_applications (
            fullname, dob, phone, email, gender, nationality,
            institution, course, level, year, graduation,
            department, other_department, duration, duration_other, startdate,
            accommodation, paid, amount,
            skills, company, role, exp_duration, responsibilities
        ) VALUES (
            :fullname, :dob, :phone, :email, :gender, :nationality,
            :institution, :course, :level, :year, :graduation,
            :department, :other_department, :duration, :duration_other, :startdate,
            :accommodation, :paid, :amount,
            :skills, :company, :role, :exp_duration, :responsibilities
        )");

        $stmt->execute([
            ':fullname'        => $fullname,
            ':dob'             => $dob,
            ':phone'           => $phone,
            ':email'           => $email,
            ':gender'          => $gender,
            ':nationality'     => $nationality,
            ':institution'     => $institution,
            ':course'          => $course,
            ':level'           => $level,
            ':year'            => $year,
            ':graduation'      => $graduation,
            ':department'      => $department,
            ':other_department'=> $other_department,
            ':duration'        => $duration,
            ':duration_other'  => $duration_other,
            ':startdate'       => $startdate,
            ':accommodation'   => $accommodation,
            ':paid'            => $paid,
            ':amount'          => $amount,
            ':skills'          => $skills,
            ':company'         => $company,
            ':role'            => $role,
            ':exp_duration'    => $exp_duration,
            ':responsibilities'=> $responsibilities
        ]);

        echo "<p style='color: green; font-size: 18px;'>✅ Application submitted successfully!<br>Avail yourself for interview on 05/09/2025</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red; font-size: 18px;'>❌ Error submitting application: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red; font-size: 18px;'>❌ Invalid request.</p>";
}
?>

<!-- Back Button -->
<p style="margin-top: 20px;">
  <a href="website.php" style="padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">← Back to Website</a>
</p>
