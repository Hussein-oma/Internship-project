<?php
require_once 'config.php';
require_once 'email_notification.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($id && in_array($action, ['approved', 'declined'])) {
        try {
            $stmt = $pdo->prepare("UPDATE internship_applications SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $action,
                ':id' => $id
            ]);
            
            // Get applicant details for both approved and declined cases
            $applicantStmt = $pdo->prepare("SELECT fullname, email FROM internship_applications WHERE id = ?");
            $applicantStmt->execute([$id]);
            $applicant = $applicantStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($applicant) {
                // If application is approved, send registration link email
                if ($action === 'approved') {
                    // Generate a unique token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));
                    
                    // Store token in database
                    // This way we don't need to create a user until they register
                    $tokenStmt = $pdo->prepare("INSERT INTO registration_tokens (token, expires_at, applicant_id, user_id) VALUES (?, ?, ?, NULL)");
                    $tokenStmt->execute([$token, $expires, $id]);
                    
                    // Get the last inserted ID to use for the registration link
                    $tokenId = $pdo->lastInsertId();
                    
                    // Send registration link email
                    sendRegistrationLinkEmail($id, $applicant['email'], $applicant['fullname'], $token);
                }
                // If application is declined, send polite rejection email
                else if ($action === 'declined') {
                    // Send rejection email
                    sendApplicationDeclinedEmail($applicant['email'], $applicant['fullname']);
                }
            }
            
            header("Location: admin_dashboard.php");
            exit;
        } catch (PDOException $e) {
            die("Error updating status: " . $e->getMessage());
        }
    }
}
