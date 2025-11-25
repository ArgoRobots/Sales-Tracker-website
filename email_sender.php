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
    $subject = 'Your Argo Books License Key';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Your Argo Books License</title>
            <style>
                 {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>
                
                <div class="content">
                    <h1>Thank You for Your Purchase!</h1>
                    <p>Here is your Argo Books license key:</p>
                    
                    <div class="license-key">{$license_key}</div>
                    
                    <div class="steps">
                        <h2>How to Activate Your License</h2>
                        <ol>
                            <li>Open Argo Books on your computer</li>
                            <li>Click the blue upgrade button on the top right</li>
                            <li>Enter your license key</li>
                            <li>Enjoy unlimited access to all premium features!</li>
                        </ol>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/documentation/index.php" class="button">View Documentation</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php">contact our support team</a>.</p>
                    <p>Thank you for choosing Argo Books!</p>
                </div>
                
                <div class="footer">
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$to_email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mail_result = mail($to_email, $subject, $email_html, implode("\r\n", $headers));
    return $mail_result;
}

/**
 * Resend license key via email using PHP mail
 * 
 * @param string $to_email Recipient email address
 * @param string $license_key The license key to resend
 * @return bool True if successful, false otherwise
 */
function resend_license_email($to_email, $license_key)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Your Requested Argo Books License Key';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Your Argo Books License</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>
                
                <div class="content content-centered">
                    <h1>Your License Key</h1>
                    <p>As requested, here is your Argo Books license key:</p>
                    
                    <div class="license-key">{$license_key}</div>
                    
                    <div class="steps steps-centered">
                        <h2>How to Activate Your License</h2>
                        <ol>
                            <li>Open Argo Books on your computer</li>
                            <li>Click the blue upgrade button on the top right</li>
                            <li>Enter your license key</li>
                            <li>Enjoy unlimited access to all premium features!</li>
                        </ol>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/documentation/index.php" class="button button-resend">View Documentation</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php">contact our support team</a>.</p>
                    <p>Thank you for using Argo Books!</p>
                </div>
                
                <div class="footer">
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$to_email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
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
    $subject = 'Verify Your Account - Argo Books';

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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>
                <div class="content">
                    <h1>Welcome to the Argo Community!</h1>
                    <p>Hello {$username},</p>
                    <p>Thank you for registering. <strong>Email verification is required</strong> to activate your account and access your license key.</p>
                    
                    <p>Please use the following verification code to complete your registration:</p>
                    <div class="verification-code">{$code}</div>
                    
                    <p>This code will expire in 24 hours.</p>
                    
                    <p>If you did not sign up for an account, you can ignore this email.</p>
                    <p>Regards,<br>The Argo Team</p>
                </div>
                <div class="footer">
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
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
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
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
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
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
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send account deletion scheduled email
 * 
 * @param string $email User's email address
 * @param string $username Username
 * @param string $scheduled_date Scheduled deletion date
 * @return bool Success status
 */
function send_account_deletion_scheduled_email($email, $username, $scheduled_date)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Account Deletion Scheduled - Argo Community';

    // Format the scheduled date nicely
    $formatted_date = date('F j, Y \a\t g:i A', strtotime($scheduled_date));

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Account Deletion Scheduled</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>
                
                <div class="content">
                    <h1>Account Deletion Scheduled</h1>
                    <p>Hello {$username},</p>
                    
                    <p>Your Argo Community account has been scheduled for deletion on <strong>{$formatted_date}</strong>.</p>
                    
                    <p><strong>Important Information:</strong></p>
                    <ul>
                            <li>Your account will be permanently deleted in 30 days</li>
                            <li>All your posts, comments, and profile data will be removed</li>
                            <li>This action can be cancelled by logging into your account before the deletion date</li>
                        </ul>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/community/users/login.php" class="button">Cancel Deletion - Login Now</a>
                    </div>
                    
                    <p>If you did not request this deletion, please log into your account immediately to cancel it.</p>
                    
                    <p>If you have any questions, please contact our support team.</p>
                    
                    <p>Best regards,<br>The Argo Team</p>
                
                <div class="footer">
                    <p>This is an automated message from the Argo Community system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send account deletion cancelled email (when user logs in after scheduling)
 *
 * @param string $email User's email address
 * @param string $username Username
 * @return bool Success status
 */
function send_account_deletion_cancelled_email($email, $username)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Account Deletion Cancelled - Argo Community';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Account Deletion Cancelled</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>Account Deletion Cancelled</h1>
                    <p>Hello {$username},</p>

                    <h3>Good News!</h3>
                        <p>Your account deletion has been <strong>cancelled</strong> because you logged into your account.</p>

                    <p>Your Argo Community account is now <strong>active</strong> and will not be deleted. All your:</p>
                    <ul>
                        <li>Profile information</li>
                        <li>Posts and comments</li>
                        <li>Community contributions</li>
                    </ul>
                    <p>remain intact and accessible.</p>

                    <div class="button-container">
                        <a href="https://argorobots.com/community/users/profile.php" class="button">View Your Profile</a>
                    </div>

                    <p>If you decide to delete your account in the future, you can do so from your profile settings.</p>

                    <p>Welcome back!</p>

                    <p>Best regards,<br>The Argo Team</p>
                </div>

                <div class="footer">
                    <p>This is an automated message from the Argo Community system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send ban notification email to banned user
 *
 * @param string $email User's email address
 * @param string $username Username
 * @param string $ban_reason Reason for ban
 * @param string $ban_duration Duration of ban (5_days, 10_days, 30_days, 100_days, 1_year, permanent)
 * @param string|null $expires_at Expiration date for temporary bans
 * @return bool Success status
 */
function send_ban_notification_email($email, $username, $ban_reason, $ban_duration, $expires_at = null)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Community Ban Notification - Argo Books';

    // Format duration text
    $duration_text = '';
    $can_appeal = true;

    switch ($ban_duration) {
        case '5_days':
            $duration_text = '5 days';
            break;
        case '10_days':
            $duration_text = '10 days';
            break;
        case '30_days':
            $duration_text = '30 days';
            break;
        case '100_days':
            $duration_text = '100 days';
            break;
        case '1_year':
            $duration_text = '1 year';
            break;
        case 'permanent':
            $duration_text = 'permanently';
            $can_appeal = true;
            break;
        default:
            $duration_text = 'an unspecified period';
            break;
    }

    // Format expiration date if available
    $expiration_info = '';
    if ($expires_at) {
        $formatted_date = date('F j, Y \a\t g:i A', strtotime($expires_at));
        $expiration_info = "<p>Your ban will expire on <strong>{$formatted_date}</strong>.</p>";
    }

    $appeal_text = $can_appeal ? '<p>If you believe this ban was issued in error, you can <a href="https://argorobots.com/contact-us/index.php">contact our support team</a> to request a review. Please include your username and explain why you believe the ban should be reconsidered.</p>' : '';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Community Ban Notification</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>Community Ban Notification</h1>
                    <p>Hello {$username},</p>
                    
                    <p>Your account has been banned from posting content on the Argo Community for <strong>{$duration_text}</strong>.</p>
                    
                    {$expiration_info}
                    
                    <p><strong>Reason:</strong> {$ban_reason}</p>
                    
                    <p>During this ban period:</p>
                    <ul>
                        <li>You can still use the Argo Books application</li>
                        <li>You can still view posts and comments on the community page</li>
                        <li>You cannot create new posts or comments</li>
                        <li>Repeated violations may result in a permanent ban</li>
                    </ul>
                    
                    {$appeal_text}
                    
                    <p>Please review our <a href="https://argorobots.com/community/guidelines.php">community guidelines</a> to ensure future compliance.</p>
                    
                    <p>Best regards,<br>The Argo Team</p>
                </div>

                <div class="footer">
                    <p>This is an automated message from the Argo Community moderation system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send unban notification email to unbanned user
 *
 * @param string $email User's email address
 * @param string $username Username
 * @return bool Success status
 */
function send_unban_notification_email($email, $username)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Account Unbanned - Argo Community';

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Account Unbanned</title>
            <style>
                {$css}  /* Needs to be embedded for PHP mail() */
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>Account Unbanned</h1>
                    <p>Hello {$username},</p>
                    
                    <p>Your community ban has been lifted. You can now post and comment again on the Argo Community.</p>
                    
                    <p>Please remember to:</p>
                    <ul>
                        <li>Review and follow our <a href="https://argorobots.com/community/guidelines.php">community guidelines</a></li>
                        <li>Be respectful and helpful to other community members</li>
                        <li>Future violations may result in another ban</li>
                    </ul>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/community/" class="button">Visit Community</a>
                    </div>
                    
                    <p>Thank you for being part of the Argo community. We're glad to have you back!</p>
                    
                    <p>Best regards,<br>The Argo Team</p>
                </div>

                <div class="footer">
                    <p>This is an automated message from the Argo Community moderation system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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
/**
 * Send username reset notification email
 *
 * @param string $email User's email address
 * @param string $old_username Original username
 * @param string $new_username New random username
 * @param string $violation_type Type of violation reported
 * @param string $additional_info Additional information from report
 * @return bool Success status
 */
function send_username_reset_email($email, $old_username, $new_username, $violation_type, $additional_info = '')
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Username Reset - Argo Community';

    // Format violation type
    $violation_text = ucfirst(str_replace('_', ' ', $violation_type));

    // Additional info section
    $additional_section = '';
    if (!empty($additional_info)) {
        $additional_section = "
        <p><strong>Additional details:</strong> " . htmlspecialchars($additional_info) . "</p>";
    }

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Username Reset Notification</title>
            <style>
                {$css}
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>Username Reset Notification</h1>
                    <p>Hello,</p>

                    <p>Your username has been changed by our moderation team due to a policy violation.</p>

                    <p><strong>Previous username:</strong> <del>{$old_username}</del></p>
                    <p><strong>New username:</strong> {$new_username}</p>

                    <p><strong>Reason for action:</strong> {$violation_text}</p>

                    {$additional_section}

                    <p><strong>What you can do:</strong></p>
                    <ul>
                        <li>You can change your username to something appropriate by visiting your <a href="https://argorobots.com/community/users/edit_profile.php">profile settings</a></li>
                        <li>Your new username must comply with our community guidelines</li>
                        <li>All your posts and comments have been updated with the new username</li>
                    </ul>

                    <p>If you believe this action was taken in error, please <a href="https://argorobots.com/contact-us/index.php">contact our support team</a> with your account details.</p>

                    <p>Please review our <a href="https://argorobots.com/community/guidelines.php">community guidelines</a> to ensure future compliance.</p>

                    <p>Best regards,<br>The Argo Team</p>
                </div>

                <div class="footer">
                    <p>This is an automated message from the Argo Community moderation system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send bio cleared notification email
 *
 * @param string $email User's email address
 * @param string $username Username
 * @param string $violation_type Type of violation reported
 * @param string $additional_info Additional information from report
 * @return bool Success status
 */
function send_bio_cleared_email($email, $username, $violation_type, $additional_info = '')
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Bio Cleared - Argo Community';

    // Format violation type
    $violation_text = ucfirst(str_replace('_', ' ', $violation_type));

    // Additional info section
    $additional_section = '';
    if (!empty($additional_info)) {
        $additional_section = "
        <p><strong>Additional details:</strong> " . htmlspecialchars($additional_info) . "</p>";
    }

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Bio Cleared Notification</title>
            <style>
                {$css}
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>Bio Cleared Notification</h1>
                    <p>Hello {$username},</p>

                    <p>Your bio has been removed by our moderation team due to a policy violation.</p>

                    <p><strong>Reason for action:</strong> {$violation_text}</p>

                    {$additional_section}

                    <p><strong>What you can do:</strong></p>
                    <ul>
                        <li>You can add a new bio by visiting your <a href="https://argorobots.com/community/users/edit_profile.php">profile settings</a></li>
                        <li>Your new bio must comply with our community guidelines</li>
                        <li>Ensure your bio content is appropriate and respectful</li>
                    </ul>

                    <p>If you believe this action was taken in error, please <a href="https://argorobots.com/contact-us/index.php">contact our support team</a> with your account details.</p>

                    <p>Please review our <a href="https://argorobots.com/community/guidelines.php">community guidelines</a> to ensure future compliance.</p>

                    <p>Best regards,<br>The Argo Team</p>
                </div>

                <div class="footer">
                    <p>This is an automated message from the Argo Community moderation system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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

/**
 * Send new report notification email to admins
 *
 * @param string $email Admin email address
 * @param int $report_id Report ID
 * @param string $content_type Type of content reported
 * @param string $violation_type Type of violation
 * @param string $reporter_username Username of reporter
 * @param string $reported_username Username of reported user (or N/A)
 * @return bool Success status
 */
function send_new_report_notification($email, $report_id, $content_type, $violation_type, $reporter_username, $reported_username = 'N/A')
{
    error_log("send_new_report_notification called for: $email (Report #$report_id, Type: $content_type, Violation: $violation_type)");

    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'New Content Report - Argo Community';

    // Format content type and violation type
    $content_type_text = ucfirst($content_type);
    $violation_text = ucfirst(str_replace('_', ' ', $violation_type));

    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>New Report Notification</title>
            <style>
                {$css}
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
                </div>

                <div class="content">
                    <h1>New Content Report</h1>
                    <p>A new content report has been submitted and requires your attention.</p>

                    <p><strong>Report Details</strong></p>
                    <ul>
                        <li><strong>Report ID:</strong> #{$report_id}</li>
                        <li><strong>Content Type:</strong> {$content_type_text}</li>
                        <li><strong>Violation Type:</strong> {$violation_text}</li>
                        <li><strong>Reported by:</strong> {$reporter_username}</li>
                        <li><strong>Reported user:</strong> {$reported_username}</li>
                    </ul>

                    <p><strong>Action Required</strong></p>
                    <p>Please review this report in the admin panel and take appropriate action.</p>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/admin/reports/" class="button">View Reports</a>
                    </div>

                    <p>This report is currently in <strong>pending</strong> status and awaits your review.</p>
                </div>

                <div class="footer">
                    <p>This is an automated notification from the Argo Community moderation system.</p>
                    <p>Argo Books &copy; 2025. All rights reserved.</p>
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
    error_log("send_new_report_notification mail() result for $email: " . ($mail_result ? "TRUE" : "FALSE"));
    return $mail_result;
}

/**
 * Send AI subscription confirmation/receipt email
 *
 * @param string $email User's email address
 * @param string $subscriptionId Subscription ID
 * @param string $billing Billing cycle (monthly/yearly)
 * @param float $amount Payment amount
 * @param string $endDate Next renewal date
 * @param string $transactionId Transaction ID
 * @param string $paymentMethod Payment method used
 * @return bool Success status
 */
function send_ai_subscription_receipt($email, $subscriptionId, $billing, $amount, $endDate, $transactionId, $paymentMethod)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = "Payment Receipt - Argo AI Subscription";

    $billingText = $billing === 'yearly' ? 'yearly' : 'monthly';
    $renewalDate = date('F j, Y', strtotime($endDate));
    $paymentDate = date('F j, Y');
    $paymentMethodText = ucfirst($paymentMethod);

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Payment Receipt</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>Payment Receipt</h1>
            <p>Thank you for subscribing to Argo AI!</p>

            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0;">Payment Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Date</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{$paymentDate}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Description</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">AI Subscription ({$billingText})</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Amount</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">\${$amount} CAD</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Payment Method</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{$paymentMethodText}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Transaction ID</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 12px; font-family: monospace;">{$transactionId}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Next Renewal</strong></td>
                        <td style="padding: 8px 0; text-align: right;">{$renewalDate}</td>
                    </tr>
                </table>
            </div>

            <h3>What's Included:</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 8px 0; padding-left: 24px; position: relative;">✓ AI-powered receipt scanning</li>
                <li style="padding: 8px 0; padding-left: 24px; position: relative;">✓ Predictive sales analysis</li>
                <li style="padding: 8px 0; padding-left: 24px; position: relative;">✓ AI business insights</li>
                <li style="padding: 8px 0; padding-left: 24px; position: relative;">✓ Natural language AI search</li>
            </ul>

            <p>You can manage your subscription anytime from your <a href="https://argorobots.com/community/users/ai-subscription.php">account settings</a>.</p>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Subscription ID: {$subscriptionId}</p>
                <p>Thank you for using Argo Books!</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}

/**
 * Send AI subscription cancellation confirmation email
 *
 * @param string $email User's email address
 * @param string $subscriptionId Subscription ID
 * @param string $endDate Date when access ends
 * @return bool Success status
 */
function send_ai_subscription_cancelled_email($email, $subscriptionId, $endDate)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = "Subscription Cancelled - Argo AI";

    $accessUntil = date('F j, Y', strtotime($endDate));

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Subscription Cancelled</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>Subscription Cancelled</h1>
            <p>Your Argo AI subscription has been cancelled as requested.</p>

            <div style="background: #fef3c7; border: 1px solid #fcd34d; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Important:</strong> You will continue to have access to AI features until <strong>{$accessUntil}</strong>.</p>
            </div>

            <p>After this date, AI features including receipt scanning, predictive analysis, and AI insights will no longer be available.</p>

            <p>Changed your mind? You can resubscribe anytime from your account settings.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://argorobots.com/upgrade/ai/" style="display: inline-block; background: #8b5cf6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Resubscribe</a>
            </div>

            <p>If you have any questions, please <a href="https://argorobots.com/contact-us/">contact our support team</a>.</p>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Subscription ID: {$subscriptionId}</p>
                <p>Thank you for trying Argo AI!</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}

/**
 * Send AI subscription reactivated email
 * @param string $email User's email address
 * @param string $subscriptionId Subscription ID
 * @param string $endDate Next billing date
 * @param string $billingCycle Monthly or yearly
 * @return bool Success status
 */
function send_ai_subscription_reactivated_email($email, $subscriptionId, $endDate, $billingCycle = 'monthly')
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = "Subscription Reactivated - Argo AI";

    $nextBillingDate = date('F j, Y', strtotime($endDate));
    $billingLabel = ucfirst($billingCycle);

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Subscription Reactivated</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>Welcome Back!</h1>
            <p>Your Argo AI subscription has been reactivated.</p>

            <div style="background: #d1fae5; border: 1px solid #6ee7b7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Your AI features are now active!</strong> You have full access to all AI-powered features.</p>
            </div>

            <p>Here's a summary of your subscription:</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;">Subscription ID</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: bold;">{$subscriptionId}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;">Billing Cycle</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: bold;">{$billingLabel}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;">Next Billing Date</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: bold;">{$nextBillingDate}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; color: #6b7280;">Status</td>
                    <td style="padding: 10px; text-align: right;"><span style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 4px; font-weight: bold;">Active</span></td>
                </tr>
            </table>

            <p>Features now available:</p>
            <ul style="color: #374151; line-height: 1.8;">
                <li>AI-powered receipt scanning</li>
                <li>Predictive sales analysis</li>
                <li>AI business insights</li>
                <li>Natural language search</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://argorobots.com/community/users/ai-subscription.php" style="display: inline-block; background: #8b5cf6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">View Subscription</a>
            </div>

            <p>If you have any questions, please <a href="https://argorobots.com/contact-us/">contact our support team</a>.</p>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Thank you for continuing with Argo AI!</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}

/**
 * Send free credit notification email
 *
 * @param string $email User's email address
 * @param float $creditAmount Amount of credit given
 * @param string $note Optional note from admin
 * @param string $subscriptionId Subscription ID
 * @return bool Success status
 */
function send_free_credit_email($email, $creditAmount, $note = '', $subscriptionId = '')
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = "You've Received Free Credit - Argo AI";

    $formattedAmount = number_format($creditAmount, 2);
    $noteSection = '';
    if (!empty($note)) {
        $noteSection = "
            <div style=\"background: #f0fdf4; border: 1px solid #86efac; padding: 15px; border-radius: 8px; margin: 20px 0;\">
                <p style=\"margin: 0;\"><strong>Note from Argo:</strong> " . htmlspecialchars($note) . "</p>
            </div>";
    }

    $email_html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Free Credit Received</title>
    <style>
        {$css}
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
        </div>

        <div class="content">
            <h1>You've Received Free Credit!</h1>
            <p>Great news! Free credit has been added to your Argo AI subscription.</p>

            <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 30px; border-radius: 12px; margin: 25px 0; text-align: center;">
                <p style="margin: 0 0 10px 0; font-size: 14px; opacity: 0.9;">Credit Added</p>
                <p style="margin: 0; font-size: 42px; font-weight: bold;">\${$formattedAmount} CAD</p>
            </div>

            {$noteSection}

            <p>This credit will be automatically applied to your future subscription renewals, saving you money on upcoming payments.</p>

            <h3>How Credit Works:</h3>
            <ul style="color: #374151; line-height: 1.8;">
                <li>Credit is applied automatically at renewal time</li>
                <li>If your credit covers the full renewal amount, you won't be charged</li>
                <li>Any remaining credit carries over to future renewals</li>
                <li>You can view your credit balance in your subscription settings</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://argorobots.com/community/users/ai-subscription.php" style="display: inline-block; background: #8b5cf6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">View Your Subscription</a>
            </div>

            <p>If you have any questions about your credit or subscription, please <a href="https://argorobots.com/contact-us/">contact our support team</a>.</p>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
                <p>Thank you for being an Argo AI subscriber!</p>
                <p><a href="https://argorobots.com">argorobots.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Books <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $email_html, implode("\r\n", $headers));
}
