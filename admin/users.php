<?php
session_start();
require_once '../db_connect.php';

// Check if user is already logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $db = get_db_connection();
    $stmt = $db->prepare('DELETE FROM community_users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['message'] = 'User deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to delete user.';
        $_SESSION['message_type'] = 'error';
    }

    // Redirect to prevent form resubmission
    header('Location: users.php');
    exit;
}

// Function to get all users
function get_all_users($search = '')
{
    $db = get_db_connection();

    if (!empty($search)) {
        $stmt = $db->prepare('SELECT * FROM community_users 
                             WHERE username LIKE :search 
                             OR email LIKE :search 
                             ORDER BY created_at DESC');
        $stmt->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);
        $result = $stmt->execute();
    } else {
        $result = $db->query('SELECT * FROM community_users ORDER BY created_at DESC');
    }

    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }

    return $users;
}

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get users
$users = get_all_users($search);

// Get user statistics for dashboard
$db = get_db_connection();

// Total users
$total_users = count($users);

// Verified users count
$verified_count = 0;
foreach ($users as $user) {
    if ($user['email_verified']) {
        $verified_count++;
    }
}

// Admin users count
$admin_count = 0;
foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        $admin_count++;
    }
}

// Recent users (joined in the last 30 days)
$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
$recent_count = 0;
foreach ($users as $user) {
    if (strtotime($user['created_at']) > strtotime($thirty_days_ago)) {
        $recent_count++;
    }
}

// Check for flash messages
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>User Management - Argo Sales Tracker</title>

    <script src="../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="index-style.css">
    <link rel="stylesheet" href="users-style.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>User Account Management</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn">License Keys</a>
                <a href="2fa-setup.php" class="btn">2FA Settings</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <h3>Verified Users</h3>
                <div class="stat-value"><?php echo $verified_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Admin Users</h3>
                <div class="stat-value"><?php echo $admin_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>New Users (30 days)</h3>
                <div class="stat-value"><?php echo $recent_count; ?></div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <form method="get" action="users.php">
                <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="users.php" class="btn" style="background: #6b7280;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Registered Users</h2>
                <span class="total-count">Total: <?php echo count($users); ?></span>
            </div>

            <?php if (!empty($search)): ?>
                <div class="search-results">
                    Showing results for: "<?php echo htmlspecialchars($search); ?>" (<?php echo count($users); ?> results)
                </div>
            <?php endif; ?>

            <?php if (empty($users)): ?>
                <p>No users found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'admin' : 'user'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['email_verified']): ?>
                                        <span class="badge badge-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                <td><?php echo $user['last_login'] ? htmlspecialchars(date('Y-m-d', strtotime($user['last_login']))) : 'Never'; ?></td>
                                <td class="action-buttons">
                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" name="delete_user" class="btn btn-small btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Restore scroll position if it exists in sessionStorage
            if (sessionStorage.getItem('scrollPosition')) {
                window.scrollTo(0, sessionStorage.getItem('scrollPosition'));
                sessionStorage.removeItem('scrollPosition');
            }

            // Save scroll position when submitting forms
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                });
            });

            // Also save position when clicking links (for the "Clear" button)
            const links = document.querySelectorAll('a[href^="users.php"]');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                });
            });

            // Auto-clear search when textbox is emptied, preserving scroll position
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                let typingTimer;

                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);

                    if (this.value.trim() === '') {
                        // Save current scroll position before redirecting
                        sessionStorage.setItem('scrollPosition', window.scrollY);
                        window.location.href = 'users.php';
                    }
                });

                // Add ability to press Escape key to clear search
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        // Save current scroll position before redirecting
                        sessionStorage.setItem('scrollPosition', window.scrollY);
                        this.value = '';
                        window.location.href = 'users.php';
                    }
                });
            }
        });
    </script>
</body>

</html>