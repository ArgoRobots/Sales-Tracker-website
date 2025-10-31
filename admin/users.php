<?php
session_start();
require_once '../db_connect.php';

// Check if user is already logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set page variables for the header
$page_title = "User Account Management";
$page_description = "Manage community user accounts, view user statistics, and moderate users";

// Handle bulk user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_ids'] ?? [];

    if (!empty($selected_ids) && $action === 'delete') {
        $db = get_db_connection();
        $success_count = 0;
        $fail_count = 0;

        foreach ($selected_ids as $user_id) {
            $stmt = $db->prepare('DELETE FROM community_users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $fail_count++;
            }
        }

        if ($success_count > 0) {
            $msg = $success_count . ' user' . ($success_count > 1 ? 's' : '') . ' deleted successfully.';
            if ($fail_count > 0) {
                $msg .= ' ' . $fail_count . ' failed.';
            }
            $_SESSION['message'] = $msg;
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete users.';
            $_SESSION['message_type'] = 'error';
        }

        // Redirect to prevent form resubmission
        header('Location: users.php' . (!empty($search) ? '?search=' . urlencode($search) : ''));
        exit;
    }
}

// Function to get all users
function get_all_users($search = '')
{
    $db = get_db_connection();
    $users = [];

    if (!empty($search)) {
        $search_param = '%' . $search . '%';
        $stmt = $db->prepare('SELECT * FROM community_users 
                             WHERE username LIKE ? 
                             OR email LIKE ? 
                             ORDER BY created_at DESC');
        $stmt->bind_param('ss', $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $result = $db->query('SELECT * FROM community_users ORDER BY created_at DESC');

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
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

include 'admin_header.php';
?>
<link rel="stylesheet" href="index.css">

<div class="container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
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

    <div class="table-container">
        <div class="table-header">
            <h2>Registered Users</h2>
        </div>

        <div class="search-container">
            <form method="get" action="users.php">
                <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-blue">Search</button>
            </form>
        </div>

        <?php if (!empty($search)): ?>
            <div class="search-results">
                Showing results for: "<?php echo htmlspecialchars($search); ?>" (<?php echo count($users); ?> results)
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <!-- Bulk Actions Form -->
            <form method="post" id="bulk-form">
                <!-- Bulk Actions Container -->
                <div class="bulk-actions-container">
                    <div class="selection-info">
                        <span id="selected-count">0</span> user(s) selected
                    </div>
                    <div class="bulk-buttons">
                        <button type="button" class="btn btn-bulk btn-delete" data-action="delete" disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <input type="hidden" name="bulk_action" id="bulk_action_input">

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-column">
                                <div class="checkbox">
                                    <input type="checkbox" id="select-all">
                                    <label for="select-all"></label>
                                </div>
                            </th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>Created</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="checkbox-column">
                                    <div class="checkbox">
                                        <input type="checkbox" 
                                            name="selected_ids[]" 
                                            value="<?php echo htmlspecialchars($user['id']); ?>"
                                            class="row-checkbox"
                                            id="user-<?php echo htmlspecialchars($user['id']); ?>">
                                        <label for="user-<?php echo htmlspecialchars($user['id']); ?>"></label>
                                    </div>
                                </td>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk selection functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkButtons = document.querySelectorAll('.btn-bulk');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkForm = document.getElementById('bulk-form');
        const bulkActionInput = document.getElementById('bulk_action_input');

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkedBoxes.length;
            selectedCountSpan.textContent = count;

            // Enable/disable buttons
            bulkButtons.forEach(btn => {
                btn.disabled = count === 0;
            });

            // Update select-all checkbox state
            if (count === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (count === rowCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Individual checkbox changes
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Bulk action buttons
        bulkButtons.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
                const count = checkedBoxes.length;

                if (count === 0) return;

                let confirmMessage = '';

                if (action === 'delete') {
                    confirmMessage = `Are you sure you want to delete ${count} user${count > 1 ? 's' : ''}? This action cannot be undone.`;
                }

                if (confirm(confirmMessage)) {
                    bulkActionInput.value = action;
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    bulkForm.submit();
                }
            });
        });

        // Initial count
        updateSelectedCount();

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

        // Also save position when clicking links
        const links = document.querySelectorAll('a[href^="users.php"]');
        links.forEach(link => {
            link.addEventListener('click', function() {
                sessionStorage.setItem('scrollPosition', window.scrollY);
            });
        });

        // Auto-clear search when textbox is emptied
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    window.location.href = 'users.php';
                }
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    this.value = '';
                    window.location.href = 'users.php';
                }
            });
        }
    });
</script>