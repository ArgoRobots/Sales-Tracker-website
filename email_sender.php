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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
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
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php">contact our support team</a>.</p>
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
 * Resend license key via email using PHP mail
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
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Logo" width="140">
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
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php">contact our support team</a>.</p>
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

/**
 * Send ban notification email to banned user
 *
 * @param string $email User's email address
 * @param string $username Username
 * @param string $ban_reason Reason for ban
 * @param string $ban_duration Duration of ban (30_days, 1_year, permanent)
 * @param string|null $expires_at Expiration date for temporary bans
 * @return bool Success status
 */
function send_ban_notification_email($email, $username, $ban_reason, $ban_duration, $expires_at = null)
{
    $css = file_get_contents(__DIR__ . '/email.css');
    $subject = 'Community Ban Notification - Argo Sales Tracker';

    // Format duration text
    $duration_text = '';
    $can_appeal = true;

    switch ($ban_duration) {
        case '30_days':
            $duration_text = '30 days';
            break;
        case '1_year':
            $duration_text = '1 year';
            break;
        case 'permanent':
            $duration_text = 'permanently';
            $can_appeal = true;
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
                        <li>You can still use the Argo Sales Tracker application</li>
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
    error_log("send_new_report_notification mail() result for $email: " . ($mail_result ? "TRUE" : "FALSE"));
    return $mail_result;
}
