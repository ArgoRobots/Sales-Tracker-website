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

// Get statistics by period (default to month)
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$allowed_periods = ['day', 'week', 'month', 'year'];
if (!in_array($period, $allowed_periods)) {
    $period = 'month';
}

$downloads = get_downloads_by_period($period);
$registrations = get_registrations_by_period($period);
$activation_rate = get_activation_rate();

// Prepare data for charts
$chart_labels = [];
$downloads_data = [];
$registrations_data = [];

// Reverse arrays to show chronological order
$downloads = array_reverse($downloads);
$registrations = array_reverse($registrations);

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
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/notifications/notifications.css">
    <style>
        /* Additional styles for statistics page */
        .period-selection {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .period-selection span {
            font-weight: 500;
            margin-right: 15px;
        }

        .period-buttons {
            display: flex;
            gap: 10px;
        }

        .period-btn {
            padding: 8px 15px;
            background: #f3f4f6;
            border-radius: 4px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .period-btn:hover {
            background: #e5e7eb;
        }

        .period-btn.active {
            background: var(--button);
            color: white;
            border: 2px solid #1e40af;
            font-weight: bold;
        }

        .chart-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container.half {
            flex: 1;
            min-width: 300px;
            height: 300px;
        }

        .export-section {
            background: white;
            border-radius: 8px;
            padding: 30px 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .export-section h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .export-section p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .export-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .chart-row {
                flex-direction: column;
                gap: 20px;
            }

            .chart-container.half {
                width: 100%;
            }

            .period-selection {
                flex-direction: column;
                align-items: flex-start;
            }

            .period-selection span {
                margin-bottom: 10px;
            }

            .period-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 10px;
            }

            .period-btn {
                text-align: center;
            }

            .export-buttons {
                flex-direction: column;
                align-items: center;
            }

            .export-buttons .btn {
                width: 200px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Statistics Dashboard</h1>
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

        <!-- Statistics overview -->
        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Downloads</h3>
                <div class="stat-value"><?php echo array_sum($downloads_data); ?></div>
            </div>
            <div class="stat-card">
                <h3>Registration Rate</h3>
                <div class="stat-value"><?php echo array_sum($registrations_data); ?></div>
            </div>
            <div class="stat-card">
                <h3>Activation Rate</h3>
                <div class="stat-value"><?php echo $activation_percentage; ?>%</div>
            </div>
            <div class="stat-card">
                <h3>Latest Period Growth</h3>
                <div class="stat-value"><?php echo ($latest_growth >= 0 ? '+' : '') . $latest_growth; ?>%</div>
            </div>
        </div>

        <!-- Combined chart -->
        <div class="chart-container">
            <h2>Downloads & Registrations (<?php echo ucfirst($period); ?>ly)</h2>
            <canvas id="combinedChart"></canvas>
        </div>

        <!-- Two charts side by side -->
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
            const activationData = <?php echo json_encode([
                                        $activation_rate['activated'],
                                        $activation_rate['total'] - $activation_rate['activated']
                                    ]); ?>;

            // Calculate growth data
            const growthData = [];
            for (let i = 1; i < downloadsData.length; i++) {
                const previous = downloadsData[i - 1];
                const current = downloadsData[i];
                const growth = previous > 0 ? ((current - previous) / previous) * 100 : 0;
                growthData.push(growth.toFixed(1));
            }

            // Combined chart
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
                            position: 'bottom',
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
                            bottom: 60
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
                            bottom: 60
                        }
                    }
                }
            });

            // Export functions
            document.getElementById('exportCSV').addEventListener('click', function() {
                // Create CSV content
                let csvContent = 'data:text/csv;charset=utf-8,';
                csvContent += 'Period,Downloads,Registrations\n';

                for (let i = 0; i < chartLabels.length; i++) {
                    const row = [
                        chartLabels[i],
                        downloadsData[i] || 0,
                        registrationsData[i] || 0
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
                const jsonData = [];

                for (let i = 0; i < chartLabels.length; i++) {
                    jsonData.push({
                        period: chartLabels[i],
                        downloads: downloadsData[i] || 0,
                        registrations: registrationsData[i] || 0
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