<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';
require_once '../../email_sender.php';

// Ensure user is logged in
require_login('', true);

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

$errors = [];
$success_messages = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_profile':
            handle_profile_update();
            break;
        case 'change_avatar':
            handle_avatar_change();
            break;
        case 'change_email':
            handle_email_change();
            break;
        case 'change_password':
            handle_password_change();
            break;
        case 'verify_email':
            handle_email_verification();
            break;
        case 'remove_avatar':
            handle_avatar_removal();
            break;
    }
}

// Function to handle profile updates (username and bio)
function handle_profile_update()
{
    global $errors, $success_messages, $user_id, $user;

    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required';
        return;
    }

    if (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Username must be between 3 and 30 characters';
        return;
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, underscores, and hyphens';
        return;
    }

    // Validate bio length
    if (strlen($bio) > 500) {
        $errors[] = 'Bio must be 500 characters or less';
        return;
    }

    $db = get_db_connection();

    // Check if username is taken by someone else
    if ($username !== $user['username']) {
        $stmt = $db->prepare('SELECT id FROM community_users WHERE username = ? AND id != ?');
        $stmt->bind_param('si', $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $stmt->close();
            $errors[] = 'Username is already taken';
            return;
        }
        $stmt->close();
    }

    // Update user profile
    $stmt = $db->prepare('UPDATE community_users SET username = ?, bio = ? WHERE id = ?');
    $stmt->bind_param('ssi', $username, $bio, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Update username across all posts and comments if changed
        if ($username !== $user['username']) {
            $stmt = $db->prepare('UPDATE community_posts SET user_name = ? WHERE user_id = ?');
            $stmt->bind_param('si', $username, $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare('UPDATE community_comments SET user_name = ? WHERE user_id = ?');
            $stmt->bind_param('si', $username, $user_id);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['username'] = $username;
        }

        // Refresh user data
        $user = get_user($user_id);
        $success_messages[] = 'Profile updated successfully!';
    } else {
        $stmt->close();
        $errors[] = 'Failed to update profile. Please try again.';
    }
}

// Function to handle avatar changes
function handle_avatar_change()
{
    global $errors, $success_messages, $user_id, $user;

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please select an image to upload';
        return;
    }

    $file = $_FILES['avatar'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed. Please try again.';
        return;
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
        return;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'File is too large. Maximum size is 5MB.';
        return;
    }

    // Create avatars directory if it doesn't exist
    $avatar_dir = '../../images/avatars';
    if (!is_dir($avatar_dir)) {
        mkdir($avatar_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $avatar_dir . '/' . $filename;

    // Delete old avatar if it exists
    if (!empty($user['avatar']) && file_exists('../../' . $user['avatar'])) {
        unlink('../../' . $user['avatar']);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database with relative path
        $relative_path = 'images/avatars/' . $filename;

        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE community_users SET avatar = ? WHERE id = ?');
        $stmt->bind_param('si', $relative_path, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            $user = get_user($user_id); // Refresh user data
            $success_messages[] = 'Avatar updated successfully!';
        } else {
            $stmt->close();
            // Clean up uploaded file on database error
            unlink($filepath);
            $errors[] = 'Failed to update avatar in database.';
        }
    } else {
        $errors[] = 'Failed to upload avatar. Please try again.';
    }
}

// Function to handle avatar removal
function handle_avatar_removal()
{
    global $errors, $success_messages, $user_id, $user;

    if (!empty($user['avatar'])) {
        // Delete file if it exists
        if (file_exists('../../' . $user['avatar'])) {
            unlink('../../' . $user['avatar']);
        }

        // Update database
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE community_users SET avatar = NULL WHERE id = ?');
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            $user = get_user($user_id); // Refresh user data
            $success_messages[] = 'Avatar removed successfully!';
        } else {
            $stmt->close();
            $errors[] = 'Failed to remove avatar.';
        }
    } else {
        $errors[] = 'No avatar to remove.';
    }
}

// Function to handle email changes
function handle_email_change()
{
    global $errors, $success_messages, $user_id, $user;

    $new_email = trim($_POST['new_email'] ?? '');
    $password = $_POST['email_password'] ?? '';

    if (empty($new_email) || empty($password)) {
        $errors[] = 'Email and password are required';
        return;
    }

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
        return;
    }

    if ($new_email === $user['email']) {
        $errors[] = 'This is already your current email address';
        return;
    }

    $db = get_db_connection();

    // Verify current password
    $stmt = $db->prepare('SELECT password_hash FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $password_data = $result->fetch_assoc();
    $stmt->close();

    if (!$password_data || !password_verify($password, $password_data['password_hash'])) {
        $errors[] = 'Current password is incorrect';
        return;
    }

    // Check if new email is already used
    $stmt = $db->prepare('SELECT id FROM community_users WHERE email = ? AND id != ?');
    $stmt->bind_param('si', $new_email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        $stmt->close();
        $errors[] = 'This email address is already registered';
        return;
    }
    $stmt->close();

    // Generate verification code
    $verification_code = generate_verification_code();

    // Store pending email change
    $stmt = $db->prepare('UPDATE community_users SET verification_code = ?, email_verified = 0 WHERE id = ?');
    $stmt->bind_param('si', $verification_code, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Send verification email to NEW email address
        $email_sent = send_verification_email($new_email, $verification_code, $user['username']);

        if ($email_sent) {
            // Store the new email temporarily in session for verification
            $_SESSION['pending_email'] = $new_email;
            $_SESSION['email_change_pending'] = true;
            $success_messages[] = 'Verification email sent to ' . htmlspecialchars($new_email) . '. Please check your email and enter the verification code below.';
        } else {
            $errors[] = 'Failed to send verification email. Please try again.';
        }
    } else {
        $stmt->close();
        $errors[] = 'Failed to initiate email change. Please try again.';
    }
}

// Function to handle email verification for email changes
function handle_email_verification()
{
    global $errors, $success_messages, $user_id, $user;

    if (!isset($_SESSION['email_change_pending']) || !isset($_SESSION['pending_email'])) {
        $errors[] = 'No email change pending';
        return;
    }

    $verification_code = trim($_POST['email_verification_code'] ?? '');

    if (empty($verification_code)) {
        $errors[] = 'Verification code is required';
        return;
    }

    $db = get_db_connection();

    // Check verification code
    $stmt = $db->prepare('SELECT verification_code FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $db_data = $result->fetch_assoc();
    $stmt->close();

    if (!$db_data || $db_data['verification_code'] !== $verification_code) {
        $errors[] = 'Invalid verification code';
        return;
    }

    // Update email and verify
    $new_email = $_SESSION['pending_email'];
    $stmt = $db->prepare('UPDATE community_users SET email = ?, email_verified = 1, verification_code = NULL WHERE id = ?');
    $stmt->bind_param('si', $new_email, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Update email in posts and comments
        $stmt = $db->prepare('UPDATE community_posts SET user_email = ? WHERE user_id = ?');
        $stmt->bind_param('si', $new_email, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('UPDATE community_comments SET user_email = ? WHERE user_id = ?');
        $stmt->bind_param('si', $new_email, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update session
        $_SESSION['email'] = $new_email;
        unset($_SESSION['pending_email']);
        unset($_SESSION['email_change_pending']);

        $user = get_user($user_id); // Refresh user data
        $success_messages[] = 'Email address updated successfully!';
    } else {
        $stmt->close();
        $errors[] = 'Failed to update email address.';
    }
}

// Function to handle password changes
function handle_password_change()
{
    global $errors, $success_messages, $user_id;

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = 'All password fields are required';
        return;
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
        return;
    }

    if (strlen($new_password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
        return;
    }

    $db = get_db_connection();

    // Verify current password
    $stmt = $db->prepare('SELECT password_hash FROM community_users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $password_data = $result->fetch_assoc();
    $stmt->close();

    if (!$password_data || !password_verify($current_password, $password_data['password_hash'])) {
        $errors[] = 'Current password is incorrect';
        return;
    }

    // Update password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('UPDATE community_users SET password_hash = ? WHERE id = ?');
    $stmt->bind_param('si', $new_password_hash, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        $success_messages[] = 'Password changed successfully!';
    } else {
        $stmt->close();
        $errors[] = 'Failed to change password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - Argo Community</title>
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="edit-profile.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="../../resources/notifications/notifications.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="title-container">
        <h1>Edit Account</h1>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_messages)): ?>
        <div class="success-message">
            <?php foreach ($success_messages as $message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="edit-sections">
        <!-- Avatar Section -->
        <div class="edit-section">
            <h2>Profile Picture</h2>
            <div class="avatar-section">
                <div class="current-avatar">
                    <?php if (!empty($user['avatar']) && file_exists('../../' . $user['avatar'])): ?>
                        <img src="../../<?php echo htmlspecialchars($user['avatar']); ?>" alt="Current Avatar" class="avatar-preview" id="avatarPreview">
                    <?php else: ?>
                        <div class="avatar-preview" id="avatarPreview">👤</div>
                    <?php endif; ?>
                </div>
                <div class="avatar-controls">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="change_avatar">
                        <div class="file-input-wrapper">
                            <input type="file" name="avatar" id="avatarFile" accept="image/*" onchange="previewAvatar(this)">
                            <label for="avatarFile" class="file-input-label">Choose New Avatar</label>
                        </div>
                        <div class="selected-file" id="selectedFile" style="display: none;"></div>
                        <p class="info-text">Upload a profile picture (JPEG, PNG, GIF, or WebP). Maximum size: 5MB.</p>
                        <div class="form-actions" style="margin-top: 15px; padding-top: 15px; justify-content: flex-start;">
                            <button type="submit" class="btn btn-blue">Update Avatar</button>
                        </div>
                    </form>

                    <?php if (!empty($user['avatar'])): ?>
                        <form method="post" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="remove_avatar">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove your avatar?')">Remove Avatar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="edit-section">
            <h2>Profile Information</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    <p class="info-text">Your username will be displayed on all your posts and comments. Only letters, numbers, underscores, and hyphens allowed.</p>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" placeholder="Tell us about yourself..." oninput="updateCharCount(this)"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <div class="char-counter">
                        <span id="bioCharCount"><?php echo strlen($user['bio'] ?? ''); ?></span>/500 characters
                    </div>
                    <p class="info-text">Write a short bio that will be displayed on your profile page.</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-blue">Update Profile</button>
                    <a href="profile.php?username=<?php echo urlencode($user['username']); ?>" class="btn btn-gray">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Email Section -->
        <div class="edit-section">
            <h2>Email Address</h2>
            <p><strong>Current Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                <?php if ($user['email_verified']): ?>
                    <span style="color: #16a34a; font-weight: 600;">✓ Verified</span>
                <?php else: ?>
                    <span style="color: #dc2626; font-weight: 600;">⚠ Not Verified</span>
                <?php endif; ?>
            </p>

            <?php if (isset($_SESSION['email_change_pending']) && $_SESSION['email_change_pending']): ?>
                <div class="verification-pending">
                    <h4>Email Change Pending</h4>
                    <p>We've sent a verification code to <strong><?php echo htmlspecialchars($_SESSION['pending_email']); ?></strong></p>
                    <form method="post">
                        <input type="hidden" name="action" value="verify_email">
                        <div class="form-group">
                            <label for="email_verification_code">Verification Code</label>
                            <input type="text" id="email_verification_code" name="email_verification_code" class="verification-code-input" placeholder="Enter 6-digit code" maxlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-blue">Verify Email</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="action" value="change_email">

                    <div class="form-group">
                        <label for="new_email">New Email Address</label>
                        <input type="email" id="new_email" name="new_email" required>
                        <p class="info-text">You'll need to verify your new email address before the change takes effect.</p>
                    </div>

                    <div class="form-group">
                        <label for="email_password">Current Password</label>
                        <input type="password" id="email_password" name="email_password" required>
                        <p class="info-text">Enter your current password to confirm this change.</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue">Change Email</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Password Section -->
        <div class="edit-section">
            <h2>Change Password</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required oninput="checkPasswordStrength(this.value)">
                    <div id="passwordStrength" class="password-strength"></div>
                    <p class="info-text">Password must be at least 8 characters long.</p>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required oninput="checkPasswordMatch()">
                    <div id="passwordMatch" class="password-strength"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-blue">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Avatar preview functionality
        function previewAvatar(input) {
            const file = input.files[0];
            const preview = document.getElementById('avatarPreview');
            const selectedFile = document.getElementById('selectedFile');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                };
                reader.readAsDataURL(file);

                selectedFile.textContent = `Selected: ${file.name}`;
                selectedFile.style.display = 'block';
            } else {
                selectedFile.style.display = 'none';
            }
        }

        // Bio character counter
        function updateCharCount(textarea) {
            const count = textarea.value.length;
            const counter = document.getElementById('bioCharCount');
            counter.textContent = count;

            if (count > 500) {
                counter.style.color = '#dc2626';
            } else if (count > 400) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = '#6b7280';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let feedback = '';

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    feedback = 'Very weak password';
                    strengthDiv.className = 'password-strength strength-weak';
                    break;
                case 2:
                    feedback = 'Weak password';
                    strengthDiv.className = 'password-strength strength-weak';
                    break;
                case 3:
                    feedback = 'Medium password';
                    strengthDiv.className = 'password-strength strength-medium';
                    break;
                case 4:
                    feedback = 'Strong password';
                    strengthDiv.className = 'password-strength strength-strong';
                    break;
                case 5:
                    feedback = 'Very strong password';
                    strengthDiv.className = 'password-strength strength-strong';
                    break;
            }

            strengthDiv.textContent = feedback;
        }

        // Password match checker
        function checkPasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirmPassword === '') {
                matchDiv.textContent = '';
                return;
            }

            if (newPassword === confirmPassword) {
                matchDiv.textContent = 'Passwords match';
                matchDiv.className = 'password-strength strength-strong';
            } else {
                matchDiv.textContent = 'Passwords do not match';
                matchDiv.className = 'password-strength strength-weak';
            }
        }
    </script>
</body>

</html>