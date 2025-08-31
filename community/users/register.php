<?php
session_start();
require_once '../../db_connect.php';
require_once 'user_functions.php';

// Redirect if already logged in
if (is_user_logged_in()) {
    header('Location: profile.php');
    exit;
}

// Check for remember me cookie and auto-login user if valid
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    check_remember_me();
}

$error = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    $terms_agreed = isset($_POST['terms']);

    // Define restricted terms for usernames
    $restricted_terms = ['argo', 'admin', 'moderator', 'mod', 'staff', 'support', 'system'];

    // Check if username contains any restricted terms
    $contains_restricted = false;
    foreach ($restricted_terms as $term) {
        if (stripos($username, $term) !== false) {
            $contains_restricted = true;
            $error = 'Username cannot contain "' . $term . '"';
            break;
        }
    }

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'Username must be between 3 and 20 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores';
    } elseif ($contains_restricted) {
        // Error message already set
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
    } elseif (!$terms_agreed) {
        $error = 'You must agree to the terms and conditions';
    } else {
        // Attempt to register user
        $result = register_user($username, $email, $password);

        if ($result['success']) {
            // Store user_id temporarily for verification
            $_SESSION['temp_user_id'] = $result['user_id'];

            // Redirect to verification page
            header('Location: verify_code.php');
            exit;
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
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Register - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <!-- Font Awesome for password toggle icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/checkbox.css">
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

            <?php if ($error): ?>
                <div class="error-message" id="server-error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-group" id="username-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    <small>Letters, numbers, and underscores only (3-20 characters)</small>
                    <div class="validation-feedback" id="username-feedback"></div>
                </div>

                <div class="form-group" id="email-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    <small>A verification code will be sent to this address</small>
                    <div class="validation-feedback" id="email-feedback"></div>
                </div>

                <div class="form-group" id="password-group">
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

                <div class="form-group" id="confirm-password-group">
                    <label for="password_confirm">Confirm Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password_confirm" name="password_confirm" required>
                        <div class="toggle-password">
                            <i class="fa fa-eye"></i>
                            <i class="fa fa-eye-slash"></i>
                        </div>
                    </div>
                    <div class="validation-feedback" id="confirm-password-feedback"></div>
                </div>

                <div class="checkbox">
                    <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                    <label for="terms">I agree to the <a href="../../legal/terms.php" target="_blank">terms and conditions</a></label>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-button" class="btn btn-blue btn-block">Register</button>
                </div>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Log in</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form elements
            const usernameField = document.getElementById('username');
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password-field');
            const passwordConfirm = document.getElementById('password_confirm');
            const submitButton = document.getElementById('submit-button');
            const registerForm = document.querySelector('.auth-form');
            const serverErrorMessage = document.getElementById('server-error-message');

            // Validation feedback elements
            const usernameGroup = document.getElementById('username-group');
            const emailGroup = document.getElementById('email-group');
            const passwordGroup = document.getElementById('password-group');
            const confirmPasswordGroup = document.getElementById('confirm-password-group');
            const usernameFeedback = document.getElementById('username-feedback');
            const emailFeedback = document.getElementById('email-feedback');
            const confirmPasswordFeedback = document.getElementById('confirm-password-feedback');
            const termsGroup = document.getElementById('terms-group');
            const termsFeedback = document.getElementById('terms-feedback');
            const passwordPolicies = document.querySelector('.password-policies');

            // Toggle password visibility for password field
            const togglePassword = document.querySelectorAll('.toggle-password')[0];
            togglePassword.addEventListener('click', function() {
                togglePassword.classList.toggle('active');
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
            });

            // Toggle password visibility for confirm password field
            const confirmTogglePassword = document.querySelectorAll('.toggle-password')[1];
            confirmTogglePassword.addEventListener('click', function() {
                confirmTogglePassword.classList.toggle('active');
                const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirm.setAttribute('type', type);
            });

            // If there was a form error, restore password values
            if (<?php echo !empty($error) ? 'true' : 'false'; ?>) {
                passwordField.value = '<?php echo isset($_POST['password']) ? addslashes($_POST['password']) : ''; ?>';
                passwordConfirm.value = '<?php echo isset($_POST['password_confirm']) ? addslashes($_POST['password_confirm']) : ''; ?>';

                // Update password policy indicators
                const event = new Event('keyup');
                passwordField.dispatchEvent(event);
            }

            // Show password policies on focus
            passwordField.addEventListener('focus', function() {
                passwordPolicies.classList.add('active');
            });

            // Username validation
            function validateUsername() {
                const username = usernameField.value.trim();
                let usernameError = "";

                if (username === "") {
                    usernameFeedback.textContent = "";
                    usernameGroup.classList.remove('invalid', 'valid');
                    return false;
                }

                // Check username length
                if (username.length < 3) {
                    usernameError = "Username must be at least 3 characters";
                } else if (username.length > 20) {
                    usernameError = "Username cannot exceed 20 characters";
                }
                // Check username format
                else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    usernameError = "Only letters, numbers, and underscores";
                }
                // Check for restricted terms
                else {
                    const restrictedTerms = ['argo', 'admin', 'moderator', 'mod', 'staff', 'support', 'system'];
                    for (const term of restrictedTerms) {
                        if (username.toLowerCase().includes(term.toLowerCase())) {
                            usernameError = `Cannot contain "${term}"`;
                            break;
                        }
                    }
                }

                // Update username validation feedback
                if (usernameError) {
                    usernameFeedback.textContent = usernameError;
                    usernameGroup.classList.add('invalid');
                    usernameGroup.classList.remove('valid');
                    return false;
                } else {
                    usernameFeedback.textContent = "";
                    usernameGroup.classList.remove('invalid');
                    usernameGroup.classList.add('valid');
                    return true;
                }
            }

            // Email validation
            function validateEmail() {
                const email = emailField.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (email === "") {
                    emailFeedback.textContent = "";
                    emailGroup.classList.remove('invalid', 'valid');
                    return false;
                }

                if (!emailRegex.test(email)) {
                    emailFeedback.textContent = "Please enter a valid email";
                    emailGroup.classList.add('invalid');
                    emailGroup.classList.remove('valid');
                    return false;
                } else {
                    emailFeedback.textContent = "";
                    emailGroup.classList.remove('invalid');
                    emailGroup.classList.add('valid');
                    return true;
                }
            }

            // Password policies validation
            function validatePasswordPolicy() {
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

                // Return true if all policies are met
                return document.querySelectorAll('.password-policies > div.active').length === 4;
            }

            // Password confirmation validation
            function validateConfirmPassword() {
                const password = passwordField.value;
                const confirmPassword = passwordConfirm.value;

                if (confirmPassword === "") {
                    confirmPasswordFeedback.textContent = "";
                    confirmPasswordGroup.classList.remove('invalid', 'valid');
                    return false;
                }

                if (password !== confirmPassword) {
                    confirmPasswordFeedback.textContent = "Passwords do not match";
                    confirmPasswordGroup.classList.add('invalid');
                    confirmPasswordGroup.classList.remove('valid');
                    return false;
                } else {
                    confirmPasswordFeedback.textContent = "";
                    confirmPasswordGroup.classList.remove('invalid');
                    confirmPasswordGroup.classList.add('valid');
                    return true;
                }
            }

            // Terms checkbox validation
            function validateTerms() {
                if (termsCheckbox.checked) {
                    termsFeedback.textContent = "";
                    termsGroup.classList.remove('invalid');
                    termsGroup.classList.add('valid');
                    return true;
                } else {
                    termsFeedback.textContent = "You must agree to the terms";
                    termsGroup.classList.add('invalid');
                    termsGroup.classList.remove('valid');
                    return false;
                }
            }

            // Form validation
            function validateForm() {
                const usernameValid = validateUsername();
                const emailValid = validateEmail();
                const passwordPoliciesValid = validatePasswordPolicy();
                const confirmPasswordValid = validateConfirmPassword();
                const termsValid = validateTerms();

                // Enable or disable submit button
                if (usernameValid && emailValid && passwordPoliciesValid && confirmPasswordValid && termsValid) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('disabled');
                } else {
                    submitButton.disabled = true;
                    submitButton.classList.add('disabled');
                }
            }

            // Add input event listeners
            usernameField.addEventListener('input', function() {
                validateUsername();
                validateForm();

                // Clear server error when username changes
                if (serverErrorMessage) {
                    serverErrorMessage.style.display = 'none';
                }
            });

            emailField.addEventListener('input', function() {
                validateEmail();
                validateForm();

                // Clear server error when email changes
                if (serverErrorMessage) {
                    serverErrorMessage.style.display = 'none';
                }
            });

            passwordField.addEventListener('input', function() {
                validatePasswordPolicy();
                validateConfirmPassword();
                validateForm();
            });

            passwordField.addEventListener('keyup', validatePasswordPolicy);

            passwordConfirm.addEventListener('input', function() {
                validateConfirmPassword();
                validateForm();
            });

            termsCheckbox.addEventListener('change', function() {
                validateTerms();
                validateForm();
            });

            // Form submission
            registerForm.addEventListener('submit', function(e) {
                // Revalidate everything before submit
                validateForm();

                if (submitButton.disabled) {
                    e.preventDefault();
                }
            });

            // Initial validation
            validateForm();
        });
    </script>
</body>

</html>