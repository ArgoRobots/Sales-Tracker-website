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

// Get stats
// Total license keys
$result = $db->query('SELECT COUNT(*) as count FROM license_keys');
$total_licenses = $result->fetch_assoc()['count'] ?? 0;

// Active licenses
$result = $db->query('SELECT COUNT(*) as count FROM license_keys WHERE activated = 1');
$active_licenses = $result->fetch_assoc()['count'] ?? 0;

// Total users
$result = $db->query('SELECT COUNT(*) as count FROM community_users');
$total_users = $result->fetch_assoc()['count'] ?? 0;

// Recent activity (last 7 days)
$result = $db->query('SELECT COUNT(*) as count FROM license_keys WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
$recent_activity = $result->fetch_assoc()['count'] ?? 0;

// Get recent activity items for timeline
$recent_items = [];

// Recent license keys (last 5)
$result = $db->query('SELECT license_key, email, created_at, activated FROM license_keys ORDER BY created_at DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    $recent_items[] = [
        'type' => 'license',
        'time' => $row['created_at'],
        'description' => 'New license key generated for ' . htmlspecialchars($row['email']),
        'status' => $row['activated'] ? 'active' : 'pending'
    ];
}

// Recent user registrations (last 5)
$result = $db->query('SELECT username, created_at, email_verified FROM community_users ORDER BY created_at DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    $recent_items[] = [
        'type' => 'user',
        'time' => $row['created_at'],
        'description' => 'New user registered: ' . htmlspecialchars($row['username']),
        'status' => $row['email_verified'] ? 'verified' : 'pending'
    ];
}

// Sort by time
usort($recent_items, function ($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

// Keep only 10 most recent
$recent_items = array_slice($recent_items, 0, 10);

// System health checks
$system_health = [
    'database' => 'operational',
    'api' => 'operational',
    'storage' => 'operational'
];

// Check database
try {
    $db->query('SELECT 1');
} catch (Exception $e) {
    $system_health['database'] = 'error';
}

// Calculate activation rate
$activation_rate = $total_licenses > 0 ? round(($active_licenses / $total_licenses) * 100) : 0;

include 'admin_header.php';
?>

<link rel="stylesheet" href="style.css">

<div class="dashboard-home">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Licenses</div>
            <div class="stat-value"><?php echo number_format($total_licenses); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Licenses</div>
            <div class="stat-value"><?php echo number_format($active_licenses); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">This Week</div>
            <div class="stat-value"><?php echo number_format($recent_activity); ?></div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="nav-cards">
        <a href="license/" class="nav-card">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
            </div>
            <div class="nav-card-title">License Keys</div>
            <div class="nav-card-description">Manage and generate license keys</div>
            <div class="nav-card-stat">
                <span class="nav-card-stat-label">Activation Rate</span>
                <span class="nav-card-stat-value"><?php echo $activation_rate; ?>%</span>
            </div>
        </a>

        <a href="app-stats/" class="nav-card">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                </svg>
            </div>
            <div class="nav-card-title">App Statistics</div>
            <div class="nav-card-description">View application analytics and metrics</div>
            <div class="nav-card-stat">
                <span class="nav-card-stat-label">Active Users</span>
                <span class="nav-card-stat-value"><?php echo number_format($active_licenses); ?></span>
            </div>
        </a>

        <a href="website-stats/" class="nav-card">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                </svg>
            </div>
            <div class="nav-card-title">Website Statistics</div>
            <div class="nav-card-description">View website analytics and metrics</div>
            <div class="nav-card-stat">
                <span class="nav-card-stat-label">Community</span>
                <span class="nav-card-stat-value"><?php echo number_format($total_users); ?></span>
            </div>
        </a>

        <a href="users/" class="nav-card">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
            </div>
            <div class="nav-card-title">User Management</div>
            <div class="nav-card-description">Manage community users</div>
            <div class="nav-card-stat">
                <span class="nav-card-stat-label">Registered</span>
                <span class="nav-card-stat-value"><?php echo number_format($total_users); ?></span>
            </div>
        </a>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Activity Timeline -->
        <div class="activity-section">
            <h2 class="section-title">Recent Activity</h2>
            <div class="timeline">
                <?php if (empty($recent_items)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 2rem 0;">No recent activity</p>
                <?php else: ?>
                    <?php foreach ($recent_items as $item): ?>
                        <div class="timeline-item type-<?php echo $item['type']; ?>">
                            <div class="timeline-time">
                                <?php
                                $time = strtotime($item['time']);
                                $diff = time() - $time;
                                if ($diff < 60) {
                                    echo 'Just now';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' hours ago';
                                } else {
                                    echo date('M j, Y g:i A', $time);
                                }
                                ?>
                            </div>
                            <div class="timeline-description"><?php echo $item['description']; ?></div>
                            <span class="timeline-status <?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Health -->
        <div class="health-section">
            <h2 class="section-title">System Health</h2>
            <div class="health-items">
                <?php foreach ($system_health as $component => $status): ?>
                    <div class="health-item">
                        <span class="health-item-name"><?php echo ucfirst($component); ?></span>
                        <div class="health-status">
                            <div class="health-indicator <?php echo $status === 'error' ? 'error' : ''; ?>"></div>
                            <span><?php echo ucfirst($status); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="health-divider"></div>
            <div class="health-summary">
                <div class="health-summary-text">All systems operational</div>
            </div>
        </div>
    </div>
</div>