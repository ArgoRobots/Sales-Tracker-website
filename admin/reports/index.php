<?php
session_start();
require_once '../../db_connect.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Set page variables for the header
$page_title = "Content Reports";
$page_description = "Review and moderate reported posts and comments";

// Get filter parameters
$status_filter = $_GET['status'] ?? 'pending';
$content_type_filter = $_GET['content_type'] ?? 'all';

// Function to get all reports with filters
function get_reports($status = 'pending', $content_type = 'all')
{
    $db = get_db_connection();
    $reports = [];

    $query = 'SELECT
        r.id,
        r.content_type,
        r.content_id,
        r.violation_type,
        r.additional_info,
        r.status,
        r.created_at,
        r.reporter_email,
        reporter.username AS reporter_username,
        reported_user.id AS reported_user_id,
        reported_user.username AS reported_user_username,
        reported_user.email AS reported_user_email,
        reported_user.role AS reported_user_role,
        CASE
            WHEN r.content_type = "post" THEN p.title
            WHEN r.content_type = "comment" THEN CONCAT("Comment on: ", post.title)
        END AS content_title,
        CASE
            WHEN r.content_type = "post" THEN p.content
            WHEN r.content_type = "comment" THEN c.content
        END AS content_text
    FROM content_reports r
    LEFT JOIN community_users reporter ON r.reporter_user_id = reporter.id
    LEFT JOIN community_posts p ON r.content_type = "post" AND r.content_id = p.id
    LEFT JOIN community_comments c ON r.content_type = "comment" AND r.content_id = c.id
    LEFT JOIN community_posts post ON c.post_id = post.id
    LEFT JOIN community_users reported_user ON (
        (r.content_type = "post" AND p.user_id = reported_user.id) OR
        (r.content_type = "comment" AND c.user_id = reported_user.id)
    )
    WHERE 1=1';

    $types = '';
    $params = [];

    if ($status !== 'all') {
        $query .= ' AND r.status = ?';
        $types .= 's';
        $params[] = $status;
    }

    if ($content_type !== 'all') {
        $query .= ' AND r.content_type = ?';
        $types .= 's';
        $params[] = $content_type;
    }

    $query .= ' ORDER BY r.created_at DESC';

    if (!empty($params)) {
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $db->query($query);
    }

    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    return $reports;
}

// Get reports count by status
function get_reports_count_by_status()
{
    $db = get_db_connection();
    $counts = ['pending' => 0, 'resolved' => 0, 'dismissed' => 0];

    $result = $db->query('SELECT status, COUNT(*) as count FROM content_reports GROUP BY status');
    while ($row = $result->fetch_assoc()) {
        $counts[$row['status']] = $row['count'];
    }

    return $counts;
}

$reports = get_reports($status_filter, $content_type_filter);
$status_counts = get_reports_count_by_status();

// Include the admin header
include '../admin_header.php';
?>

<link rel="stylesheet" href="style.css">

<!-- Status badges -->
<div class="status-badges">
    <div class="status-badge pending">
        <div class="status-badge-label">Pending</div>
        <div class="status-badge-count"><?php echo $status_counts['pending']; ?></div>
    </div>
    <div class="status-badge">
        <div class="status-badge-label">Resolved</div>
        <div class="status-badge-count"><?php echo $status_counts['resolved']; ?></div>
    </div>
    <div class="status-badge">
        <div class="status-badge-label">Dismissed</div>
        <div class="status-badge-count"><?php echo $status_counts['dismissed']; ?></div>
    </div>
</div>

<!-- Filters -->
<div class="filters-container">
    <form method="GET" action="">
        <div class="filters-row">
            <div class="filter-group">
                <label for="status">Status</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="dismissed" <?php echo $status_filter === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="content_type">Content Type</label>
                <select name="content_type" id="content_type" onchange="this.form.submit()">
                    <option value="all" <?php echo $content_type_filter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="post" <?php echo $content_type_filter === 'post' ? 'selected' : ''; ?>>Posts</option>
                    <option value="comment" <?php echo $content_type_filter === 'comment' ? 'selected' : ''; ?>>Comments</option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- Reports list -->
<div class="reports-table">
    <?php if (empty($reports)): ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>No reports found</p>
        </div>
    <?php else: ?>
        <?php foreach ($reports as $report): ?>
            <div class="report-row" data-report-id="<?php echo $report['id']; ?>">
                <div class="report-header">
                    <div class="report-info">
                        <div class="report-id">Report #<?php echo $report['id']; ?></div>
                        <h3 class="report-title"><?php echo htmlspecialchars($report['content_title'] ?? 'Content Deleted'); ?></h3>
                        <div class="report-meta">
                            <span class="content-type-badge"><?php echo ucfirst($report['content_type']); ?></span>
                            <span class="violation-badge"><?php echo ucfirst(str_replace('_', ' ', $report['violation_type'])); ?></span>
                            <span class="status-badge-inline <?php echo $report['status']; ?>"><?php echo ucfirst($report['status']); ?></span>
                        </div>
                        <div class="report-meta">
                            <span>Reported by: <strong><?php echo htmlspecialchars($report['reporter_username'] ?? $report['reporter_email']); ?></strong></span>
                            <span>Reported user: <strong><?php echo htmlspecialchars($report['reported_user_username'] ?? 'Unknown'); ?></strong></span>
                            <span><?php echo date('M j, Y g:i a', strtotime($report['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($report['content_text']): ?>
                    <div class="content-preview">
                        <?php echo htmlspecialchars(substr($report['content_text'], 0, 200)) . (strlen($report['content_text']) > 200 ? '...' : ''); ?>
                    </div>
                <?php endif; ?>

                <?php if ($report['additional_info']): ?>
                    <div class="report-details">
                        <strong>Additional information:</strong> <?php echo htmlspecialchars($report['additional_info']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($report['status'] === 'pending'): ?>
                    <div class="report-actions">
                        <?php if ($report['content_text']): ?>
                            <a href="../../community/<?php echo $report['content_type'] === 'post' ? 'view_post.php?id=' . $report['content_id'] : 'view_post.php?id=' . $report['content_id']; ?>"
                               class="btn-small btn-view" target="_blank">View Content</a>
                        <?php endif; ?>
                        <?php if ($report['reported_user_role'] !== 'admin'): ?>
                            <button class="btn-small btn-delete" onclick="handleReport(<?php echo $report['id']; ?>, 'delete', '<?php echo $report['content_type']; ?>', <?php echo $report['content_id']; ?>)">Delete Content</button>
                            <?php if ($report['reported_user_id']): ?>
                                <button class="btn-small btn-ban" onclick="showBanModal(<?php echo $report['id']; ?>, <?php echo $report['reported_user_id']; ?>, '<?php echo htmlspecialchars($report['reported_user_username']); ?>')">Ban User</button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <button class="btn-small btn-dismiss" onclick="handleReport(<?php echo $report['id']; ?>, 'dismiss')">Dismiss</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Ban Modal -->
<div id="banModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Ban User</h3>
            <button class="modal-close" onclick="closeBanModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>You are about to ban <strong id="banUsername"></strong></p>
            <input type="hidden" id="banReportId">
            <input type="hidden" id="banUserId">

            <div class="form-group">
                <label for="banReason">Ban Reason</label>
                <textarea id="banReason" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="banDuration">Ban Duration</label>
                <select id="banDuration">
                    <option value="30_days">30 Days</option>
                    <option value="1_year">1 Year</option>
                    <option value="permanent">Permanent</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeBanModal()">Cancel</button>
            <button class="btn btn-red" onclick="submitBan()">Ban User</button>
        </div>
    </div>
</div>

<script src="reports.js"></script>

        </main>
    </div>
</body>

</html>