<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Function to get download statistics by period
function get_downloads_by_period($period = 'month', $limit = 12)
{
    $db = get_db_connection();

    $sql_period = '';
    $display_format = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(created_at)';
            $display_format = 'DATE(created_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(created_at)';
            $display_format = 'CONCAT("Week ", WEEK(created_at), ", ", YEAR(created_at))';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(created_at, "%Y-%m")';
            $display_format = 'DATE_FORMAT(created_at, "%b %Y")';
            break;
        case 'year':
            $sql_period = 'YEAR(created_at)';
            $display_format = 'YEAR(created_at)';
            break;
    }

    // For demonstration, using the license_keys table for download stats
    // In practice, you would use the statistics table we'll create
    $query = "
        SELECT 
            $sql_period as period, 
            $display_format as display_period,
            COUNT(*) as count 
        FROM license_keys 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

// Function to get user registrations by period
function get_registrations_by_period($period = 'month', $limit = 12)
{
    $db = get_db_connection();

    $sql_period = '';
    $display_format = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(created_at)';
            $display_format = 'DATE(created_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(created_at)';
            $display_format = 'CONCAT("Week ", WEEK(created_at), ", ", YEAR(created_at))';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(created_at, "%Y-%m")';
            $display_format = 'DATE_FORMAT(created_at, "%b %Y")';
            break;
        case 'year':
            $sql_period = 'YEAR(created_at)';
            $display_format = 'YEAR(created_at)';
            break;
    }

    $query = "
        SELECT 
            $sql_period as period, 
            $display_format as display_period,
            COUNT(*) as count 
        FROM community_users 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

// Function to get activation statistics
function get_activation_rate()
{
    $db = get_db_connection();
    $query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN activated = 1 THEN 1 ELSE 0 END) as activated
        FROM license_keys";

    $result = $db->query($query);
    $data = $result->fetch_assoc();

    return $data;
}

// Function to get page view statistics
function get_page_views_by_period($period = 'month', $limit = 12)
{
    $db = get_db_connection();

    $sql_period = '';
    $display_format = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(created_at)';
            $display_format = 'DATE(created_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(created_at)';
            $display_format = 'CONCAT("Week ", WEEK(created_at), ", ", YEAR(created_at))';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(created_at, "%Y-%m")';
            $display_format = 'DATE_FORMAT(created_at, "%b %Y")';
            break;
        case 'year':
            $sql_period = 'YEAR(created_at)';
            $display_format = 'YEAR(created_at)';
            break;
    }

    $query = "
        SELECT 
            $sql_period as period, 
            $display_format as display_period,
            COUNT(*) as count 
        FROM statistics 
        WHERE event_type = 'page_view' 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

// Function to get community post views
function get_community_post_views()
{
    $db = get_db_connection();
    $query = "
        SELECT 
            SUM(views) as total_views,
            AVG(views) as avg_views_per_post,
            MAX(views) as most_viewed
        FROM community_posts";

    $result = $db->query($query);
    $data = $result->fetch_assoc();

    return $data;
}

// Function to get community activity by post type
function get_community_post_types()
{
    $db = get_db_connection();
    $query = "
        SELECT 
            post_type,
            COUNT(*) as count,
            SUM(views) as total_views
        FROM community_posts
        GROUP BY post_type";

    $result = $db->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Function to get geographic distribution of users
function get_user_countries()
{
    $db = get_db_connection();
    $query = "
        SELECT 
            country_code,
            COUNT(*) as count
        FROM statistics
        WHERE country_code IS NOT NULL AND country_code != ''
        GROUP BY country_code
        ORDER BY count DESC
        LIMIT 10";

    $result = $db->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Function to get browser/platform statistics
function get_user_agents()
{
    $db = get_db_connection();
    $query = "
        SELECT 
            CASE
                WHEN user_agent LIKE '%Chrome%' THEN 'Chrome'
                WHEN user_agent LIKE '%Firefox%' THEN 'Firefox'
                WHEN user_agent LIKE '%Safari%' THEN 'Safari'
                WHEN user_agent LIKE '%Edge%' THEN 'Edge'
                WHEN user_agent LIKE '%MSIE%' OR user_agent LIKE '%Trident%' THEN 'Internet Explorer'
                ELSE 'Other'
            END as browser,
            COUNT(*) as count
        FROM statistics
        WHERE user_agent IS NOT NULL
        GROUP BY browser
        ORDER BY count DESC";

    $result = $db->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Function to get conversion rate data
function get_conversion_data()
{
    $db = get_db_connection();

    // Get total downloads (from statistics table)
    $download_query = "SELECT COUNT(*) as count FROM statistics WHERE event_type = 'download'";
    $download_result = $db->query($download_query);
    $downloads = $download_result->fetch_assoc()['count'];

    // Get total registrations
    $reg_query = "SELECT COUNT(*) as count FROM community_users";
    $reg_result = $db->query($reg_query);
    $registrations = $reg_result->fetch_assoc()['count'];

    // Get total license keys purchased
    $license_query = "SELECT COUNT(*) as count FROM license_keys";
    $license_result = $db->query($license_query);
    $licenses = $license_result->fetch_assoc()['count'];

    return [
        'downloads' => $downloads,
        'registrations' => $registrations,
        'licenses' => $licenses,
        'registration_to_purchase' => $registrations > 0 ? ($licenses / $registrations) * 100 : 0
    ];
}

// Function to get most active users
function get_most_active_users($limit = 5)
{
    $db = get_db_connection();
    $query = "
        SELECT 
            u.username,
            u.email,
            COUNT(DISTINCT p.id) as post_count,
            COUNT(DISTINCT c.id) as comment_count,
            SUM(p.views) as total_views,
            (COUNT(DISTINCT p.id) + COUNT(DISTINCT c.id)) as activity_score
        FROM community_users u
        LEFT JOIN community_posts p ON u.id = p.user_id
        LEFT JOIN community_comments c ON u.id = c.user_id
        GROUP BY u.id, u.username, u.email
        ORDER BY activity_score DESC
        LIMIT ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Get statistics by period (default to month)
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$allowed_periods = ['day', 'week', 'month', 'year'];
if (!in_array($period, $allowed_periods)) {
    $period = 'month';
}

$downloads = get_downloads_by_period($period);
$registrations = get_registrations_by_period($period);
$activation_rate = get_activation_rate();
$page_views = get_page_views_by_period($period);
$post_views = get_community_post_views();
$post_types = get_community_post_types();
$user_countries = get_user_countries();
$user_agents = get_user_agents();
$conversion_data = get_conversion_data();
$active_users = get_most_active_users();

// Prepare data for charts
$chart_labels = [];
$downloads_data = [];
$registrations_data = [];
$page_views_data = [];

// Reverse arrays to show chronological order
$downloads = array_reverse($downloads);
$registrations = array_reverse($registrations);
$page_views = array_reverse($page_views);

foreach ($downloads as $item) {
    $chart_labels[] = isset($item['display_period']) ? $item['display_period'] : $item['period'];
    $downloads_data[] = $item['count'];
}

$reg_data = [];
foreach ($registrations as $item) {
    $period_key = $item['period'];
    $reg_data[$period_key] = $item['count'];
}

// Align registration data with download periods
foreach ($downloads as $index => $item) {
    $period_key = $item['period'];
    $registrations_data[] = isset($reg_data[$period_key]) ? $reg_data[$period_key] : 0;
}

// Align page view data with download periods
$view_data = [];
foreach ($page_views as $item) {
    $period_key = $item['period'];
    $view_data[$period_key] = $item['count'];
}

foreach ($downloads as $index => $item) {
    $period_key = $item['period'];
    $page_views_data[] = isset($view_data[$period_key]) ? $view_data[$period_key] : 0;
}

// Calculate activation rate percentage
$activation_percentage = 0;
if ($activation_rate['total'] > 0) {
    $activation_percentage = round(($activation_rate['activated'] / $activation_rate['total']) * 100, 1);
}

// Calculate growth rate
$latest_growth = 0;
if (count($downloads_data) >= 2) {
    $latest = end($downloads_data);
    $previous = prev($downloads_data);
    if ($previous > 0) {
        $latest_growth = round((($latest - $previous) / $previous) * 100, 1);
    }
}

// Format post views numbers
$total_post_views = isset($post_views['total_views']) ? number_format($post_views['total_views']) : 0;
$avg_post_views = isset($post_views['avg_views_per_post']) ? round($post_views['avg_views_per_post'], 1) : 0;
$most_viewed = isset($post_views['most_viewed']) ? number_format($post_views['most_viewed']) : 0;

// Prepare post type data for charts
$post_type_labels = [];
$post_type_counts = [];
$post_type_views = [];

foreach ($post_types as $type) {
    $post_type_labels[] = ucfirst($type['post_type']);
    $post_type_counts[] = $type['count'];
    $post_type_views[] = $type['total_views'];
}

// Prepare country data for charts
$country_labels = [];
$country_counts = [];

foreach ($user_countries as $country) {
    $country_labels[] = $country['country_code'];
    $country_counts[] = $country['count'];
}

// Prepare browser data for charts
$browser_labels = [];
$browser_counts = [];

foreach ($user_agents as $agent) {
    $browser_labels[] = $agent['browser'];
    $browser_counts[] = $agent['count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Statistics - Argo Sales Tracker</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="statistics.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Enhanced Statistics Dashboard</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn">License Keys</a>
                <a href="users.php" class="btn">User Accounts</a>
                <a href="2fa-setup.php" class="btn">2FA Settings</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>

        <!-- Period selection -->
        <div class="period-selection">
            <span>Time Period:</span>
            <div class="period-buttons">
                <?php
                // Define all periods with their display names
                $periods = [
                    'day' => 'Daily',
                    'week' => 'Weekly',
                    'month' => 'Monthly',
                    'year' => 'Yearly'
                ];

                // Loop through periods and create buttons
                foreach ($periods as $periodKey => $periodName) {
                    $activeClass = ($period === $periodKey) ? 'active' : '';
                    echo "<a href=\"?period={$periodKey}\" class=\"period-btn {$activeClass}\">{$periodName}</a>";
                }
                ?>
            </div>
        </div>

        <!-- Statistics cards -->
        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Downloads</h3>
                <div class="stat-value"><?php echo array_sum($downloads_data); ?></div>
            </div>
            <div class="stat-card">
                <h3>Registrations</h3>
                <div class="stat-value"><?php echo array_sum($registrations_data); ?></div>
            </div>
            <div class="stat-card">
                <h3>Activation Rate</h3>
                <div class="stat-value"><?php echo $activation_percentage; ?>%</div>
            </div>
            <div class="stat-card">
                <h3>Growth Rate</h3>
                <div class="stat-value"><?php echo ($latest_growth >= 0 ? '+' : '') . $latest_growth; ?>%</div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <h3>Page Views</h3>
                <div class="stat-value"><?php echo array_sum($page_views_data); ?></div>
            </div>
            <div class="stat-card">
                <h3>Post Views</h3>
                <div class="stat-value"><?php echo $total_post_views; ?></div>
            </div>
            <div class="stat-card">
                <h3>Avg Views/Post</h3>
                <div class="stat-value"><?php echo $avg_post_views; ?></div>
            </div>
            <div class="stat-card">
                <h3>Most Viewed Post</h3>
                <div class="stat-value"><?php echo $most_viewed; ?></div>
            </div>
        </div>

        <!-- Downloads, registrations and page views chart -->
        <div class="chart-container">
            <h2>Downloads, Registrations & Page Views (<?php echo ucfirst($period); ?>ly)</h2>
            <canvas id="combinedChart"></canvas>
        </div>

        <!-- More charts -->
        <div class="chart-row">
            <div class="chart-container half">
                <h2>License Activation Rate</h2>
                <canvas id="activationChart"></canvas>
            </div>
            <div class="chart-container half">
                <h2>Growth Trends</h2>
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-container half">
                <h2>Community Post Types</h2>
                <canvas id="postTypeChart"></canvas>
            </div>
            <div class="chart-container half">
                <h2>Post Views by Type</h2>
                <canvas id="postViewsChart"></canvas>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-container half">
                <h2>Top 10 User Countries</h2>
                <canvas id="countryChart"></canvas>
            </div>
            <div class="chart-container half">
                <h2>Browser Distribution</h2>
                <canvas id="browserChart"></canvas>
            </div>
        </div>

        <!-- Most active users table -->
        <div class="table-container">
            <h2>Most Active Community Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Posts</th>
                        <th>Comments</th>
                        <th>Total Views</th>
                        <th>Activity Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['post_count']; ?></td>
                            <td><?php echo $user['comment_count']; ?></td>
                            <td><?php echo number_format($user['total_views']); ?></td>
                            <td><?php echo $user['activity_score']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Export options -->
        <div class="export-section">
            <h2>Export Statistics</h2>
            <p>Download statistics data for your records or further analysis.</p>
            <div class="export-buttons">
                <button id="exportCSV" class="btn">Export as CSV</button>
                <button id="exportJSON" class="btn">Export as JSON</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data
            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const downloadsData = <?php echo json_encode($downloads_data); ?>;
            const registrationsData = <?php echo json_encode($registrations_data); ?>;
            const pageViewsData = <?php echo json_encode($page_views_data); ?>;
            const activationData = <?php echo json_encode([
                                        $activation_rate['activated'],
                                        $activation_rate['total'] - $activation_rate['activated']
                                    ]); ?>;
            const postTypeLabels = <?php echo json_encode($post_type_labels); ?>;
            const postTypeCounts = <?php echo json_encode($post_type_counts); ?>;
            const postTypeViews = <?php echo json_encode($post_type_views); ?>;
            const countryLabels = <?php echo json_encode($country_labels); ?>;
            const countryCounts = <?php echo json_encode($country_counts); ?>;
            const browserLabels = <?php echo json_encode($browser_labels); ?>;
            const browserCounts = <?php echo json_encode($browser_counts); ?>;
            const conversionData = <?php echo json_encode([
                                        $conversion_data['downloads'],
                                        $conversion_data['registrations'],
                                        $conversion_data['licenses']
                                    ]); ?>;

            // Calculate growth data
            const growthData = [];
            for (let i = 1; i < downloadsData.length; i++) {
                const previous = downloadsData[i - 1];
                const current = downloadsData[i];
                const growth = previous > 0 ? ((current - previous) / previous) * 100 : 0;
                growthData.push(growth.toFixed(1));
            }

            // Combined chart - Downloads, Registrations and Page Views
            const ctxCombined = document.getElementById('combinedChart').getContext('2d');
            new Chart(ctxCombined, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                            label: 'Downloads',
                            backgroundColor: 'rgba(37, 99, 235, 0.2)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 2,
                            data: downloadsData,
                            borderRadius: 4,
                        },
                        {
                            label: 'Registrations',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            data: registrationsData,
                            borderRadius: 4,
                        },
                        {
                            label: 'Page Views',
                            backgroundColor: 'rgba(245, 158, 11, 0.2)',
                            borderColor: 'rgba(245, 158, 11, 1)',
                            borderWidth: 2,
                            data: pageViewsData,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                padding: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    layout: {
                        padding: {
                            bottom: 60
                        }
                    }
                }
            });

            // Activation rate chart (doughnut)
            const ctxActivation = document.getElementById('activationChart').getContext('2d');
            new Chart(ctxActivation, {
                type: 'doughnut',
                data: {
                    labels: ['Activated', 'Not Activated'],
                    datasets: [{
                        data: activationData,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 80
                        }
                    }
                }
            });

            // Growth chart
            const ctxGrowth = document.getElementById('growthChart').getContext('2d');
            new Chart(ctxGrowth, {
                type: 'line',
                data: {
                    labels: chartLabels.slice(1), // Remove first label
                    datasets: [{
                        label: 'Growth Rate (%)',
                        data: growthData,
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(37, 99, 235, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                precision: 0,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            ticks: {
                                padding: 10
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Growth: ${context.raw}%`;
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 80
                        }
                    }
                }
            });

            // Post Type chart (pie)
            const ctxPostType = document.getElementById('postTypeChart').getContext('2d');
            new Chart(ctxPostType, {
                type: 'pie',
                data: {
                    labels: postTypeLabels,
                    datasets: [{
                        data: postTypeCounts,
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(99, 102, 241, 0.8)'
                        ],
                        borderColor: [
                            'rgba(37, 99, 235, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(99, 102, 241, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 80
                        }
                    }
                }
            });

            // Post Views by Type chart (bar)
            const ctxPostViews = document.getElementById('postViewsChart').getContext('2d');
            new Chart(ctxPostViews, {
                type: 'bar',
                data: {
                    labels: postTypeLabels,
                    datasets: [{
                        label: 'Views',
                        data: postTypeViews,
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(99, 102, 241, 0.7)'
                        ],
                        borderColor: [
                            'rgba(37, 99, 235, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(99, 102, 241, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 60
                        }
                    }
                }
            });

            // Country chart (horizontal bar)
            const ctxCountry = document.getElementById('countryChart').getContext('2d');
            new Chart(ctxCountry, {
                type: 'bar',
                data: {
                    labels: countryLabels,
                    datasets: [{
                        label: 'Users',
                        data: countryCounts,
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 60
                        }
                    }
                }
            });

            // Browser chart (doughnut)
            const ctxBrowser = document.getElementById('browserChart').getContext('2d');
            new Chart(ctxBrowser, {
                type: 'doughnut',
                data: {
                    labels: browserLabels,
                    datasets: [{
                        data: browserCounts,
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(107, 114, 128, 0.7)'
                        ],
                        borderColor: [
                            'rgba(37, 99, 235, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(99, 102, 241, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(107, 114, 128, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '50%',
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 80
                        }
                    }
                }
            });

            // Export functions
            document.getElementById('exportCSV').addEventListener('click', function() {
                // Create CSV content
                let csvContent = 'data:text/csv;charset=utf-8,';
                csvContent += 'Period,Downloads,Registrations,PageViews\n';

                for (let i = 0; i < chartLabels.length; i++) {
                    const row = [
                        chartLabels[i],
                        downloadsData[i] || 0,
                        registrationsData[i] || 0,
                        pageViewsData[i] || 0
                    ].join(',');
                    csvContent += row + '\n';
                }

                // Create download link
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'argo_statistics.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            document.getElementById('exportJSON').addEventListener('click', function() {
                // Create JSON content
                const jsonData = {
                    time_series: [],
                    activation: {
                        activated: activationData[0],
                        not_activated: activationData[1],
                        percentage: <?php echo $activation_percentage; ?>
                    },
                    post_types: [],
                    countries: [],
                    browsers: [],
                    conversion: {
                        downloads: conversionData[0],
                        registrations: conversionData[1],
                        purchases: conversionData[2],
                    }
                };

                // Add time series data
                for (let i = 0; i < chartLabels.length; i++) {
                    jsonData.time_series.push({
                        period: chartLabels[i],
                        downloads: downloadsData[i] || 0,
                        registrations: registrationsData[i] || 0,
                        page_views: pageViewsData[i] || 0
                    });
                }

                // Add post type data
                for (let i = 0; i < postTypeLabels.length; i++) {
                    jsonData.post_types.push({
                        type: postTypeLabels[i],
                        count: postTypeCounts[i],
                        views: postTypeViews[i]
                    });
                }

                // Add country data
                for (let i = 0; i < countryLabels.length; i++) {
                    jsonData.countries.push({
                        country_code: countryLabels[i],
                        count: countryCounts[i]
                    });
                }

                // Add browser data
                for (let i = 0; i < browserLabels.length; i++) {
                    jsonData.browsers.push({
                        browser: browserLabels[i],
                        count: browserCounts[i]
                    });
                }

                const jsonString = JSON.stringify(jsonData, null, 2);
                const blob = new Blob([jsonString], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);

                // Create download link
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'argo_statistics.json');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });

            // Restore scroll position if it exists in sessionStorage
            if (sessionStorage.getItem('scrollPosition')) {
                window.scrollTo(0, sessionStorage.getItem('scrollPosition'));
                sessionStorage.removeItem('scrollPosition');
            }

            // Save scroll position when clicking links
            const links = document.querySelectorAll('a[href^="?period="]');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                });
            });
        });
    </script>
</body>

</html>