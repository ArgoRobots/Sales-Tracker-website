<?php
// Get current page and directory for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine base path for assets (different for subdirectories vs root)
$in_subdir = ($current_dir !== 'admin');
$base_path = $in_subdir ? '../' : '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $base_path; ?>../images/argo-logo/A-logo.ico">
    <title>Admin - Argo Sales Tracker</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="<?php echo $base_path; ?>../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="<?php echo $base_path; ?>common-style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../resources/styles/link.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../resources/styles/button.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../resources/notifications/notifications.css">
     <link rel="stylesheet" href="<?php echo $base_path; ?>../resources/styles/table-auto-size.css">
</head>

<body>
    <div class="admin-wrapper">
        <!-- Admin Header -->
        <header class="admin-header">
            <!-- BURGER MENU -->
            <input class="menu-btn" type="checkbox" id="menu-btn" onclick="headerToggleMenu()">
            <label class="menu-icon" id="menu-icon" for="menu-btn"><span class="nav-icon"></span></label>

            <div class="header-container">
                <a href="<?php echo $base_path; ?>../index.php" class="btn-small btn-home" title="Go to Main Site">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    <span class="home-text">Home</span>
                </a>

                <a href="<?php echo $base_path; ?>index.php" class="logo-section">
                    <img src="<?php echo $base_path; ?>../images/argo-logo/A-logo.ico" alt="Argo Logo" class="header-logo">
                    <span class="header-title">Admin Dashboard</span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="header-nav desktop-nav">
                    <a href="<?php echo $base_path; ?>license/" class="header-link <?php echo $current_dir === 'license' ? 'active' : ''; ?>">
                        License Keys
                    </a>
                    <a href="<?php echo $base_path; ?>app-stats/" class="header-link <?php echo $current_dir === 'app-stats' ? 'active' : ''; ?>">
                        App Stats
                    </a>
                    <a href="<?php echo $base_path; ?>website-stats/" class="header-link <?php echo $current_dir === 'website-stats' ? 'active' : ''; ?>">
                        Website Stats
                    </a>
                    <a href="<?php echo $base_path; ?>referral-links/" class="header-link <?php echo $current_dir === 'referral-links' ? 'active' : ''; ?>">
                        Referral Links
                    </a>
                    <a href="<?php echo $base_path; ?>users/" class="header-link <?php echo $current_dir === 'users' ? 'active' : ''; ?>">
                        Users
                    </a>
                    <a href="<?php echo $base_path; ?>reports/" class="header-link <?php echo $current_dir === 'reports' ? 'active' : ''; ?>">
                        Reports
                    </a>
                    <a href="<?php echo $base_path; ?>settings/" class="header-link <?php echo $current_dir === 'settings' ? 'active' : ''; ?>">
                        2FA Settings
                    </a>
                </nav>

                <!-- Desktop Actions -->
                <div class="header-actions desktop-actions">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    <a href="<?php echo $base_path; ?>logout.php" class="btn btn-small btn-red">Logout</a>
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
                        <a href="<?php echo $base_path; ?>../index.php" class="home-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                            </svg>
                            Home
                        </a>
                    </li>
                    <li><a href="<?php echo $base_path; ?>license/" class="<?php echo $current_dir === 'license' ? 'active' : ''; ?>">License Keys</a></li>
                    <li><a href="<?php echo $base_path; ?>app-stats/" class="<?php echo $current_dir === 'app-stats' ? 'active' : ''; ?>">App Stats</a></li>
                    <li><a href="<?php echo $base_path; ?>website-stats/" class="<?php echo $current_dir === 'website-stats' ? 'active' : ''; ?>">Website Stats</a></li>
                    <li><a href="<?php echo $base_path; ?>referral-links/" class="<?php echo $current_dir === 'referral-links' ? 'active' : ''; ?>">Referral Links</a></li>
                    <li><a href="<?php echo $base_path; ?>users/" class="<?php echo $current_dir === 'users' ? 'active' : ''; ?>">Users</a></li>
                    <li><a href="<?php echo $base_path; ?>reports/" class="<?php echo $current_dir === 'reports' ? 'active' : ''; ?>">Reports</a></li>
                    <li><a href="<?php echo $base_path; ?>settings/" class="<?php echo $current_dir === 'settings' ? 'active' : ''; ?>">2FA Settings</a></li>
                    <li><a href="<?php echo $base_path; ?>logout.php" class="logout-link">Logout</a></li>
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