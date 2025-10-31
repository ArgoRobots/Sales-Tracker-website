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

// Function to get all users with optional filters
function get_all_users($search = '', $date_from = '', $date_to = '')
{
    $db = get_db_connection();
    $users = [];

    $query = 'SELECT * FROM community_users WHERE 1=1';
    $types = '';
    $params = [];

    if (!empty($search)) {
        $query .= ' AND (username LIKE ? OR email LIKE ?)';
        $search_param = '%' . $search . '%';
        $types .= 'ss';
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($date_from)) {
        $query .= ' AND DATE(created_at) >= ?';
        $types .= 's';
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $query .= ' AND DATE(created_at) <= ?';
        $types .= 's';
        $params[] = $date_to;
    }

    $query .= ' ORDER BY created_at DESC';

    if (!empty($params)) {
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $result = $db->query($query);

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_preset = isset($_GET['date_preset']) ? trim($_GET['date_preset']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Calculate date range based on preset
if (!empty($date_preset) && $date_preset !== 'custom') {
    $date_to = date('Y-m-d'); // Today

    switch ($date_preset) {
        case 'today':
            $date_from = date('Y-m-d');
            break;
        case 'last_week':
            $date_from = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'last_month':
            $date_from = date('Y-m-d', strtotime('-30 days'));
            break;
        case 'last_year':
            $date_from = date('Y-m-d', strtotime('-365 days'));
            break;
        case 'last_3_years':
            $date_from = date('Y-m-d', strtotime('-1095 days'));
            break;
        case 'last_5_years':
            $date_from = date('Y-m-d', strtotime('-1825 days'));
            break;
    }
}

// Get users (filtered)
$users = get_all_users($search, $date_from, $date_to);

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
<link rel="stylesheet" href="../resources/styles/checkbox.css">

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

        <!-- Filter Container -->
        <form method="get" action="users.php" id="filter-form">
            <div class="filter-container">
                <div class="filter-group search-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Username or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group date-filter-group">
                    <label for="date_preset">Date Range</label>
                    <select id="date_preset" name="date_preset" class="date-preset-select">
                        <option value="" <?php echo empty($date_preset) ? 'selected' : ''; ?>>All Time</option>
                        <option value="today" <?php echo $date_preset === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="last_week" <?php echo $date_preset === 'last_week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="last_month" <?php echo $date_preset === 'last_month' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="last_year" <?php echo $date_preset === 'last_year' ? 'selected' : ''; ?>>Last Year</option>
                        <option value="last_3_years" <?php echo $date_preset === 'last_3_years' ? 'selected' : ''; ?>>Last 3 Years</option>
                        <option value="last_5_years" <?php echo $date_preset === 'last_5_years' ? 'selected' : ''; ?>>Last 5 Years</option>
                        <option value="custom" <?php echo $date_preset === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>

                    <div class="custom-date-range" id="custom_date_range" style="display: <?php echo $date_preset === 'custom' ? 'flex' : 'none'; ?>;">
                        <div class="date-input-group">
                            <label for="date_from">From</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo $date_preset === 'custom' ? htmlspecialchars($date_from) : ''; ?>">
                        </div>
                        <div class="date-input-group">
                            <label for="date_to">To</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo $date_preset === 'custom' ? htmlspecialchars($date_to) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-blue">Apply</button>
                </div>
            </div>
        </form>

        <?php if (!empty($search) || !empty($date_preset)): ?>
            <div class="search-results">
                <?php
                $filters = [];
                if (!empty($search)) $filters[] = "search: \"" . htmlspecialchars($search) . "\"";
                if (!empty($date_preset)) {
                    if ($date_preset === 'custom') {
                        $filters[] = "date: " . htmlspecialchars($date_from) . " to " . htmlspecialchars($date_to);
                    } else {
                        $preset_labels = [
                            'today' => 'Today',
                            'last_week' => 'Last 7 Days',
                            'last_month' => 'Last 30 Days',
                            'last_year' => 'Last Year',
                            'last_3_years' => 'Last 3 Years',
                            'last_5_years' => 'Last 5 Years'
                        ];
                        $filters[] = "date: " . $preset_labels[$date_preset];
                    }
                }
                echo "Showing results for " . implode(", ", $filters) . " (" . count($users) . " results)";
                ?>
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

        // Date preset select handling
        const datePresetSelect = document.getElementById('date_preset');
        const customDateRange = document.getElementById('custom_date_range');

        datePresetSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
            }
        });

        // If user clicks on date inputs, select custom option
        const dateInputs = customDateRange.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('focus', function() {
                datePresetSelect.value = 'custom';
                customDateRange.style.display = 'flex';
            });
        });

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
        const searchInput = document.querySelector('#search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    const datePreset = document.getElementById('date_preset').value;

                    if (!datePreset) {
                        sessionStorage.setItem('scrollPosition', window.scrollY);
                        window.location.href = 'users.php';
                    }
                }
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    this.value = '';
                    const datePreset = document.getElementById('date_preset').value;

                    if (!datePreset) {
                        window.location.href = 'users.php';
                    }
                }
            });
        }
    });
</script>