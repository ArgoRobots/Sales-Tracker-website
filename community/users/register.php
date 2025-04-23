<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'Username must be between 3 and 20 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must contain at least one special character';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        // Attempt to register user - username will be used as display name
        $result = register_user($username, $email, $password);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Register - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <!-- Font Awesome for password toggle icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h1>Create an Account</h1>
            <p class="auth-subtitle">Join Argo Community to share ideas and connect with other users</p>

            <!-- Added email verification requirement notice -->
            <div class="verification-notice">
                <p><strong>Note:</strong> Email verification is required to activate your account and obtain your license.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <p>Please check your email to verify your account and receive your license.</p>
                    <p><a href="login.php" class="btn btn-blue">Proceed to Login</a></p>
                </div>
            <?php else: ?>
                <form method="post" class="auth-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        <small>Letters, numbers, and underscores only (3-20 characters)</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        <small>A verification email will be sent to this address</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password-field" name="password" required>
                            <div class="toggle-password">
                                <i class="fa fa-eye"></i>
                                <i class="fa fa-eye-slash"></i>
                            </div>
                        </div>

                        <div class="password-policies">
                            <div class="policy-length">
                                At least 8 characters
                            </div>
                            <div class="policy-uppercase">
                                Contains uppercase letter
                            </div>
                            <div class="policy-number">
                                Contains number
                            </div>
                            <div class="policy-special">
                                Contains special character
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue btn-block">Register</button>
                    </div>

                    <div class="auth-links">
                        <p>Already have an account? <a href="login.php">Log in</a></p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Password validation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordField = document.getElementById('password-field');
            const passwordPolicies = document.querySelector('.password-policies');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                togglePassword.classList.toggle('active');
                if (passwordField.getAttribute('type') === 'password') {
                    passwordField.setAttribute('type', 'text');
                } else {
                    passwordField.setAttribute('type', 'password');
                }
            });

            // Show password policies on focus
            passwordField.addEventListener('focus', function() {
                passwordPolicies.classList.add('active');
            });

            // Update policy indicators in real-time
            passwordField.addEventListener('keyup', function() {
                const password = passwordField.value;

                // Check password length
                if (password.length >= 8) {
                    document.querySelector('.policy-length').classList.add('active');
                } else {
                    document.querySelector('.policy-length').classList.remove('active');
                }

                // Check for uppercase letter
                if (/[A-Z]/.test(password)) {
                    document.querySelector('.policy-uppercase').classList.add('active');
                } else {
                    document.querySelector('.policy-uppercase').classList.remove('active');
                }

                // Check for number
                if (/[0-9]/.test(password)) {
                    document.querySelector('.policy-number').classList.add('active');
                } else {
                    document.querySelector('.policy-number').classList.remove('active');
                }

                // Check for special character
                if (/[^A-Za-z0-9]/.test(password)) {
                    document.querySelector('.policy-special').classList.add('active');
                } else {
                    document.querySelector('.policy-special').classList.remove('active');
                }
            });

            // Validate passwords match
            const passwordConfirm = document.getElementById('password_confirm');
            const registerForm = document.querySelector('.auth-form');

            registerForm.addEventListener('submit', function(e) {
                const password = passwordField.value;
                const confirmPassword = passwordConfirm.value;

                // Check if all policies are met
                const allPoliciesMet = document.querySelectorAll('.password-policies > div.active').length === 4;

                if (!allPoliciesMet) {
                    e.preventDefault();
                    alert('Please ensure your password meets all the requirements.');
                    return false;
                }

                // Check if passwords match
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                    return false;
                }
            });
        });
    </script>
</body>

</html>