<?php
// Connect to the database
require_once 'config.php';

// Fetch internship fields
$fields = [];
try {
    $stmt = $pdo->query("SELECT field_name FROM internship_fields");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $fields = [];
}

// Fetch application status and close date
$appStatusText = "Applications is currently CLOSED.";
$isAppOpen = false;
try {
    $stmt = $pdo->query("SELECT status, close_date FROM application_dates WHERE id = 1");
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($app && $app['status'] === 'open') {
        $deadline = date("d/m/Y", strtotime($app['close_date']));
        $appStatusText = "Applications (OPEN) deadline: $deadline";
        $isAppOpen = true;
    }
} catch (PDOException $e) {
    $appStatusText = "Unable to load application status.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Out West Ltd - Internship Portal</title>
  <link rel="stylesheet" href="websites.css">
</head>
<body>

  <div class="background-layer"></div>

  <div class="container">

    <div class="top-bar">
      <span class="deadline"><?= htmlspecialchars($appStatusText) ?></span>

      <?php if ($isAppOpen): ?>
        <a href="http://localhost/out-west/application.html">Apply Now</a>
      <?php else: ?>
        <a href="#" onclick="alert('🚫 Application is currently closed. Please check back later.'); return false;">Apply Now</a>
      <?php endif; ?>

      <a href="http://localhost/Out-west/status.php">Check status of your application</a>
    </div>

    <!-- Open Internship Fields Dropdown -->
    <div class="dropdown-field">
      <button class="dropdown-btn">Open Internship Fields &#9662;</button>
      <div class="dropdown-content">
        <?php if (!empty($fields)): ?>
          <ul>
            <?php foreach ($fields as $field): ?>
              <li><?= htmlspecialchars($field['field_name']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>No open fields available at this time.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="logo">
      <img src="logo.jpeg" alt="Company Logo">
    </div>

    <h1 class="welcome">Welcome to outwest internship portal.</h1>

    <!-- Slideshow -->
    <div class="slideshow-container">
      <img class="slide" src="it-staff.jpeg" alt="Slide 1">
      <img class="slide" src="it-staff1.jpeg" alt="Slide 2">
      <img class="slide" src="it-staff2.jpeg" alt="Slide 3">
      <img class="slide" src="it-staff3.jpeg" alt="Slide 4">
    </div>

    <div class="bottom-section">
      <div class="contacts">
        <strong>CONTACTS</strong><br>
        TEL: 0729779365<br>
        EMAIL: husseinadanomar18@gmail.com<br>
        Location: Nairobi CBD
      </div>

      <div class="about">
        <button class="dropdown-btn">ABOUT US &#9662;</button>
        <div class="dropdown-content">
          Welcome to our IT company. Our expertise spans software development, 
          network management, cybersecurity, and IT consulting.
        </div>
      </div>
    </div>

  </div>

  <script>
    // Slideshow
    let slideIndex = 0;
    const slides = document.getElementsByClassName("slide");

    function showSlides() {
      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      slideIndex++;
      if (slideIndex > slides.length) slideIndex = 1;
      slides[slideIndex - 1].style.display = "block";
      setTimeout(showSlides, 3000);
    }
    window.onload = showSlides;

    // Dropdown toggle
    document.querySelectorAll(".dropdown-btn").forEach(button => {
      button.addEventListener("click", function () {
        const content = this.nextElementSibling;
        content.style.display = (content.style.display === "none" || content.style.display === "") ? "block" : "none";
      });
    });
  </script>

</body>
</html>
