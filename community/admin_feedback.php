<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Feedback Management - Argo Sales Tracker Admin</title>

    <script src="../resources/scripts/main.js"></script>
    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="admin-feedback.js"></script>

    <link rel="stylesheet" href="../admin/index-style.css">
    <link rel="stylesheet" href="admin-feedback-style.css">
</head>

<body>
    <?php
    session_start();
    require_once '../db_connect.php';

    // Check if user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }

    // Get database connection
    $db = get_db_connection();

    // Function to get bug reports with optional filters
    function get_bug_reports($db, $status = null, $search = null) {
        $query = "SELECT * FROM bug_reports";
        $conditions = [];
        $params = [];

        if ($status !== null) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }

        if ($search !== null) {
            $conditions[] = "(title LIKE :search OR steps_to_reproduce LIKE :search OR actual_result LIKE :search OR expected_result LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, SQLITE3_TEXT);
        }

        $result = $stmt->execute();

        $reports = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $reports[] = $row;
        }

        return $reports;
    }

    // Function to get feature requests with optional filters
    function get_feature_requests($db, $status = null, $search = null) {
        $query = "SELECT * FROM feature_requests";
        $conditions = [];
        $params = [];

        if ($status !== null) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }

        if ($search !== null) {
            $conditions[] = "(title LIKE :search OR description LIKE :search OR benefit LIKE :search OR examples LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, SQLITE3_TEXT);
        }

        $result = $stmt->execute();

        $requests = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $requests[] = $row;
        }

        return $requests;
    }

    // Function to update bug report status
    function update_bug_status($db, $id, $status) {
        $stmt = $db->prepare("UPDATE bug_reports SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $db->changes() > 0;
    }

    // Function to update feature request status
    function update_feature_status($db, $id, $status) {
        $stmt = $db->prepare("UPDATE feature_requests SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $db->changes() > 0;
    }

    // Process status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_bug_status' && isset($_POST['bug_id']) && isset($_POST['status'])) {
            $success = update_bug_status($db, $_POST['bug_id'], $_POST['status']);
            if ($success) {
                $status_message = "Bug report status updated successfully.";
            } else {
                $error_message = "Failed to update bug report status.";
            }
        } elseif ($_POST['action'] === 'update_feature_status' && isset($_POST['feature_id']) && isset($_POST['status'])) {
            $success = update_feature_status($db, $_POST['feature_id'], $_POST['status']);
            if ($success) {
                $status_message = "Feature request status updated successfully.";
            } else {
                $error_message = "Failed to update feature request status.";
            }
        }
    }

    // Get filter parameters
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'bugs';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
    $search_query = isset($_GET['search']) ? $_GET['search'] : null;

    // Get reports based on current tab and filters
    $bug_reports = $tab === 'bugs' ? get_bug_reports($db, $status_filter, $search_query) : [];
    $feature_requests = $tab === 'features' ? get_feature_requests($db, $status_filter, $search_query) : [];

    // Count total bugs and features
    $total_bugs = $db->querySingle("SELECT COUNT(*) FROM bug_reports");
    $total_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests");

    // Count bugs by status
    $new_bugs = $db->querySingle("SELECT COUNT(*) FROM bug_reports WHERE status = 'new'");
    $in_progress_bugs = $db->querySingle("SELECT COUNT(*) FROM bug_reports WHERE status = 'in_progress'");
    $resolved_bugs = $db->querySingle("SELECT COUNT(*) FROM bug_reports WHERE status = 'resolved'");
    $closed_bugs = $db->querySingle("SELECT COUNT(*) FROM bug_reports WHERE status = 'closed'");

    // Count features by status
    $new_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests WHERE status = 'new'");
    $under_review_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests WHERE status = 'under_review'");
    $planned_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests WHERE status = 'planned'");
    $completed_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests WHERE status = 'completed'");
    $declined_features = $db->querySingle("SELECT COUNT(*) FROM feature_requests WHERE status = 'declined'");
    ?>

    <div class="container">
        <div class="header">
            <h1>User Feedback Management</h1>
            <div class="header-buttons">
                <a href="../admin/index.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="../admin/logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>

        <!-- Status message -->
        <?php if (isset($status_message)): ?>
            <div class="status-message success">
                <?php echo htmlspecialchars($status_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="status-message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-header">Bug Reports</div>
                <div class="stat-value"><?php echo $total_bugs; ?></div>
                <div class="stat-details">
                    <div class="stat-tag new"><?php echo $new_bugs; ?> New</div>
                    <div class="stat-tag in-progress"><?php echo $in_progress_bugs; ?> In Progress</div>
                    <div class="stat-tag resolved"><?php echo $resolved_bugs; ?> Resolved</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">Feature Requests</div>
                <div class="stat-value"><?php echo $total_features; ?></div>
                <div class="stat-details">
                    <div class="stat-tag new"><?php echo $new_features; ?> New</div>
                    <div class="stat-tag under-review"><?php echo $under_review_features; ?> Under Review</div>
                    <div class="stat-tag planned"><?php echo $planned_features; ?> Planned</div>
                </div>
            </div>
        </div>

        <!-- Tab navigation -->
        <div class="tab-navigation">
            <a href="?tab=bugs" class="tab-link <?php echo $tab === 'bugs' ? 'active' : ''; ?>">
                Bug Reports
            </a>
            <a href="?tab=features" class="tab-link <?php echo $tab === 'features' ? 'active' : ''; ?>">
                Feature Requests
            </a>
        </div>

        <!-- Filters and search -->
        <div class="filters-container">
            <div class="filter-group">
                <label for="status-filter">Status:</label>
                <select id="status-filter" onchange="applyFilters()">
                    <option value="">All Statuses</option>
                    <?php if ($tab === 'bugs'): ?>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    <?php else: ?>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                        <option value="planned" <?php echo $status_filter === 'planned' ? 'selected' : ''; ?>>Planned</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="declined" <?php echo $status_filter === 'declined' ? 'selected' : ''; ?>>Declined</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="search-container">
                <input type="text" id="search-input" placeholder="Search..." value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
                <button onclick="applyFilters()" class="btn search-btn">Search</button>
                <?php if ($search_query): ?>
                    <button onclick="clearSearch()" class="btn clear-btn">Clear</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bug reports table -->
        <?php if ($tab === 'bugs'): ?>
            <div class="feedback-table-container">
                <h2>Bug Reports <?php if ($status_filter || $search_query): ?><span class="filter-indicator">(Filtered)</span><?php endif; ?></h2>
                
                <?php if (empty($bug_reports)): ?>
                    <div class="empty-state">
                        <p>No bug reports found.</p>
                    </div>
                <?php else: ?>
                    <table class="feedback-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Severity</th>
                                <th>OS</th>
                                <th>Version</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bug_reports as $bug): ?>
                                <tr>
                                    <td>#<?php echo $bug['id']; ?></td>
                                    <td class="title-cell">
                                        <a href="#" onclick="viewBugDetails(<?php echo $bug['id']; ?>); return false;">
                                            <?php echo htmlspecialchars($bug['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="severity-tag <?php echo strtolower($bug['severity']); ?>">
                                            <?php echo ucfirst($bug['severity']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($bug['operating_system']); ?></td>
                                    <td><?php echo htmlspecialchars($bug['version']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($bug['created_at'])); ?></td>
                                    <td>
                                        <span class="status-tag <?php echo $bug['status']; ?>">
                                            <?php 
                                                $status_display = str_replace('_', ' ', $bug['status']);
                                                echo ucwords($status_display); 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="action" value="update_bug_status">
                                            <input type="hidden" name="bug_id" value="<?php echo $bug['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="">Update Status</option>
                                                <option value="new" <?php echo $bug['status'] === 'new' ? 'disabled' : ''; ?>>New</option>
                                                <option value="in_progress" <?php echo $bug['status'] === 'in_progress' ? 'disabled' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $bug['status'] === 'resolved' ? 'disabled' : ''; ?>>Resolved</option>
                                                <option value="closed" <?php echo $bug['status'] === 'closed' ? 'disabled' : ''; ?>>Closed</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Feature requests table -->
        <?php if ($tab === 'features'): ?>
            <div class="feedback-table-container">
                <h2>Feature Requests <?php if ($status_filter || $search_query): ?><span class="filter-indicator">(Filtered)</span><?php endif; ?></h2>
                
                <?php if (empty($feature_requests)): ?>
                    <div class="empty-state">
                        <p>No feature requests found.</p>
                    </div>
                <?php else: ?>
                    <table class="feedback-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feature_requests as $feature): ?>
                                <tr>
                                    <td>#<?php echo $feature['id']; ?></td>
                                    <td class="title-cell">
                                        <a href="#" onclick="viewFeatureDetails(<?php echo $feature['id']; ?>); return false;">
                                            <?php echo htmlspecialchars($feature['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $feature['category'])); ?></td>
                                    <td>
                                        <?php if ($feature['priority']): ?>
                                            <span class="priority-tag <?php echo strtolower($feature['priority']); ?>">
                                                <?php echo ucfirst($feature['priority']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="priority-tag none">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($feature['created_at'])); ?></td>
                                    <td>
                                        <span class="status-tag <?php echo $feature['status']; ?>">
                                            <?php 
                                                $status_display = str_replace('_', ' ', $feature['status']);
                                                echo ucwords($status_display); 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="action" value="update_feature_status">
                                            <input type="hidden" name="feature_id" value="<?php echo $feature['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="">Update Status</option>
                                                <option value="new" <?php echo $feature['status'] === 'new' ? 'disabled' : ''; ?>>New</option>
                                                <option value="under_review" <?php echo $feature['status'] === 'under_review' ? 'disabled' : ''; ?>>Under Review</option>
                                                <option value="planned" <?php echo $feature['status'] === 'planned' ? 'disabled' : ''; ?>>Planned</option>
                                                <option value="completed" <?php echo $feature['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                                                <option value="declined" <?php echo $feature['status'] === 'declined' ? 'disabled' : ''; ?>>Declined</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bug details modal -->
    <div id="bugModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeBugModal()">&times;</span>
            <div id="bugDetails"></div>
        </div>
    </div>

    <!-- Feature details modal -->
    <div id="featureModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeFeatureModal()">&times;</span>
            <div id="featureDetails"></div>
        </div>
    </div>

    <script>
        // Apply filters function
        function applyFilters() {
            const status = document.getElementById('status-filter').value;
            const search = document.getElementById('search-input').value.trim();
            const currentTab = '<?php echo $tab; ?>';
            
            let url = `?tab=${currentTab}`;
            
            if (status) {
                url += `&status=${encodeURIComponent(status)}`;
            }
            
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }
            
            window.location.href = url;
        }
        
        // Clear search function
        function clearSearch() {
            const status = document.getElementById('status-filter').value;
            const currentTab = '<?php echo $tab; ?>';
            
            let url = `?tab=${currentTab}`;
            
            if (status) {
                url += `&status=${encodeURIComponent(status)}`;
            }
            
            window.location.href = url;
        }
        
        // View bug details
        function viewBugDetails(id) {
            fetch(`get_bug_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('bugDetails').innerHTML = data.html;
                        document.getElementById('bugModal').style.display = 'block';
                    } else {
                        alert('Error loading bug details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading bug details.');
                });
        }
        
        // Close bug modal
        function closeBugModal() {
            document.getElementById('bugModal').style.display = 'none';
        }
        
        // View feature details
        function viewFeatureDetails(id) {
            fetch(`get_feature_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('featureDetails').innerHTML = data.html;
                        document.getElementById('featureModal').style.display = 'block';
                    } else {
                        alert('Error loading feature details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading feature details.');
                });
        }
        
        // Close feature modal
        function closeFeatureModal() {
            document.getElementById('featureModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const bugModal = document.getElementById('bugModal');
            const featureModal = document.getElementById('featureModal');
            
            if (event.target === bugModal) {
                bugModal.style.display = 'none';
            } else if (event.target === featureModal) {
                featureModal.style.display = 'none';
            }
        }
        
        // Listen for Enter key in search input
        document.getElementById('search-input').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                applyFilters();
            }
        });
    </script>
</body>

</html>