<?php

namespace {
    require_once __DIR__ . '/../../db_connect.php';
    require_once __DIR__ . '/../../email_sender.php';

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
        $stmt = $db->prepare('SELECT id FROM community_users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_exists = $result->fetch_assoc();
        $stmt->close();

        if ($user_exists) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email exists
        $stmt = $db->prepare('SELECT id FROM community_users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $email_exists = $result->fetch_assoc();
        $stmt->close();

        if ($email_exists) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $verification_code = generate_verification_code();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $db->prepare('INSERT INTO community_users (username, email, password_hash, verification_code, email_verified) 
                         VALUES (?, ?, ?, ?, 0)');
        $stmt->bind_param('ssss', $username, $email, $password_hash, $verification_code);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $user_id = $db->insert_id;

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
     * Verify user email
     * 
     * @param string $token Verification token
     * @return bool Success status
     */
    function verify_email($token)
    {
        $db = get_db_connection();

        // Find user by verification token
        $stmt = $db->prepare('SELECT id FROM community_users WHERE verification_token = ?');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Update user as verified
        $stmt = $db->prepare('UPDATE community_users SET email_verified = 1, verification_token = NULL WHERE id = ?');
        $stmt->bind_param('i', $user['id']);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
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
        $stmt = $db->prepare("SELECT * FROM community_users WHERE $field = ?");
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Check if user had scheduled deletion
            $deletion_was_scheduled = !is_null($user['deletion_scheduled_at']);

            // Update last login time and cancel scheduled deletion
            $stmt = $db->prepare('UPDATE community_users SET last_login = NOW(), deletion_scheduled_at = NULL WHERE id = ?');
            $stmt->bind_param('i', $user['id']);
            $stmt->execute();
            $stmt->close();

            // If deletion was scheduled, send cancellation email
            if ($deletion_was_scheduled) {
                $email_sent = send_account_deletion_cancelled_email($user['email'], $user['username']);

                if (!$email_sent) {
                    error_log("Failed to send deletion cancelled email to: " . $user['email']);
                }
            }

            // Don't return sensitive data
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
                $db = get_db_connection();

                // Check if user had scheduled deletion
                $stmt = $db->prepare('SELECT deletion_scheduled_at FROM community_users WHERE id = ?');
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();
                $deletion_was_scheduled = !is_null($user_data['deletion_scheduled_at']);
                $stmt->close();

                // Update last login time and cancel scheduled deletion
                $stmt = $db->prepare('UPDATE community_users SET last_login = NOW(), deletion_scheduled_at = NULL WHERE id = ?');
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $stmt->close();

                // If deletion was scheduled, send cancellation email
                if ($deletion_was_scheduled) {
                    $email_sent = send_account_deletion_cancelled_email($user['email'], $user['username']);

                    if (!$email_sent) {
                        error_log("Failed to send deletion cancelled email to: " . $user['email']);
                    }
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email_verified'] = $user['email_verified'];
                $_SESSION['avatar'] = $user['avatar'];
            }
        }
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

        // Remove any existing tokens for this user
        $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        // Store the new token
        $stmt = $db->prepare('INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $user_id, $token, $expires);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
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
                         WHERE rt.token = ? AND rt.expires_at > NOW()');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

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

        $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

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

        // Use a new database connection for each call
        $stmt = $db->prepare('SELECT id, username, email, bio, avatar, role, email_verified, created_at, last_login 
                        FROM community_users WHERE id = ?');

        if (!$stmt) {
            error_log("Database prepare error in get_user(): " . $db->error);
            return false;
        }

        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ? $user : false;
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
        $stmt = $db->prepare('SELECT id, username FROM community_users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Generate reset token
        $reset_token = md5(uniqid(rand(), true));

        // Set token expiry (24 hours from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Update user with reset token
        $stmt = $db->prepare('UPDATE community_users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?');
        $stmt->bind_param('ssi', $reset_token, $expiry, $user['id']);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            return send_password_reset_email($email, $reset_token, $user['username']);
        }

        return false;
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
        $stmt = $db->prepare('SELECT id FROM community_users WHERE reset_token = ? AND reset_token_expiry > NOW()');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update user with new password and clear reset token
        $stmt = $db->prepare('UPDATE community_users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?');
        $stmt->bind_param('si', $password_hash, $user['id']);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
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

        $stmt = $db->prepare('SELECT role FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user && $user['role'] === 'admin';
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

        // Get current avatar path before updating
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT avatar FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_avatar = $result->fetch_assoc()['avatar'] ?? '';
        $stmt->close();

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
        $target_path = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Set permissions for the file
            chmod($target_path, 0644);

            try {
                // Begin transaction to reduce lock time
                $db->begin_transaction();

                $avatar_path = 'uploads/avatars/' . $filename;
                $stmt = $db->prepare('UPDATE community_users SET avatar = ?, updated_at = NOW() WHERE id = ?');
                $stmt->bind_param('si', $avatar_path, $user_id);
                $success = $stmt->execute();
                $stmt->close();

                if (!$success) {
                    $db->rollback();
                    error_log("Failed to update avatar in database: " . $db->error);
                    return false;
                }

                // Commit transaction
                $db->commit();

                // Update session with avatar path
                $_SESSION['avatar'] = $avatar_path;

                // Delete old avatar file if it exists
                if (!empty($old_avatar)) {
                    $old_file_path = dirname(__DIR__) . '/' . $old_avatar;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                        error_log("Deleted old avatar: " . $old_file_path);
                    }
                }

                error_log("Avatar successfully uploaded: " . $avatar_path);
                return $avatar_path;
            } catch (Exception $e) {
                // Rollback on exception
                $db->rollback();
                error_log("Exception in avatar upload: " . $e->getMessage());
                return false;
            }
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

            $stmt = $db->prepare('SELECT id FROM community_users WHERE id = ?');
            if (!$stmt) {
                error_log('Failed to prepare statement in is_user_logged_in: ' . $db->error);
                return false;
            }

            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
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
     * @param string $redirect_url URL to redirect to after login (optional, defaults to current page)
     */
    function require_login($redirect_url = '')
    {
        if (!is_user_logged_in()) {
            $current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $redirect = !empty($redirect_url) ? $redirect_url : $current_url;

            // Store the intended destination for after login
            $_SESSION['redirect_after_login'] = $redirect;

            // Get the web path to login.php based on where this file is located
            // __DIR__ gives us the filesystem path to community/users/
            // We need to convert this to a web-accessible URL
            $doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
            $login_dir = __DIR__;

            // Get relative path from document root to login.php
            $relative_path = str_replace($doc_root, '', $login_dir);
            $relative_path = str_replace('\\', '/', $relative_path); // Windows compatibility

            header('Location: ' . $relative_path . '/login.php');
            exit;
        }
    }
}

namespace CommunityUsers {
    /**
     * Get the current logged-in user's data
     *
     * @return array|null User data or null if not logged in
     */
    function get_current_user()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $user_id = $_SESSION['user_id'];
        $db = get_db_connection();

        $stmt = $db->prepare('SELECT id, username, email, bio, avatar, role, email_verified, created_at, last_login 
                         FROM community_users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
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

        return $user;
    }
}

namespace {
    /**
     * Generate a 6-digit verification code
     *
     * @return string 6-digit code
     */
    function generate_verification_code()
    {
        return sprintf('%06d', mt_rand(100000, 999999));
    }
}
