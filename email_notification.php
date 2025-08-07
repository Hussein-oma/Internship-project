<?php
require_once 'config.php';
require_once 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

/**
 * Send registration link email to approved applicants
 * 
 * @param int $applicant_id The ID of the approved applicant
 * @param string $email The email of the approved applicant
 * @param string $name The name of the approved applicant
 * @param string $token The registration token
 * @return bool True if email sent successfully, false otherwise
 */
function sendRegistrationLinkEmail($applicant_id, $email, $name, $token) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke';
        $mail->Password   = 'nrmv bqpz kmms pcia';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPDebug  = 0;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('omar.hussein2022@students.jkuat.ac.ke', 'Internship Portal');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Internship Application Has Been Approved';
        
        // Registration link with token
        $registration_link = 'http://localhost/internship-portal/register.php?token=' . $token . '&id=' . $applicant_id;
        
        // Email body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>🎉 Congratulations! Your Internship Application is Approved</h2>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p>Dear {$name},</p>
                    <p>We are pleased to inform you that your application for the internship program has been approved.</p>
                    <p>To complete your registration, please click the button below to create your account:</p>
                </div>
                <p><a href='{$registration_link}' style='display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Complete Registration</a></p>
                <p>Or copy and paste this link in your browser:</p>
                <p>{$registration_link}</p>
                <p>This link will expire in 48 hours.</p>
                <p>Welcome to our internship program!</p>
            </div>
        ";
        
        $mail->AltBody = "Congratulations! Your internship application has been approved.\n\nDear {$name},\n\nWe are pleased to inform you that your application for the internship program has been approved.\n\nTo complete your registration, please visit the following link to create your account:\n\n{$registration_link}\n\nThis link will expire in 48 hours.\n\nWelcome to our internship program!";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error but don't expose to user
        error_log("Registration email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send email notification for new messages
 * 
 * @param int $recipient_id The ID of the recipient
 * @param string $recipient_role The role of the recipient (admin, supervisor, intern)
 * @param string $message_content The content of the message
 * @param string $sender_name The name of the sender
 * @param string $message_type The type of message (message, notification)
 * @return bool True if email sent successfully, false otherwise
 */
function sendEmailNotification($recipient_id, $recipient_role, $message_content, $sender_name, $message_type) {
    global $pdo;
    
    // Get recipient email
    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ? AND role = ?");
    $stmt->execute([$recipient_id, $recipient_role]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipient) {
        return false; // Recipient not found
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';      // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke'; // Gmail address
        $mail->Password   = 'nrmv bqpz kmms pcia';   // Gmail Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPDebug  = 0;                    // Disable debug output (0 = off, 1 = client, 2 = client and server)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('omar.hussein2022@students.jkuat.ac.ke', 'Internship Portal'); // Gmail address
        $mail->addAddress($recipient['email'], $recipient['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $message_type === 'notification' ? 'New Notification from Internship Portal' : 'New Message from Internship Portal';
        
        // Email body
        $icon = $message_type === 'notification' ? '🔔' : '💬';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>$icon New {$message_type} from {$sender_name}</h2>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p>{$message_content}</p>
                </div>
                <p>Please log in to the Internship Portal to respond.</p>
                <p><a href='http://localhost/internship-portal/login.php' style='display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Go to Portal</a></p>
            </div>
        ";
        
        $mail->AltBody = "$icon New {$message_type} from {$sender_name}\n\n{$message_content}\n\nPlease log in to the Internship Portal to respond.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error but don't expose to user
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send email notifications to a group of users
 * 
 * @param string $group The group to send to (all_interns, all_supervisors, all_users)
 * @param string $message_content The content of the message
 * @param string $sender_name The name of the sender
 * @param string $message_type The type of message (message, notification)
 * @return int Number of successful emails sent
 */
function sendGroupEmailNotification($group, $message_content, $sender_name, $message_type) {
    global $pdo;
    
    $sql = "SELECT id, email, name, role FROM users WHERE ";
    
    switch ($group) {
        case 'all_interns':
            $sql .= "role = 'intern'";
            break;
        case 'all_supervisors':
            $sql .= "role = 'supervisor'";
            break;
        case 'all_users':
            $sql .= "role IN ('intern', 'supervisor', 'admin')";
            break;
        default:
            return 0; // Invalid group
    }
    
    $stmt = $pdo->query($sql);
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $success_count = 0;
    
    foreach ($recipients as $recipient) {
        if (sendEmailNotification($recipient['id'], $recipient['role'], $message_content, $sender_name, $message_type)) {
            $success_count++;
        }
    }
    
    return $success_count;
}

/**
 * Send application confirmation email to applicants
 * 
 * @param string $email The email of the applicant
 * @param string $name The name of the applicant
 * @return bool True if email sent successfully, false otherwise
 */
function sendApplicationConfirmationEmail($email, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke';
        $mail->Password   = 'nrmv bqpz kmms pcia';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPDebug  = 0;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('omar.hussein2022@students.jkuat.ac.ke', 'Internship Portal');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Internship Application Received';
        
        // Email body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>✅ Application Received</h2>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p>Dear {$name},</p>
                    <p>Thank you for submitting your internship application. We have received your application and it is currently under review.</p>
                    <p>We will notify you of any updates regarding your application status.</p>
                </div>
                <p>You can check the status of your application using your email address at any time.</p>
                <p><a href='http://localhost/internship-portal/status.php' style='display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Check Application Status</a></p>
                <p>If you have any questions, please feel free to contact us.</p>
            </div>
        ";
        
        $mail->AltBody = "Application Received\n\nDear {$name},\n\nThank you for submitting your internship application. We have received your application and it is currently under review.\n\nWe will notify you of any updates regarding your application status.\n\nYou can check the status of your application using your email address at any time at: http://localhost/internship-portal/status.php\n\nIf you have any questions, please feel free to contact us.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error but don't expose to user
        error_log("Application confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send application declined email to applicants
 * 
 * @param string $email The email of the applicant
 * @param string $name The name of the applicant
 * @return bool True if email sent successfully, false otherwise
 */
function sendApplicationDeclinedEmail($email, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke';
        $mail->Password   = 'nrmv bqpz kmms pcia';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPDebug  = 0;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('omar.hussein2022@students.jkuat.ac.ke', 'Internship Portal');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Internship Application Status Update';
        
        // Email body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>Application Status Update</h2>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p>Dear {$name},</p>
                    <p>Thank you for your interest in our internship program and for taking the time to apply.</p>
                    <p>After careful consideration of your application, we regret to inform you that we are unable to offer you an internship position at this time.</p>
                    <p>We appreciate your interest in our organization and encourage you to apply for future opportunities that match your skills and interests.</p>
                </div>
                <p>We wish you the best in your future endeavors.</p>
            </div>
        ";
        
        $mail->AltBody = "Application Status Update\n\nDear {$name},\n\nThank you for your interest in our internship program and for taking the time to apply.\n\nAfter careful consideration of your application, we regret to inform you that we are unable to offer you an internship position at this time.\n\nWe appreciate your interest in our organization and encourage you to apply for future opportunities that match your skills and interests.\n\nWe wish you the best in your future endeavors.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error but don't expose to user
        error_log("Application declined email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>