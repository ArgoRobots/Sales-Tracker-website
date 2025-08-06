<?php

/**
 * Send license key via email using PHP mail
 * 
 * @param string $to_email Recipient email address
 * @param string $license_key The license key to send
 * @return bool True if successful, false otherwise
 */
function send_license_email($to_email, $license_key)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Your Argo Sales Tracker License Key';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Your Argo Sales Tracker License</title>
            <style>
                 {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Sales Tracker Logo">
                </div>
                
                <div class="content">
                    <h1>Thank You for Your Purchase!</h1>
                    <p>Here is your Argo Sales Tracker license key:</p>
                    
                    <div class="license-key">{$license_key}</div>
                    
                    <div class="steps">
                        <h2>How to Activate Your License</h2>
                        <ol>
                            <li>Open Argo Sales Tracker on your computer</li>
                            <li>Click the blue upgrade button on the top right</li>
                            <li>Enter your license key</li>
                            <li>Enjoy unlimited access to all premium features!</li>
                        </ol>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/documentation/index.php" class="button">View Documentation</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php" style="color: #2563eb;">contact our support team</a>.</p>
                    <p>Thank you for choosing Argo Sales Tracker!</p>
                </div>
                
                <div class="footer">
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$to_email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Sales Tracker <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mail_result = mail($to_email, $subject, $email_html, implode("\r\n", $headers));
    return $mail_result;
}

/**
 * Send resend license key via email using PHP mail
 * 
 * @param string $to_email Recipient email address
 * @param string $license_key The license key to resend
 * @return bool True if successful, false otherwise
 */
function resend_license_email($to_email, $license_key)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Your Requested Argo Sales Tracker License Key';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Your Argo Sales Tracker License</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Sales Tracker Logo">
                </div>
                
                <div class="content content-centered">
                    <h1>Your License Key</h1>
                    <p>As requested, here is your Argo Sales Tracker license key:</p>
                    
                    <div class="license-key">{$license_key}</div>
                    
                    <div class="steps steps-centered">
                        <h2>How to Activate Your License</h2>
                        <ol>
                            <li>Open Argo Sales Tracker on your computer</li>
                            <li>Click the blue upgrade button on the top right</li>
                            <li>Enter your license key</li>
                            <li>Enjoy unlimited access to all premium features!</li>
                        </ol>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/documentation/index.php" class="button button-resend">View Documentation</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php" style="color: #2563eb;">contact our support team</a>.</p>
                    <p>Thank you for using Argo Sales Tracker!</p>
                </div>
                
                <div class="footer">
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$to_email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Sales Tracker <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mail_result = mail($to_email, $subject, $email_html, implode("\r\n", $headers));
    return $mail_result;
}

/**
 * Send verification email with code
 * 
 * @param string $email User's email address
 * @param string $code Verification code
 * @param string $username Username
 * @return bool Success status
 */
function send_verification_email($email, $code, $username)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Verify Your Account - Argo Sales Tracker';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Verify Your Account</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Welcome to the Argo Community!</h2>
                </div>
                <div class="content">
                    <p>Hello {$username},</p>
                    <p>Thank you for registering. <strong>Email verification is required</strong> to activate your account and access your license key.</p>
                    
                    <p>Please use the following verification code to complete your registration:</p>
                    <div class="verification-code">{$code}</div>
                    
                    <p>This code will expire in 24 hours.</p>
                    
                    <p>If you did not sign up for an account, you can ignore this email.</p>
                    <p>Regards,<br>The Argo Team</p>
                </div>
                <div class="footer">
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Sales Tracker <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mail_result = mail($email, $subject, $email_html, implode("\r\n", $headers));
    return $mail_result;
}

/**
 * Community admin notification sender
 * 
 * @param string $type Notification type ('new_post', 'new_comment')
 * @param array $data Notification data
 * @return bool Success status
 */
function send_notification_email($type, $data)
{
    $db = get_db_connection();

    // Get all admins with the corresponding notification enabled
    $notification_column = ($type === 'new_post') ? 'notify_new_posts' : 'notify_new_comments';

    $stmt = $db->prepare("SELECT u.username, ans.notification_email 
                         FROM admin_notification_settings ans
                         JOIN community_users u ON ans.user_id = u.id
                         WHERE u.role = 'admin' AND ans.$notification_column = 1");
    $stmt->execute();
    $result = $stmt->get_result();

    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row;
    }

    $stmt->close();

    // If no admins have notifications enabled, exit early
    if (empty($recipients)) {
        return true;
    }

    // Load CSS for email template
    $css = file_get_contents(__DIR__ . '/email.css');

    // Prepare email content
    $subject = '';
    $site_url = get_site_url();

    if ($type === 'new_post') {
        $post_type_text = $data['post_type'] === 'bug' ? 'Bug Report' : 'Feature Request';
        $subject = "[Argo Community] New $post_type_text: " . $data['title'];
        $post_url = "$site_url/community/view_post.php?id=" . $data['id'];

        $email_template = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>New Community Post</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Sales Tracker Logo">
                </div>
                
                <div class="content">
                    <h2>New {$post_type_text} Posted</h2>
                    <p>A new {$post_type_text} has been posted on the Argo Community:</p>
                    
                    <p><strong>Title:</strong> {$data['title']}</p>
                    <p><strong>Posted by:</strong> {$data['user_name']} ({$data['user_email']})</p>
                    
                    <div class="button-container">
                        <a href="{$post_url}" class="button">View Post</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>This is an automated notification from the Argo Community system.</p>
                    <p>You received this message because you're an administrator of the Argo Community. 
                    You can adjust your notification settings <a href="$site_url/community/users/admin_notification_settings.php">here</a>.</p>
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
HTML;
    } elseif ($type === 'new_comment') {
        $subject = "[Argo Community] New Comment on: " . $data['post_title'];
        $post_url = "$site_url/community/view_post.php?id=" . $data['post_id'];

        $email_template = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>New Community Comment</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Sales Tracker Logo">
                </div>
                
                <div class="content">
                    <h2>New Comment Posted</h2>
                    <p>A new comment has been posted on "{$data['post_title']}":</p>
                    
                    <p><strong>Posted by:</strong> {$data['user_name']} ({$data['user_email']})</p>
                    
                    <div class="button-container">
                        <a href="{$post_url}" class="button">View Comment</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>This is an automated notification from the Argo Community system.</p>
                    <p>You received this message because you're an administrator of the Argo Community. 
                    You can adjust your notification settings <a href="$site_url/community/users/admin_notification_settings.php">here</a>.</p>
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
HTML;
    } else {
        return false; // Unknown notification type
    }

    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Send emails to all recipients
    $success = true;
    foreach ($recipients as $recipient) {
        $personal_email = str_replace(
            "You're an administrator of the Argo Community.",
            "You're an administrator ({$recipient['username']}) of the Argo Community.",
            $email_template
        );

        $mail_success = mail($recipient['notification_email'], $subject, $personal_email, implode("\r\n", $headers));
        if (!$mail_success) {
            $success = false;
        }
    }

    return $success;
}

/**
 * Send password reset email
 * 
 * @param string $email User's email address
 * @param string $token Reset token
 * @param string $username Username
 * @return bool Success status
 */
function send_password_reset_email($email, $token, $username)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Password Reset - Argo Community';

    // Get the base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . $host;

    $reset_link = $base_url . "/community/users/reset_password.php?token=" . $token;

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Reset Your Password</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo">
                </div>
                
                <div class="content">
                    <h1>Password Reset Request</h1>
                    <p>Hello {$username},</p>
                    <p>We received a request to reset your password for your Argo Community account. To complete the password reset process, please click the button below:</p>
                    
                    <div class="button-container">
                        <a href="{$reset_link}" class="button">Reset Password</a>
                    </div>
                    
                    <p>If the button above doesn't work, you can also copy and paste the following link into your browser:</p>
                    <div class="reset-link">{$reset_link}</div>
                    
                    <p>This password reset link will expire in 24 hours.</p>
                    
                    <p>If you did not request a password reset, you can safely ignore this email - your account is secure.</p>
                    
                    <p>Regards,<br>The Argo Team</p>
                </div>
                
                <div class="footer">
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mail_result = mail($email, $subject, $email_html, implode("\r\n", $headers));
    return $mail_result;
}