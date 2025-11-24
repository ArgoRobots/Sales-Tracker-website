<?php
session_start();
require_once '../../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set page variables for header
$page_title = "Argo Books Statistics";
$page_description = "View and analyze anonymous user data with geo-location insights from the Sales Tracker application";

$dataDir = '../data-logs/';
$errorMessage = '';
$aggregatedData = [
    'dataPoints' => [
        'Export' => [],
        'OpenAI' => [],
        'OpenExchangeRates' => [],
        'GoogleSheets' => [],
        'Session' => [],
        'Error' => []
    ],
    'geoLocationEnabled' => false,
    'privacySettings' => [
        'collectCityData' => true,
        'collectIPHashes' => false,
        'collectISPData' => true,
        'collectCoordinates' => false
    ]
];
$fileInfo = [];

if (!is_dir($dataDir)) {
    $errorMessage = "Directory 'data-logs/' does not exist.";
} else {
    $dataFiles = glob($dataDir . '*.json');
    if (!$dataFiles || count($dataFiles) === 0) {
        $errorMessage = "No anonymous data files found.";
    } else {
        // Sort files by modification time (newest first) for file info display
        usort($dataFiles, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $totalFiles = count($dataFiles);
        $processedFiles = 0;
        $failedFiles = 0;

        // Process all JSON files and aggregate the data
        foreach ($dataFiles as $file) {
            $jsonDataRaw = file_get_contents($file);
            if ($jsonDataRaw === false || trim($jsonDataRaw) === '') {
                $failedFiles++;
                continue;
            }

            $fileData = json_decode($jsonDataRaw, true);
            if ($fileData === null || !isset($fileData['dataPoints'])) {
                $failedFiles++;
                continue;
            }

            // Check if geo-location is enabled in this file
            if (isset($fileData['geoLocationEnabled']) && $fileData['geoLocationEnabled']) {
                $aggregatedData['geoLocationEnabled'] = true;
            }

            // Update privacy settings from latest file
            if (isset($fileData['privacySettings'])) {
                $aggregatedData['privacySettings'] = array_merge(
                    $aggregatedData['privacySettings'],
                    $fileData['privacySettings']
                );
            }

            // Merge data from this file into the aggregated data
            foreach ($fileData['dataPoints'] as $category => $dataPoints) {
                if (!isset($aggregatedData['dataPoints'][$category])) {
                    $aggregatedData['dataPoints'][$category] = [];
                }

                // Add data points with file source information
                foreach ($dataPoints as $dataPoint) {
                    $dataPoint['source_file'] = basename($file);
                    $aggregatedData['dataPoints'][$category][] = $dataPoint;
                }
            }

            $processedFiles++;
        }

        // Store file processing information
        $fileInfo = [
            'total_files' => $totalFiles,
            'processed_files' => $processedFiles,
            'failed_files' => $failedFiles,
            'latest_file' => $totalFiles > 0 ? basename($dataFiles[0]) : '',
            'latest_modified' => $totalFiles > 0 ? filemtime($dataFiles[0]) : 0,
            'oldest_file' => $totalFiles > 0 ? basename(end($dataFiles)) : '',
            'oldest_modified' => $totalFiles > 0 ? filemtime(end($dataFiles)) : 0
        ];

        // Sort all aggregated data by timestamp for chronological analysis
        foreach ($aggregatedData['dataPoints'] as $category => &$dataPoints) {
            usort($dataPoints, function ($a, $b) {
                $timeA = strtotime($a['timestamp'] ?? '1970-01-01');
                $timeB = strtotime($b['timestamp'] ?? '1970-01-01');
                return $timeA - $timeB;
            });
        }
        unset($dataPoints); // break reference

        if ($processedFiles === 0) {
            $errorMessage = "No valid JSON data found in any files.";
        }
    }
}

// Convert aggregated data to JSON for JavaScript
$jsonData = json_encode($aggregatedData);

// Include the shared header
include '../admin_header.php';
?>

<style>
.tabs-container {
    margin-bottom: 2rem;
}

.tab-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0;
}

.tab-button {
    padding: 12px 20px;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-bottom: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.tab-button:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab-button.active {
    background: white;
    color: #1f2937;
    border-color: #9ca3af;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: -2px;
    border-bottom: 2px solid white;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    text-align: center;
}

.stat-card h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0.5rem 0;
}

.stat-card .subtext {
    font-size: 0.75rem;
    color: #9ca3af;
    margin: 0;
}

.chart-section {
    margin-bottom: 3rem;
}

.section-title {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #374151;
    font-size: 1.5rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .tab-buttons {
        gap: 2px;
    }
    
    .tab-button {
        padding: 10px 14px;
        font-size: 13px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}
</style>

<div class="container">
    <?php if ($errorMessage): ?>
        <div class="error-message">
            <strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?>
            <br><small>Make sure the data directory exists and contains valid JSON files.</small>
        </div>
    <?php elseif (
        empty($aggregatedData['dataPoints']) ||
        (count($aggregatedData['dataPoints']['Export']) == 0 &&
            count($aggregatedData['dataPoints']['OpenAI']) == 0 &&
            count($aggregatedData['dataPoints']['OpenExchangeRates']) == 0 &&
            count($aggregatedData['dataPoints']['GoogleSheets']) == 0 &&
            count($aggregatedData['dataPoints']['Session']) == 0 &&
            count($aggregatedData['dataPoints']['Error']) == 0)
    ): ?>
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No anonymous data has been collected yet. Data will appear here once users start using the application and uploading their analytics.</p>
            <p><small>Data files are automatically uploaded to: <code>admin/data-logs/</code></small></p>
        </div>
    <?php else: ?>
        <div class="data-info">
            <strong>Data Summary:</strong>
            <?= $fileInfo['processed_files'] ?> files processed
            (<?= $fileInfo['total_files'] ?> total files)
            <?php if ($fileInfo['failed_files'] > 0): ?>
                | <?= $fileInfo['failed_files'] ?> files failed to process
            <?php endif; ?>
            <br>
            <strong>Latest Data:</strong> <?= date('M j, Y g:i A', $fileInfo['latest_modified']) ?>
            <?php if ($fileInfo['oldest_file'] !== $fileInfo['latest_file']): ?>
                | <strong>Oldest Data:</strong> <?= date('M j, Y g:i A', $fileInfo['oldest_modified']) ?>
            <?php endif; ?>
        </div>

        <div class="tabs-container">
            <div class="tab-buttons">
                <div class="tab-button active" onclick="switchTab('overview')">Overview</div>
                <?php if ($aggregatedData['geoLocationEnabled']): ?>
                <div class="tab-button" onclick="switchTab('geographic')">Geographic</div>
                <?php endif; ?>
                <div class="tab-button" onclick="switchTab('versions')">Versions</div>
                <div class="tab-button" onclick="switchTab('errors')">Errors</div>
                <div class="tab-button" onclick="switchTab('usage')">Usage</div>
                <div class="tab-button" onclick="switchTab('api')">API Usage</div>
                <div class="tab-button" onclick="switchTab('activity')">Activity</div>
            </div>

            <!-- Overview Tab -->
            <div id="overview-tab" class="tab-content active">
                <div class="stats-grid" id="statsGrid">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>

            <!-- Geographic Tab -->
            <?php if ($aggregatedData['geoLocationEnabled']): ?>
            <div id="geographic-tab" class="tab-content">
                <h2 class="section-title">Geographic Analytics</h2>
                
                <div class="chart-row">
                    <div class="chart-container">
                        <h2>User Distribution by Country</h2>
                        <canvas id="countryDistributionChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Top Cities</h2>
                        <canvas id="cityDistributionChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Feature Usage by Region</h2>
                        <canvas id="featureUsageByRegionChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Performance by Country</h2>
                        <canvas id="performanceByCountryChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Error Rates by Country</h2>
                        <canvas id="errorRatesByCountryChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Session Duration by Region</h2>
                        <canvas id="sessionDurationByRegionChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Timezone Distribution</h2>
                        <canvas id="timezoneChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Versions Tab -->
            <div id="versions-tab" class="tab-content">
                <h2 class="section-title">Version Analytics</h2>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Version Distribution</h2>
                        <canvas id="versionDistributionChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Version Usage Over Time</h2>
                        <canvas id="versionTimeChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Most Active Versions</h2>
                        <canvas id="topVersionsChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Version Performance Comparison</h2>
                        <canvas id="versionPerformanceChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Session Duration by Version</h2>
                        <canvas id="versionSessionChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Error Rate by Version</h2>
                        <canvas id="versionErrorChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Errors Tab -->
            <div id="errors-tab" class="tab-content">
                <h2 class="section-title">Error Analysis</h2>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Errors by Category</h2>
                        <canvas id="errorCategoryChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Most Common Error Codes</h2>
                        <canvas id="errorCodeChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Error Frequency Over Time</h2>
                        <canvas id="errorTimeChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Application Stability Overview</h2>
                        <canvas id="stabilityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Usage Tab -->
            <div id="usage-tab" class="tab-content">
                <h2 class="section-title">Usage Analytics</h2>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Average Session Duration</h2>
                        <canvas id="sessionDurationChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Export Types Distribution</h2>
                        <canvas id="exportTypesGrid"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Average Duration by Export Type</h2>
                        <canvas id="exportDurationByTypeChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Average File Size by Export Type</h2>
                        <canvas id="exportFileSizeByTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- API Usage Tab -->
            <div id="api-tab" class="tab-content">
                <h2 class="section-title">API Usage</h2>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>OpenAI API Usage</h2>
                        <canvas id="openaiChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>OpenAI Token Usage</h2>
                        <canvas id="openaiTokenChart"></canvas>
                    </div>
                </div>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Export Durations Over Time</h2>
                        <canvas id="exportDurationChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Exchange Rates API Usage</h2>
                        <canvas id="exchangeRatesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div id="activity-tab" class="tab-content">
                <h2 class="section-title">Overall Activity</h2>

                <div class="chart-row">
                    <div class="chart-container">
                        <h2>Export File Sizes</h2>
                        <canvas id="exportFileSizeChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Data Points Over Time</h2>
                        <canvas id="overallActivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Pass PHP data to JavaScript
window.dashboardData = <?= $jsonData ?>;
</script>
<script src="main.js"></script>