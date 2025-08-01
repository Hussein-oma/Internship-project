# Email Notifications System for Internship Portal

## Overview
This system allows users to receive email notifications when messages or notifications are sent through the Internship Portal. The integration uses PHPMailer to send emails through SMTP.

## Configuration

### 1. Email Credentials Setup
Before using the email notification system, you need to configure your email credentials in the `email_notification.php` file:

1. Open `email_notification.php`
2. Locate the following lines:
   ```php
   $mail->Username   = 'husseinadanomar18@gmail.com'; // Gmail address
   $mail->Password   = 'Haski8167';   // Gmail Password
   $mail->setFrom('husseinadanomar18@gmail.com', 'Internship Portal');
   ```
3. Replace with your actual Gmail address and app password

### 2. Gmail App Password
To use Gmail SMTP, you need to create an App Password:

1. Go to your Google Account settings
2. Navigate to Security > 2-Step Verification
3. Scroll down to "App passwords"
4. Create a new app password for "Mail"
5. Use the generated password in the configuration

### 3. Testing the System
To test if the email system is working:

1. Log in as an admin, supervisor, or intern
2. Send a message or notification to another user
3. Check if the recipient receives an email notification

## Features

- Email notifications for direct messages
- Email notifications for group messages
- Email notifications for replies
- HTML-formatted emails with links back to the portal

## Troubleshooting

If emails are not being sent:

1. Check that your Gmail credentials are correct
2. Ensure that "Less secure app access" is enabled in your Google account or use an App Password
3. Check the server error logs for any PHP errors
4. Verify that the recipient email addresses are valid

## Security Notes

- Never commit your email credentials to version control
- Consider using environment variables for sensitive information
- Regularly update your app password for security