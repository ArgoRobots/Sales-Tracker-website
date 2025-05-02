<?php

/**
 * Register a new user with verification code
 * 
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Plain text password
 * @return array Result with success, message, and user_id
 */
function register_user($username, $email, $password)
{
    $db = get_db_connection();

    // Check if username exists
    $stmt = $db->prepare('SELECT id FROM community_users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        return ['success' => false, 'message' => 'Username already exists'];
    }

    // Check if email exists
    $stmt = $db->prepare('SELECT id FROM community_users WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Generate verification code
    $verification_code = generate_verification_code();

    // Generate password hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $db->prepare('INSERT INTO community_users (username, email, password_hash, verification_code, email_verified) 
                         VALUES (:username, :email, :password_hash, :verification_code, 0)');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password_hash', $password_hash, SQLITE3_TEXT);
    $stmt->bindValue(':verification_code', $verification_code, SQLITE3_TEXT);

    if ($stmt->execute()) {
        $user_id = $db->lastInsertRowID();

        // Send verification email with code
        send_verification_email($email, $verification_code, $username);

        return [
            'success' => true,
            'message' => 'Registration successful! Please check your email for the verification code.',
            'user_id' => $user_id
        ];
    }

    return ['success' => false, 'message' => 'Registration failed'];
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
    $subject = 'Verify Your Account - Argo Sales Tracker';

    $message = "
    <html>
    <head>
        <title>Verify Your Account</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
            }
            .container {
                padding: 20px;
                background-color: #f9f9f9;
                border-radius: 5px;
            }
            .header {
                background-color: #1e3a8a;
                color: white;
                padding: 15px;
                text-align: center;
                border-radius: 5px 5px 0 0;
            }
            .content {
                padding: 20px;
                background-color: white;
                border: 1px solid #ddd;
            }
            .verification-code {
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 5px;
                text-align: center;
                color: #1e3a8a;
                margin: 20px 0;
                padding: 10px;
                background-color: #f0f4ff;
                border-radius: 5px;
            }
            .footer {
                margin-top: 20px;
                font-size: 12px;
                color: #666;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to Argo Community!</h2>
            </div>
            <div class='content'>
                <p>Hello $username,</p>
                <p>Thank you for registering. <strong>Email verification is required</strong> to activate your account and access your license key.</p>
                
                <p>Please use the following verification code to complete your registration:</p>
                <div class='verification-code'>$code</div>
                
                <p>This code will expire in 24 hours.</p>
                
                <p>If you did not sign up for an account, you can ignore this email.</p>
                <p>Regards,<br>The Argo Team</p>
            </div>
            <div class='footer'>
                <p>© Argo. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: no-reply@argorobots.com'
    ];

    // Send email
    return mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Verify user email
 * 
 * @param string $token Verification token
 * @return bool Success status
 */
function verify_email($token)
{
    $db = get_db_connection();

    // Find user by verification token
    $stmt = $db->prepare('SELECT id FROM community_users WHERE verification_token = :token');
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$result) {
        return false;
    }

    // Update user as verified
    $stmt = $db->prepare('UPDATE community_users SET email_verified = 1, verification_token = NULL WHERE id = :id');
    $stmt->bindValue(':id', $result['id'], SQLITE3_INTEGER);

    return $stmt->execute() !== false;
}

/**
 * Authenticate user
 * 
 * @param string $login Username or email
 * @param string $password Plain text password
 * @return array|bool User data on success or false on failure
 */
function login_user($login, $password)
{
    $db = get_db_connection();

    // Check if login is email or username
    $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Find user by login
    $stmt = $db->prepare("SELECT * FROM community_users WHERE $field = :login");
    $stmt->bindValue(':login', $login, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$user) {
        return false;
    }

    // Verify password
    if (password_verify($password, $user['password_hash'])) {
        // Update last login time
        $stmt = $db->prepare('UPDATE community_users SET last_login = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
        $stmt->execute();

        // Don't return password hash
        unset($user['password_hash']);
        unset($user['verification_token']);
        unset($user['reset_token']);
        unset($user['reset_token_expiry']);

        // Store avatar in session for the header
        $_SESSION['avatar'] = $user['avatar'];

        return $user;
    }

    return false;
}

/**
 * Check for remember me token and auto-login user
 */
function check_remember_me()
{
    if (isset($_COOKIE['remember_me']) && !isset($_SESSION['user_id'])) {
        $token = $_COOKIE['remember_me'];
        $user = validate_remember_token($token);

        if ($user) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];

            // Refresh the remember me token
            $new_token = generate_remember_token($user['id']);
            if ($new_token) {
                setcookie(
                    'remember_me',
                    $new_token,
                    time() + (30 * 24 * 60 * 60), // 30 days
                    '/'
                );
            }

            return true;
        } else {
            // Invalid token, clear cookie
            setcookie('remember_me', '', time() - 3600, '/');
            return false;
        }
    }

    return false;
}

/**
 * Generate a remember me token for a user
 * 
 * @param int $user_id User ID
 * @return string|bool Token or false on failure
 */
function generate_remember_token($user_id)
{
    $db = get_db_connection();

    // Create a unique token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

    // Check if table exists, create if not
    $db->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
    )");

    // Remove any existing tokens for this user
    $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->execute();

    // Store the new token
    $stmt = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':expires_at', $expires, SQLITE3_TEXT);

    if ($stmt->execute()) {
        return $token;
    }

    return false;
}

/**
 * Validate a remember me token and get the associated user
 * 
 * @param string $token Remember me token
 * @return array|bool User data or false if invalid
 */
function validate_remember_token($token)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT rt.user_id, u.* FROM remember_tokens rt 
                         JOIN community_users u ON rt.user_id = u.id
                         WHERE rt.token = :token AND rt.expires_at > CURRENT_TIMESTAMP');
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $result = $stmt->execute();

    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        // Don't return sensitive data
        unset($user['password_hash']);
        unset($user['verification_token']);
        unset($user['reset_token']);
        unset($user['reset_token_expiry']);

        return $user;
    }

    return false;
}

/**
 * Clear remember me token when logging out
 * 
 * @param int $user_id User ID
 */
function clear_remember_token($user_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->execute();

    // Clear the cookie
    setcookie('remember_me', '', time() - 3600, '/');
}

/**
 * Get user by ID
 * 
 * @param int $user_id User ID
 * @return array|bool User data or false if not found
 */
function get_user($user_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT id, username, email, bio, avatar, role, email_verified, created_at, last_login 
                         FROM community_users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    return $result ? $result : false;
}

/**
 * Request password reset
 * 
 * @param string $email User's email address
 * @return bool Success status
 */
function request_password_reset($email)
{
    $db = get_db_connection();

    // Find user by email
    $stmt = $db->prepare('SELECT id, username FROM community_users WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$user) {
        return false;
    }

    // Generate reset token
    $reset_token = md5(uniqid(rand(), true));

    // Set token expiry (24 hours from now)
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Update user with reset token
    $stmt = $db->prepare('UPDATE community_users SET reset_token = :reset_token, reset_token_expiry = :expiry WHERE id = :id');
    $stmt->bindValue(':reset_token', $reset_token, SQLITE3_TEXT);
    $stmt->bindValue(':expiry', $expiry, SQLITE3_TEXT);
    $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);

    if ($stmt->execute()) {
        // Send password reset email
        return send_password_reset_email($email, $reset_token, $user['username']);
    }

    return false;
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
    $subject = 'Password Reset - Argo Community';

    // Get the base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . $host;

    $reset_link = $base_url . "/community/reset_password.php?token=" . $token;

    $message = "
    <html>
    <head>
        <title>Reset Your Password</title>
    </head>
    <body>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
            <h2>Reset Your Password</h2>
            <p>Hello $username,</p>
            <p>We received a request to reset your password. Click the link below to create a new password:</p>
            <p><a href='$reset_link' style='display: inline-block; background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Reset Password</a></p>
            <p>Or copy and paste this link in your browser:</p>
            <p>$reset_link</p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not request a password reset, you can ignore this email.</p>
            <p>Regards,<br>The Argo Team</p>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: no-reply@argorobots.com'
    ];

    // Send email
    return mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Reset password using token
 * 
 * @param string $token Reset token
 * @param string $new_password New password
 * @return bool Success status
 */
function reset_password($token, $new_password)
{
    $db = get_db_connection();

    // Find user by reset token and check expiry
    $stmt = $db->prepare('SELECT id FROM community_users WHERE reset_token = :token AND reset_token_expiry > CURRENT_TIMESTAMP');
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$user) {
        return false;
    }

    // Hash new password
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update user with new password and clear reset token
    $stmt = $db->prepare('UPDATE community_users SET password_hash = :password_hash, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id');
    $stmt->bindValue(':password_hash', $password_hash, SQLITE3_TEXT);
    $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);

    return $stmt->execute() !== false;
}

/**
 * Check if user is an admin
 * 
 * @param int $user_id User ID
 * @return bool True if user is admin
 */
function is_admin($user_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT role FROM community_users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    return $result && $result['role'] === 'admin';
}

/**
 * Get user profile with post and comment counts
 * 
 * @param int $user_id User ID
 * @return array|bool User profile data or false if not found
 */
function get_user_profile($user_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT * FROM community_user_profiles WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    return $result ? $result : false;
}

/**
 * Upload avatar image
 * 
 * @param int $user_id User ID
 * @param array $file File data from $_FILES
 * @return string|bool Image path on success, false on failure
 */
function upload_avatar($user_id, $file)
{
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return false;
    }

    // Validate image type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Invalid file type: " . $file['type']);
        return false;
    }

    // Validate file size (max 2MB)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        error_log("File too large: " . $file['size'] . " bytes");
        return false;
    }

    // Create base uploads directory first
    $base_dir = dirname(__DIR__) . '/uploads/';
    if (!file_exists($base_dir)) {
        if (!mkdir($base_dir, 0755)) {
            error_log("Failed to create base uploads directory: " . $base_dir);
            return false;
        }
        chmod($base_dir, 0755); // Ensure correct permissions
    }

    // Then create avatars subdirectory
    $upload_dir = $base_dir . 'avatars/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755)) {
            error_log("Failed to create avatars directory: " . $upload_dir);
            return false;
        }
        chmod($upload_dir, 0755); // Ensure correct permissions
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
    $target_path = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Set permissions for the file
        chmod($target_path, 0644);

        // Update user record with new avatar path
        $db = get_db_connection();
        $avatar_path = 'uploads/avatars/' . $filename;
        $stmt = $db->prepare('UPDATE community_users SET avatar = :avatar, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':avatar', $avatar_path, SQLITE3_TEXT);
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
        $stmt->execute();

        // Update session with avatar path
        $_SESSION['avatar'] = $avatar_path;

        error_log("Avatar successfully uploaded: " . $avatar_path);
        return $avatar_path;
    }

    error_log("Failed to move uploaded file to: " . $target_path);
    return false;
}

/**
 * Check if user is logged in and exists in database
 * 
 * @return bool True if user is logged in and exists in database
 */
function is_user_logged_in()
{
    // First check session variables
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        error_log('Session user_id is not set or empty');
        return false;
    }

    // Then verify user exists in database
    try {
        $db = get_db_connection();
        if (!$db) {
            error_log('Database connection failed in is_user_logged_in');
            return false;
        }

        $stmt = $db->prepare('SELECT id FROM community_users WHERE id = :id');
        if (!$stmt) {
            error_log('Failed to prepare statement in is_user_logged_in: ' . $db->lastErrorMsg());
            return false;
        }

        $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $result = $stmt->execute();

        if (!$result) {
            error_log('Failed to execute statement in is_user_logged_in: ' . $db->lastErrorMsg());
            return false;
        }

        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user === false) {
            error_log('User with ID ' . $_SESSION['user_id'] . ' not found in database');
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log('Exception in is_user_logged_in: ' . $e->getMessage());
        return false;
    }
}

/**
 * Require user to be logged in, redirect to login if not
 * 
 * @param string $redirect_url URL to redirect to after login
 * @param bool $force_redirect If true, will always redirect non-logged users, otherwise allows read-only access
 */
function require_login($redirect_url = '', $force_redirect = false)
{
    // For pages that require login for viewing (force_redirect=true), redirect to login
    if ($force_redirect && !is_user_logged_in()) {
        $current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $redirect = !empty($redirect_url) ? $redirect_url : $current_url;

        // Store the intended destination for after login
        $_SESSION['redirect_after_login'] = $redirect;

        // Redirect to login page
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current logged in user data
 * 
 * @return array|null User data or null if not logged in
 */
function get_current_user_ID()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $user_id = $_SESSION['user_id'];
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT id, username, email, bio, avatar, role, email_verified, created_at, last_login 
                         FROM community_users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$result) {
        // User ID in session but not found in database
        // Return basic info from session
        return [
            'id' => $user_id,
            'username' => $_SESSION['username'] ?? 'Unknown',
            'email' => $_SESSION['email'] ?? '',
            'email_verified' => $_SESSION['email_verified'] ?? 0,
            'role' => $_SESSION['role'] ?? 'user',
            'avatar' => ''
        ];
    }

    return $result;
}

/**
 * Generate a 6-digit verification code
 * 
 * @return string 6-digit code
 */
function generate_verification_code()
{
    return sprintf('%06d', mt_rand(100000, 999999));
}


/**
 * Resend verification code
 * 
 * @param int $user_id User ID
 * @return bool Success status
 */
function resend_verification_code($user_id)
{
    $db = get_db_connection();

    // Get user data
    $stmt = $db->prepare('SELECT email, username FROM community_users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$user) {
        return false;
    }

    // Generate new verification code
    $verification_code = generate_verification_code();

    // Update user with new verification code
    $stmt = $db->prepare('UPDATE community_users SET verification_code = :code WHERE id = :id');
    $stmt->bindValue(':code', $verification_code, SQLITE3_TEXT);
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        // Send verification email
        return send_verification_email($user['email'], $verification_code, $user['username']);
    }

    return false;
}
