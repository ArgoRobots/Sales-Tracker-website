<?php
// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Set default page title if not already set
if (!isset($page_title)) {
    $page_title = 'Admin Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title><?php echo htmlspecialchars($page_title); ?> - Argo Sales Tracker</title>

    <!-- Chart.js for visualizations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    <!-- Notification system -->
    <script src="../resources/notifications/notifications.js" defer></script>

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="admin_header.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">

    <?php
    // Include additional CSS files if specified
    if (isset($additional_css) && is_array($additional_css)) {
        foreach ($additional_css as $css_file) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($css_file) . '">' . "\n    ";
        }
    }
    ?>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-container">
                <div class="logo-section">
                    <img src="../images/argo-logo/A-logo.ico" alt="Argo Logo" class="header-logo">
                    <span class="header-title">Admin Dashboard</span>
                </div>

                <nav class="header-nav">
                    <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                        License Keys
                    </a>
                    <a href="statistics.php" class="nav-link <?php echo $current_page === 'statistics.php' ? 'active' : ''; ?>">
                        Statistics
                    </a>
                    <a href="anonymous_dashboard.php" class="nav-link <?php echo $current_page === 'anonymous_dashboard.php' ? 'active' : ''; ?>">
                        Anonymous Data
                    </a>
                    <a href="users.php" class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                        Users
                    </a>
                    <a href="2fa-setup.php" class="nav-link <?php echo $current_page === '2fa-setup.php' ? 'active' : ''; ?>">
                        2FA Settings
                    </a>
                </nav>

                <div class="header-actions">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="admin-content">
            <?php if (isset($page_title) || isset($page_description)): ?>
                <div class="page-header">
                    <?php if (isset($page_title)): ?>
                        <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                    <?php endif; ?>
                    <?php if (isset($page_description)): ?>
                        <p class="page-description"><?php echo htmlspecialchars($page_description); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
                    <?php
                    echo htmlspecialchars($_SESSION['message']);
                    unset($_SESSION['message']);
                    if (isset($_SESSION['message_type'])) {
                        unset($_SESSION['message_type']);
                    }
                    ?>
                </div>
            <?php endif; ?>