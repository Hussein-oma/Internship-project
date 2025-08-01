# Email Setup Instructions for Internship Portal

## Gmail Configuration

To enable email sending through the Internship Portal application, you need to configure your Gmail account properly. Follow these steps:

### 1. Create an App Password (Recommended Method)

Google no longer allows less secure apps to access Gmail accounts. Instead, you need to use an App Password:

1. Go to your Google Account settings: https://myaccount.google.com/
2. Select "Security" from the left menu
3. Under "Signing in to Google," select "2-Step Verification" (enable it if not already enabled)
4. At the bottom of the page, select "App passwords"
5. Select "Mail" as the app and "Other" as the device (name it "Internship Portal")
6. Click "Generate"
7. Google will display a 16-character password. Copy this password.
8. Update the `email_notification.php` file with this password:

```php
$mail->Password = 'xxxx xxxx xxxx xxxx'; // App password format (16 characters with spaces)
```

### 2. Allow Less Secure Apps (Alternative, Less Secure Method)

If you cannot use an App Password for some reason:

1. Go to https://myaccount.google.com/lesssecureapps
2. Turn on "Allow less secure apps"
3. Note: Google may still block sign-in attempts from apps that it deems less secure

## Troubleshooting Email Issues

If emails are not being sent or received:

1. Verify that your Gmail username and password are correct
2. Check if your Gmail account has 2-Step Verification enabled (if yes, you MUST use an App Password)
3. Make sure your server allows outgoing connections on port 587
4. Check if your Gmail account has exceeded sending limits
5. Verify the recipient email address is valid and correctly formatted

### Debug Mode

If you need to troubleshoot email sending issues, you can temporarily enable debug output by changing the SMTPDebug setting in email_notification.php:

```php
$mail->SMTPDebug = 2; // Enable verbose debug output (0 = off, 1 = client, 2 = client and server)
```

**Important:** Always set SMTPDebug back to 0 in production to prevent "headers already sent" errors, as debug output will be sent to the browser before any redirects.

## Email Configuration in the Application

The email settings are configured in `email_notification.php`. The current settings are:

```php
$mail->Host       = 'smtp.gmail.com';      // Gmail SMTP server
$mail->SMTPAuth   = true;
$mail->Username   = 'omar.hussein2022@students.jkuat.ac.ke'; // Gmail address
$mail->Password   = 'nrmv bqpz kmms pcia';   // App Password
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;
```

Update these settings as needed for your specific email configuration.