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
$verification_notice = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Basic validation
    if (empty($login) || empty($password)) {
        $error = 'Please enter both username/email and password';
    } else {
        // Attempt to log in
        $user = login_user($login, $password);

        if ($user) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];

            // Handle "Stay logged in" option
            if ($remember_me) {
                // Set session cookie to last 30 days instead of browser close
                $cookie_params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    session_id(),
                    time() + (30 * 24 * 60 * 60), // 30 days
                    $cookie_params['path'],
                    $cookie_params['domain'],
                    $cookie_params['secure'],
                    $cookie_params['httponly']
                );

                // Store remember me token in database and set cookie
                $token = generate_remember_token($user['id']);
                if ($token) {
                    setcookie(
                        'remember_me',
                        $token,
                        time() + (30 * 24 * 60 * 60), // 30 days
                        '/'
                    );
                }
            }

            // Redirect after login
            if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            } else {
                header('Location: profile.php');
            }
            exit;
        } else {
            $error = 'Invalid username/email or password';
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
    <title>Log In - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <!-- Font Awesome for password toggle icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="auth.css">
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
            <h1>Log In</h1>
            <p class="auth-subtitle">Welcome back! Log in to your account</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($verification_notice): ?>
                <div class="verification-notice">
                    <?php echo $verification_notice; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="login">Username or Email</label>
                    <input type="text" id="login" name="login" value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" name="password" required>
                        <div class="toggle-password">
                            <i class="fa fa-eye"></i>
                            <i class="fa fa-eye-slash"></i>
                        </div>
                    </div>
                </div>

                <div class="checkbox">
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Stay logged in for 30 days</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-blue btn-block">Log In</button>
                </div>

                <div class="auth-links">
                    <a href="forgot_password.php">Forgot password?</a>
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get password field and toggle button
            const passwordField = document.getElementById('password');
            const togglePassword = document.querySelector('.toggle-password');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                togglePassword.classList.toggle('active');
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
            });
        });
    </script>
</body>

</html>