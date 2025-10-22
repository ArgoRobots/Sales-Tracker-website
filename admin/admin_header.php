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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="unified-admin.css">
    <link rel="stylesheet" href="../resources/styles/link.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">
</head>

<body>
    <div class="admin-wrapper">
        <!-- Admin Header -->
        <header class="admin-header">
            <!-- BURGER MENU -->
            <input class="menu-btn" type="checkbox" id="menu-btn" onclick="headerToggleMenu()">
            <label class="menu-icon" id="menu-icon" for="menu-btn"><span class="nav-icon"></span></label>

            <div class="header-container">
                <a href="../index.php" class="btn-small btn-home" title="Go to Main Site">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    <span class="home-text">Home</span>
                </a>

                <div class="logo-section">
                    <img src="../images/argo-logo/A-logo.ico" alt="Argo Logo" class="header-logo">
                    <span class="header-title">Admin Dashboard</span>
                </div>

                <!-- Desktop Navigation -->
                <nav class="header-nav desktop-nav">
                    <a href="index.php" class="header-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                        License Keys
                    </a>
                    <a href="statistics.php" class="header-link <?php echo $current_page === 'statistics.php' ? 'active' : ''; ?>">
                        Statistics
                    </a>
                    <a href="anonymous_dashboard.php" class="header-link <?php echo $current_page === 'anonymous_dashboard.php' ? 'active' : ''; ?>">
                        Anonymous Data
                    </a>
                    <a href="users.php" class="header-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                        Users
                    </a>
                    <a href="2fa-setup.php" class="header-link <?php echo $current_page === '2fa-setup.php' ? 'active' : ''; ?>">
                        2FA Settings
                    </a>
                    <a href="referrals.php" class="header-link <?php echo $current_page === 'referrals.php' ? 'active' : ''; ?>">
    Referrals
</a>
                </nav>

                <!-- Desktop Actions -->
                <div class="header-actions desktop-actions">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="btn btn-small btn-red">Logout</a>
                </div>

                <!-- Mobile User Name (right side) -->
                <div class="mobile-user">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div id="menu" class="hamburger-nav-menu">
                <ul>
                    <li>
                        <a href="../index.php" class="home-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                            </svg>
                            Home
                        </a>
                    </li>
                    <li><a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">License Keys</a></li>
                    <li><a href="statistics.php" class="<?php echo $current_page === 'statistics.php' ? 'active' : ''; ?>">Statistics</a></li>
                    <li><a href="anonymous_dashboard.php" class="<?php echo $current_page === 'anonymous_dashboard.php' ? 'active' : ''; ?>">Anonymous Data</a></li>
                    <li><a href="users.php" class="<?php echo $current_page === 'users.php' ? 'active' : ''; ?>">Users</a></li>
                    <li><a href="2fa-setup.php" class="<?php echo $current_page === '2fa-setup.php' ? 'active' : ''; ?>">2FA Settings</a></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                    <li><a href="referrals.php" class="<?php echo $current_page === 'referrals.php' ? 'active' : ''; ?>">Referrals</a></li>

                </ul>
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

            <script>
                const menu = document.getElementById('menu');
                const header = document.querySelector('.admin-header');
                const menuBtn = document.getElementById('menu-btn');
                const menuIcon = document.getElementById('menu-icon');

                function headerToggleMenu() {
                    if (!menu.classList.contains('active')) {
                        // Opening the menu
                        menu.classList.add('active');

                        // Get the current scroll height
                        const currentMenuHeight = menu.scrollHeight;
                        menu.style.height = currentMenuHeight + 'px';

                        document.body.classList.add('menu-open');
                    } else {
                        // Closing the menu
                        menu.classList.remove('active');
                        menu.style.height = '0';
                        document.body.classList.remove('menu-open');
                    }
                }

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    // Don't close if clicking menu icon
                    if (menuIcon.contains(e.target)) {
                        return;
                    }

                    // Close only if menu is active and click is outside header
                    if (menu.classList.contains('active') && !header.contains(e.target)) {
                        menuBtn.checked = false;
                        headerToggleMenu();
                    }
                });

                // Close menu when clicking on a link
                const mobileNavLinks = menu.querySelectorAll('a');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        menuBtn.checked = false;
                        headerToggleMenu();
                    });
                });

                // Close menu on escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && menu.classList.contains('active')) {
                        menuBtn.checked = false;
                        headerToggleMenu();
                    }
                });
            </script>