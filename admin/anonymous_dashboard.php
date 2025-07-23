<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set page variables for header
$page_title = "Anonymous Data Dashboard";
$page_description = "View and analyze anonymous user data with geo-location insights from the Sales Tracker application";

$dataDir = 'data_logs/';
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
    $errorMessage = "Directory 'data_logs/' does not exist.";
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
include 'admin_header.php';
?>
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
            <p><small>Data files are automatically uploaded to: <code>admin/data_logs/</code></small></p>
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

        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsGrid">
            <!-- Will be populated by JavaScript -->
        </div>

        <!-- Geo-Location Analysis Section -->
        <?php if ($aggregatedData['geoLocationEnabled']): ?>
            <div class="geo-section">
                <h2 style="text-align: center; margin-bottom: 1.5rem; color: #374151;">🌍 Geographic Analytics</h2>

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
                        <h2>VPN/Proxy Usage</h2>
                        <canvas id="vpnUsageChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h2>Timezone Distribution</h2>
                        <canvas id="timezoneChart"></canvas>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Analysis Section -->
        <div class="error-section">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #374151;">🐛 Error Analysis</h2>

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

        <!-- Usage Analysis Section -->
        <div class="usage-section">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #374151;">📊 Usage Analytics</h2>

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

        <!-- API Usage Section -->
        <div class="api-section">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #374151;">🔌 API Usage</h2>

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

        <!-- Overall Activity Section -->
        <div class="activity-section">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #374151;">📈 Overall Activity</h2>

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
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawData = <?= $jsonData ?>;
        const isGeoEnabled = rawData.geoLocationEnabled || false;
        const privacySettings = rawData.privacySettings || {};

        const typeColors = {
            'ExcelSheetsChart': '#3b82f6',
            'GoogleSheetsChart': '#10b981',
            'Backup': '#f59e0b',
            'XLSX': '#ef4444',
            'Receipts': '#8b5cf6'
        };

        const countryColors = {
            'United States': '#3b82f6',
            'Canada': '#ef4444',
            'United Kingdom': '#10b981',
            'Germany': '#f59e0b',
            'Australia': '#8b5cf6',
            'France': '#06b6d4',
            'Netherlands': '#84cc16',
            'Japan': '#f97316',
            'Brazil': '#ec4899',
            'India': '#6366f1'
        };

        if (!rawData.dataPoints) {
            console.log('No data points available');
            return;
        }

        const exportData = rawData.dataPoints.Export || [];
        const openaiData = rawData.dataPoints.OpenAI || [];
        const exchangeRatesData = rawData.dataPoints.OpenExchangeRates || [];
        const googleSheetsData = rawData.dataPoints.GoogleSheets || [];
        const sessionData = rawData.dataPoints.Session || [];
        const errorData = rawData.dataPoints.Error || [];

        console.log('Data loaded:', {
            exports: exportData.length,
            openai: openaiData.length,
            exchangeRates: exchangeRatesData.length,
            googleSheets: googleSheetsData.length,
            sessions: sessionData.length,
            errors: errorData.length,
            geoEnabled: isGeoEnabled
        });

        // Generate statistics
        generateStatistics(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);

        // Generate geo-location charts if enabled
        if (isGeoEnabled) {
            generateCountryDistributionChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);
            generateCityDistributionChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);
            generateFeatureUsageByRegionChart(exportData, openaiData, exchangeRatesData);
            generatePerformanceByCountryChart(exportData, openaiData, exchangeRatesData);
            generateErrorRatesByCountryChart(errorData, exportData, openaiData, exchangeRatesData);
            generateSessionDurationByRegionChart(sessionData);
            generateVPNUsageChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);
            generateTimezoneChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);
        }

        // Generate error charts
        generateErrorCategoryChart(errorData);
        generateErrorCodeChart(errorData);
        generateErrorTimeChart(errorData);
        generateStabilityChart(exportData, openaiData, exchangeRatesData, sessionData, errorData);

        // Generate usage charts
        generateSessionDurationChart(sessionData);
        generateExportTypesBreakdown(exportData);
        generateExportDurationByTypeChart(exportData);
        generateExportFileSizeByTypeChart(exportData);

        // Generate performance charts
        generateExportDurationChart(exportData);
        generateOpenAIChart(openaiData);
        generateOpenAITokenChart(openaiData);
        generateExchangeRatesChart(exchangeRatesData);

        // Generate overall activity
        generateExportFileSizeChart(exportData);
        generateOverallActivityChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData);

        function generateStatistics(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            const statsGrid = document.getElementById('statsGrid');

            // Calculate statistics
            const totalExports = exportData.length;
            const totalOpenAI = openaiData.length;
            const totalExchangeRates = exchangeRatesData.length;
            const totalGoogleSheets = googleSheetsData.length;
            const totalSessions = sessionData.length;
            const totalErrors = errorData.length;

            const avgExportDuration = exportData.length > 0 ?
                exportData.reduce((sum, item) => sum + parseFloat(item.DurationMS || 0), 0) / exportData.length : 0;

            const totalTokens = openaiData.reduce((sum, item) => sum + parseInt(item.TokensUsed || 0), 0);

            const sessionDurations = sessionData.map(d => parseFloat(d.duration || 0)).filter(d => d > 0);
            const avgSessionDuration = sessionDurations.length > 0 ?
                sessionDurations.reduce((sum, duration) => sum + duration, 0) / sessionDurations.length : 0;

            // Calculate error rate
            const totalOperations = totalExports + totalOpenAI + totalExchangeRates + totalGoogleSheets;
            const errorRate = totalOperations > 0 ? ((totalErrors / totalOperations) * 100).toFixed(2) : 0;

            // Count unique countries
            const uniqueCountries = new Set();
            const allDataWithCountry = [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData, ...sessionData, ...errorData];
            allDataWithCountry.forEach(item => {
                if (item.country && item.country !== 'Unknown') {
                    uniqueCountries.add(item.country);
                }
            });

            // Count VPN users
            const vpnUsers = allDataWithCountry.filter(item => item.isVPN === true).length;

            const stats = [{
                    title: 'Total Errors',
                    value: totalErrors.toLocaleString(),
                    subtext: errorData.length > 0 ? 'incidents reported' : 'No errors 🎉'
                },
                {
                    title: 'Error Rate',
                    value: errorRate + '%',
                    subtext: totalOperations > 0 ? 'errors per operation' : 'No data'
                },
                {
                    title: 'Countries',
                    value: uniqueCountries.size > 0 ? uniqueCountries.size.toString() : 'Unknown',
                    subtext: isGeoEnabled ? 'unique locations' : 'Geo tracking disabled'
                },
                {
                    title: 'Total Exports',
                    value: totalExports.toLocaleString(),
                    subtext: 'operations'
                },
                {
                    title: 'OpenAI Calls',
                    value: totalOpenAI.toLocaleString(),
                    subtext: openaiData.length > 0 ? `${totalTokens.toLocaleString()} tokens` : 'No data'
                },
                {
                    title: 'User Sessions',
                    value: totalSessions.toLocaleString(),
                    subtext: sessionData.length > 0 ? `${Math.round(avgSessionDuration)}s avg` : 'No data'
                },
                {
                    title: 'Exchange Rate Calls',
                    value: totalExchangeRates.toLocaleString(),
                    subtext: exchangeRatesData.length > 0 ? 'API requests' : 'No data'
                },
                {
                    title: 'Avg Export Time',
                    value: Math.round(avgExportDuration) + ' ms',
                    subtext: exportData.length > 0 ? 'processing time' : 'No data'
                },
                {
                    title: 'VPN/Proxy Users',
                    value: vpnUsers.toString(),
                    subtext: isGeoEnabled ? 'detected' : 'Not tracked'
                }
            ];

            statsGrid.innerHTML = stats.map(stat => `
                <div class="stat-card">
                    <h3>${stat.title}</h3>
                    <div class="value">${stat.value}</div>
                    ${stat.subtext ? `<div class="subtext">${stat.subtext}</div>` : ''}
                </div>
            `).join('');
        }

        // Geo-location chart functions
        function generateCountryDistributionChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            const allData = [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData, ...sessionData, ...errorData];
            const countryCounts = {};

            allData.forEach(item => {
                const country = item.country || 'Unknown';
                if (country !== 'Unknown') {
                    countryCounts[country] = (countryCounts[country] || 0) + 1;
                }
            });

            if (Object.keys(countryCounts).length === 0) {
                document.getElementById('countryDistributionChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No country data available</div>';
                return;
            }

            const sortedCountries = Object.entries(countryCounts)
                .sort(([, a], [, b]) => b - a)
                .slice(0, 15); // Top 15 countries

            const labels = sortedCountries.map(([country]) => country);
            const data = sortedCountries.map(([, count]) => count);
            const colors = labels.map(country => countryColors[country] || '#9ca3af');

            new Chart(document.getElementById("countryDistributionChart"), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Operations',
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Operations'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateCityDistributionChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            if (!privacySettings.collectCityData) {
                document.getElementById('cityDistributionChart').parentElement.innerHTML =
                    '<div class="chart-no-data">City data collection disabled for privacy</div>';
                return;
            }

            const allData = [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData, ...sessionData, ...errorData];
            const cityCounts = {};

            allData.forEach(item => {
                const city = item.city || 'Unknown';
                if (city !== 'Unknown' && city !== 'Hidden') {
                    const region = item.region || '';
                    const country = item.country || '';
                    const fullLocation = region ? `${city}, ${region}, ${country}` : `${city}, ${country}`;
                    cityCounts[fullLocation] = (cityCounts[fullLocation] || 0) + 1;
                }
            });

            if (Object.keys(cityCounts).length === 0) {
                document.getElementById('cityDistributionChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No city data available</div>';
                return;
            }

            const sortedCities = Object.entries(cityCounts)
                .sort(([, a], [, b]) => b - a)
                .slice(0, 10); // Top 10 cities

            new Chart(document.getElementById("cityDistributionChart"), {
                type: 'pie',
                data: {
                    labels: sortedCities.map(([city]) => city),
                    datasets: [{
                        data: sortedCities.map(([, count]) => count),
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateFeatureUsageByRegionChart(exportData, openaiData, exchangeRatesData) {
            const regionData = {};

            const addToRegion = (item, type) => {
                const country = item.country || 'Unknown';
                if (country !== 'Unknown') {
                    if (!regionData[country]) {
                        regionData[country] = {
                            exports: 0,
                            openai: 0,
                            exchangeRates: 0
                        };
                    }
                    regionData[country][type]++;
                }
            };

            exportData.forEach(item => addToRegion(item, 'exports'));
            openaiData.forEach(item => addToRegion(item, 'openai'));
            exchangeRatesData.forEach(item => addToRegion(item, 'exchangeRates'));

            if (Object.keys(regionData).length === 0) {
                document.getElementById('featureUsageByRegionChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No regional feature usage data available</div>';
                return;
            }

            const countries = Object.keys(regionData).slice(0, 10); // Top 10 countries

            new Chart(document.getElementById("featureUsageByRegionChart"), {
                type: 'bar',
                data: {
                    labels: countries,
                    datasets: [{
                            label: 'Exports',
                            data: countries.map(country => regionData[country].exports),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'OpenAI',
                            data: countries.map(country => regionData[country].openai),
                            backgroundColor: '#8b5cf6'
                        },
                        {
                            label: 'Exchange Rates',
                            data: countries.map(country => regionData[country].exchangeRates),
                            backgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generatePerformanceByCountryChart(exportData, openaiData, exchangeRatesData) {
            const countryPerformance = {};

            const addPerformanceData = (items, type) => {
                items.forEach(item => {
                    const country = item.country || 'Unknown';
                    const duration = parseFloat(item.DurationMS || 0);

                    if (country !== 'Unknown' && duration > 0) {
                        if (!countryPerformance[country]) {
                            countryPerformance[country] = [];
                        }
                        countryPerformance[country].push(duration);
                    }
                });
            };

            addPerformanceData(exportData, 'export');
            addPerformanceData(openaiData, 'openai');
            addPerformanceData(exchangeRatesData, 'exchangeRates');

            if (Object.keys(countryPerformance).length === 0) {
                document.getElementById('performanceByCountryChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No performance data by country available</div>';
                return;
            }

            // Calculate average performance per country
            const countryAverages = Object.entries(countryPerformance)
                .map(([country, durations]) => ({
                    country,
                    avgDuration: durations.reduce((sum, d) => sum + d, 0) / durations.length,
                    count: durations.length
                }))
                .filter(item => item.count >= 5) // Only countries with at least 5 operations
                .sort((a, b) => a.avgDuration - b.avgDuration)
                .slice(0, 10);

            new Chart(document.getElementById("performanceByCountryChart"), {
                type: 'bar',
                data: {
                    labels: countryAverages.map(item => item.country),
                    datasets: [{
                        label: 'Average Duration (ms)',
                        data: countryAverages.map(item => Math.round(item.avgDuration)),
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Average Duration (ms)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateErrorRatesByCountryChart(errorData, exportData, openaiData, exchangeRatesData) {
            if (errorData.length === 0) {
                document.getElementById('errorRatesByCountryChart').parentElement.innerHTML =
                    '<div class="chart-no-data"><h3>🎉 No Errors by Country!</h3><p>All regions running smoothly</p></div>';
                return;
            }

            const countryErrors = {};
            const countryOperations = {};

            // Count errors by country
            errorData.forEach(error => {
                const country = error.country || 'Unknown';
                if (country !== 'Unknown') {
                    countryErrors[country] = (countryErrors[country] || 0) + 1;
                }
            });

            // Count total operations by country
            [...exportData, ...openaiData, ...exchangeRatesData].forEach(item => {
                const country = item.country || 'Unknown';
                if (country !== 'Unknown') {
                    countryOperations[country] = (countryOperations[country] || 0) + 1;
                }
            });

            // Calculate error rates
            const countryErrorRates = Object.keys(countryErrors)
                .map(country => ({
                    country,
                    errorRate: ((countryErrors[country] || 0) / (countryOperations[country] || 1)) * 100,
                    errors: countryErrors[country] || 0,
                    operations: countryOperations[country] || 0
                }))
                .filter(item => item.operations >= 5) // Only countries with significant operations
                .sort((a, b) => b.errorRate - a.errorRate);

            new Chart(document.getElementById("errorRatesByCountryChart"), {
                type: 'bar',
                data: {
                    labels: countryErrorRates.map(item => item.country),
                    datasets: [{
                        label: 'Error Rate (%)',
                        data: countryErrorRates.map(item => item.errorRate.toFixed(2)),
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = countryErrorRates[context.dataIndex];
                                    return `${item.country}: ${item.errorRate.toFixed(2)}% (${item.errors}/${item.operations})`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Error Rate (%)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateSessionDurationByRegionChart(sessionData) {
            const sessionEndData = sessionData.filter(s => s.action === 'SessionEnd' && s.duration > 0);

            if (sessionEndData.length === 0) {
                document.getElementById('sessionDurationByRegionChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No session duration data by region available</div>';
                return;
            }

            const regionDurations = {};
            sessionEndData.forEach(session => {
                const country = session.country || 'Unknown';
                if (country !== 'Unknown') {
                    if (!regionDurations[country]) {
                        regionDurations[country] = [];
                    }
                    regionDurations[country].push(parseFloat(session.duration));
                }
            });

            const regionAverages = Object.entries(regionDurations)
                .map(([country, durations]) => ({
                    country,
                    avgDuration: durations.reduce((sum, d) => sum + d, 0) / durations.length,
                    count: durations.length
                }))
                .filter(item => item.count >= 2) // At least 2 sessions
                .sort((a, b) => b.avgDuration - a.avgDuration);

            new Chart(document.getElementById("sessionDurationByRegionChart"), {
                type: 'bar',
                data: {
                    labels: regionAverages.map(item => item.country),
                    datasets: [{
                        label: 'Average Session Duration (seconds)',
                        data: regionAverages.map(item => Math.round(item.avgDuration)),
                        backgroundColor: '#06b6d4',
                        borderColor: '#0891b2',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Average Duration (seconds)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateVPNUsageChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            const allData = [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData, ...sessionData, ...errorData];
            const vpnCount = allData.filter(item => item.isVPN === true).length;
            const regularCount = allData.filter(item => item.isVPN === false || item.isVPN === undefined).length;

            if (vpnCount === 0 && regularCount === 0) {
                document.getElementById('vpnUsageChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No VPN detection data available</div>';
                return;
            }

            new Chart(document.getElementById("vpnUsageChart"), {
                type: 'doughnut',
                data: {
                    labels: ['Regular Connections', 'VPN/Proxy Connections'],
                    datasets: [{
                        data: [regularCount, vpnCount],
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderColor: ['#059669', '#d97706'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateTimezoneChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            const allData = [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData, ...sessionData, ...errorData];
            const timezoneCounts = {};

            allData.forEach(item => {
                const timezone = item.timezone || 'Unknown';
                if (timezone !== 'Unknown') {
                    timezoneCounts[timezone] = (timezoneCounts[timezone] || 0) + 1;
                }
            });

            if (Object.keys(timezoneCounts).length === 0) {
                document.getElementById('timezoneChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No timezone data available</div>';
                return;
            }

            const sortedTimezones = Object.entries(timezoneCounts)
                .sort(([, a], [, b]) => b - a)
                .slice(0, 10); // Top 10 timezones

            new Chart(document.getElementById("timezoneChart"), {
                type: 'bar',
                data: {
                    labels: sortedTimezones.map(([tz]) => tz.replace('/', '/\n')), // Line break for readability
                    datasets: [{
                        label: 'Operations',
                        data: sortedTimezones.map(([, count]) => count),
                        backgroundColor: '#6366f1',
                        borderColor: '#4f46e5',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Operations'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        // All the existing chart functions remain the same...
        // (generateErrorCategoryChart, generateErrorCodeChart, etc.)
        // I'll include a few key ones to show the pattern:

        function generateErrorCategoryChart(errorData) {
            if (errorData.length === 0) {
                document.getElementById('errorCategoryChart').parentElement.innerHTML =
                    '<div class="chart-no-data"><h3>🎉 No Errors Detected!</h3><p>Your application is running smoothly</p></div>';
                return;
            }

            const categoryCounts = {};
            errorData.forEach(error => {
                const category = error.ErrorCategory || 'Unknown';
                categoryCounts[category] = (categoryCounts[category] || 0) + 1;
            });

            const sortedCategories = Object.entries(categoryCounts).sort(([, a], [, b]) => b - a);
            const labels = sortedCategories.map(([category]) => category);
            const data = sortedCategories.map(([, count]) => count);
            const colors = ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e', '#06b6d4', '#6366f1', '#8b5cf6'];

            new Chart(document.getElementById("errorCategoryChart"), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw} errors (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateErrorCodeChart(errorData) {
            if (errorData.length === 0) {
                document.getElementById('errorCodeChart').parentElement.innerHTML =
                    '<div class="chart-no-data"><h3>🎉 No Error Codes!</h3><p>Clean codebase detected</p></div>';
                return;
            }

            const codeCounts = {};
            errorData.forEach(error => {
                const code = error.ErrorCode || 'Unknown';
                codeCounts[code] = (codeCounts[code] || 0) + 1;
            });

            const sortedCodes = Object.entries(codeCounts)
                .sort(([, a], [, b]) => b - a)
                .slice(0, 10);

            new Chart(document.getElementById("errorCodeChart"), {
                type: 'bar',
                data: {
                    labels: sortedCodes.map(([code]) => code),
                    datasets: [{
                        label: 'Error Occurrences',
                        data: sortedCodes.map(([, count]) => count),
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Occurrences'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateErrorTimeChart(errorData) {
            if (errorData.length === 0) {
                document.getElementById('errorTimeChart').parentElement.innerHTML =
                    '<div class="chart-no-data"><h3>📈 Stable Performance</h3><p>No error trends to display</p></div>';
                return;
            }

            const dailyErrors = {};
            errorData.forEach(error => {
                const date = error.timestamp.split(' ')[0];
                dailyErrors[date] = (dailyErrors[date] || 0) + 1;
            });

            const dates = Object.keys(dailyErrors).sort();
            const errorCounts = dates.map(date => dailyErrors[date]);

            new Chart(document.getElementById("errorTimeChart"), {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Error Count',
                        data: errorCounts,
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Error Count'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateStabilityChart(exportData, openaiData, exchangeRatesData, sessionData, errorData) {
            const totalOperations = exportData.length + openaiData.length + exchangeRatesData.length + sessionData.length;
            const totalErrors = errorData.length;
            const successfulOperations = totalOperations - totalErrors;

            if (totalOperations === 0) {
                document.getElementById('stabilityChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No operations data available</div>';
                return;
            }

            new Chart(document.getElementById("stabilityChart"), {
                type: 'doughnut',
                data: {
                    labels: ['Successful Operations', 'Errors'],
                    datasets: [{
                        data: [successfulOperations, totalErrors],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderColor: ['#059669', '#dc2626'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateSessionDurationChart(sessionData) {
            const sessionEndData = sessionData.filter(s => s.action === 'SessionEnd' && s.duration > 0);

            if (sessionEndData.length === 0) {
                document.getElementById('sessionDurationChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No session duration data available</div>';
                return;
            }

            const dailyStats = {};
            sessionEndData.forEach(session => {
                const date = session.timestamp.split(' ')[0];
                if (!dailyStats[date]) {
                    dailyStats[date] = {
                        totalDuration: 0,
                        count: 0
                    };
                }
                dailyStats[date].totalDuration += parseFloat(session.duration);
                dailyStats[date].count++;
            });

            const dates = Object.keys(dailyStats).sort();
            const avgDurations = dates.map(date => Math.round(dailyStats[date].totalDuration / dailyStats[date].count));

            new Chart(document.getElementById("sessionDurationChart"), {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Average Session Duration (seconds)',
                        data: avgDurations,
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (seconds)'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExportTypesBreakdown(exportData) {
            if (exportData.length === 0) {
                document.getElementById('exportTypesGrid').parentElement.innerHTML =
                    '<div class="chart-no-data">No export data available</div>';
                return;
            }

            const typeCounts = {};
            exportData.forEach(item => {
                const type = item.ExportType || 'Unknown';
                typeCounts[type] = (typeCounts[type] || 0) + 1;
            });

            const sortedTypes = Object.entries(typeCounts).sort(([, a], [, b]) => b - a);
            const labels = sortedTypes.map(([type]) => type);
            const data = sortedTypes.map(([, count]) => count);
            const colors = labels.map(type => typeColors[type] || '#9ca3af');

            new Chart(document.getElementById("exportTypesGrid"), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw} exports (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExportDurationByTypeChart(exportData) {
            if (exportData.length === 0) {
                document.getElementById('exportDurationByTypeChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No export data available</div>';
                return;
            }

            const typeAverages = {};
            const typeCounts = {};

            exportData.forEach(item => {
                const type = item.ExportType || 'Unknown';
                const duration = item.DurationMS;
                const durationValue = typeof duration === 'string' ?
                    parseFloat(duration.replace(/[^\d.]/g, '')) : parseInt(duration) || 0;

                if (!typeAverages[type]) {
                    typeAverages[type] = 0;
                    typeCounts[type] = 0;
                }
                typeAverages[type] += durationValue;
                typeCounts[type]++;
            });

            const labels = [];
            const averages = [];
            const colors = [];

            for (const type in typeAverages) {
                if (typeCounts[type] > 0) {
                    labels.push(type);
                    averages.push(Math.round(typeAverages[type] / typeCounts[type]));
                    colors.push(typeColors[type] || '#9ca3af');
                }
            }

            new Chart(document.getElementById("exportDurationByTypeChart"), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Duration (ms)',
                        data: averages,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.6', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (ms)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExportFileSizeByTypeChart(exportData) {
            const filteredData = exportData
                .filter(d => d.FileSize && d.FileSize !== 'null' && d.FileSize !== null)
                .map(d => ({
                    type: d.ExportType || 'Unknown',
                    size: parseInt(d.FileSize) || 0
                }))
                .filter(item => item.size > 0);

            if (filteredData.length === 0) {
                document.getElementById('exportFileSizeByTypeChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No file size data available</div>';
                return;
            }

            const typeAverages = {};
            const typeCounts = {};

            filteredData.forEach(item => {
                if (!typeAverages[item.type]) {
                    typeAverages[item.type] = 0;
                    typeCounts[item.type] = 0;
                }
                typeAverages[item.type] += item.size;
                typeCounts[item.type]++;
            });

            const labels = [];
            const averages = [];
            const colors = [];

            for (const type in typeAverages) {
                if (typeCounts[type] > 0) {
                    labels.push(type);
                    averages.push(Math.round(typeAverages[type] / typeCounts[type]));
                    colors.push(typeColors[type] || '#9ca3af');
                }
            }

            new Chart(document.getElementById("exportFileSizeByTypeChart"), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average File Size (bytes)',
                        data: averages,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.6', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'File Size (bytes)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1048576) return (value / 1048576).toFixed(1) + 'MB';
                                    if (value >= 1024) return (value / 1024).toFixed(1) + 'KB';
                                    return value + 'B';
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExportDurationChart(exportData) {
            if (exportData.length === 0) {
                document.getElementById('exportDurationChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No export data available</div>';
                return;
            }

            const recentData = exportData.slice(-100);
            const labels = recentData.map((d, index) => {
                const date = new Date(d.timestamp);
                return recentData.length > 50 ?
                    (index % Math.ceil(recentData.length / 20) === 0 ? date.toLocaleDateString() : '') :
                    date.toLocaleString();
            });

            const durations = recentData.map(d => {
                const duration = d.DurationMS;
                return typeof duration === 'string' ?
                    parseFloat(duration.replace(/[^\d.]/g, '')) : parseInt(duration) || 0;
            });

            new Chart(document.getElementById("exportDurationChart"), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Duration (ms)',
                        data: durations,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: exportData.length > 50 ? 1 : 3,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            ticks: {
                                maxTicksLimit: 15,
                                maxRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (milliseconds)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateOpenAIChart(openaiData) {
            if (openaiData.length === 0) {
                document.getElementById('openaiChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No OpenAI data available</div>';
                return;
            }

            const modelCounts = {};
            openaiData.forEach(item => {
                const model = item.Model || 'Unknown';
                modelCounts[model] = (modelCounts[model] || 0) + 1;
            });

            new Chart(document.getElementById("openaiChart"), {
                type: 'pie',
                data: {
                    labels: Object.keys(modelCounts),
                    datasets: [{
                        data: Object.values(modelCounts),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateOpenAITokenChart(openaiData) {
            if (openaiData.length === 0) {
                document.getElementById('openaiTokenChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No OpenAI token data available</div>';
                return;
            }

            const recentData = openaiData.slice(-50);
            const labels = recentData.map((d, index) => `Call ${index + 1}`);
            const tokens = recentData.map(d => parseInt(d.TokensUsed) || 0);

            new Chart(document.getElementById("openaiTokenChart"), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tokens Used',
                        data: tokens,
                        backgroundColor: '#8b5cf6',
                        borderColor: '#7c3aed',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Tokens'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExchangeRatesChart(exchangeRatesData) {
            if (exchangeRatesData.length === 0) {
                document.getElementById('exchangeRatesChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No exchange rates data available</div>';
                return;
            }

            const recentData = exchangeRatesData.slice(-30);
            const labels = recentData.map(d => new Date(d.timestamp).toLocaleDateString());
            const durations = recentData.map(d => parseInt(d.DurationMS) || 0);

            new Chart(document.getElementById("exchangeRatesChart"), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: durations,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (ms)'
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateExportFileSizeChart(exportData) {
            const fileSizes = exportData
                .filter(d => d.FileSize && d.FileSize !== 'null' && d.FileSize !== null)
                .map(d => ({
                    size: parseInt(d.FileSize) || 0,
                    type: d.ExportType || 'Unknown'
                }))
                .filter(item => item.size > 0);

            if (fileSizes.length === 0) {
                document.getElementById('exportFileSizeChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No file size data available</div>';
                return;
            }

            const datasets = [];
            const types = [...new Set(fileSizes.map(item => item.type))];

            types.forEach(type => {
                const typeData = fileSizes.filter(item => item.type === type);
                datasets.push({
                    label: type,
                    data: typeData.map((item, index) => ({
                        x: index + 1,
                        y: item.size
                    })),
                    backgroundColor: typeColors[type] || '#9ca3af',
                    borderColor: typeColors[type] || '#9ca3af',
                    pointRadius: 4
                });
            });

            new Chart(document.getElementById("exportFileSizeChart"), {
                type: 'scatter',
                data: {
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Export Number'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'File Size (bytes)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1048576) return (value / 1048576).toFixed(1) + 'MB';
                                    if (value >= 1024) return (value / 1024).toFixed(1) + 'KB';
                                    return value + 'B';
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }

        function generateOverallActivityChart(exportData, openaiData, exchangeRatesData, googleSheetsData, sessionData, errorData) {
            const allData = [
                ...exportData.map(d => ({
                    ...d,
                    type: 'Export'
                })),
                ...openaiData.map(d => ({
                    ...d,
                    type: 'OpenAI'
                })),
                ...exchangeRatesData.map(d => ({
                    ...d,
                    type: 'Exchange Rates'
                })),
                ...googleSheetsData.map(d => ({
                    ...d,
                    type: 'Google Sheets'
                })),
                ...sessionData.map(d => ({
                    ...d,
                    type: 'Session'
                })),
                ...errorData.map(d => ({
                    ...d,
                    type: 'Error'
                }))
            ];

            if (allData.length === 0) {
                document.getElementById('overallActivityChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No activity data available</div>';
                return;
            }

            const dailyCounts = {};
            allData.forEach(item => {
                const date = new Date(item.timestamp).toLocaleDateString();
                if (!dailyCounts[date]) {
                    dailyCounts[date] = {
                        Export: 0,
                        OpenAI: 0,
                        'Exchange Rates': 0,
                        'Google Sheets': 0,
                        Session: 0,
                        Error: 0
                    };
                }
                dailyCounts[date][item.type]++;
            });

            const sortedDates = Object.keys(dailyCounts).sort();
            const recent30Dates = sortedDates.slice(-30);

            const datasets = [{
                    label: 'Exports',
                    data: recent30Dates.map(date => dailyCounts[date].Export),
                    backgroundColor: '#3b82f6'
                },
                {
                    label: 'OpenAI',
                    data: recent30Dates.map(date => dailyCounts[date].OpenAI),
                    backgroundColor: '#8b5cf6'
                },
                {
                    label: 'Exchange Rates',
                    data: recent30Dates.map(date => dailyCounts[date]['Exchange Rates']),
                    backgroundColor: '#f59e0b'
                },
                {
                    label: 'Google Sheets',
                    data: recent30Dates.map(date => dailyCounts[date]['Google Sheets']),
                    backgroundColor: '#10b981'
                },
                {
                    label: 'Sessions',
                    data: recent30Dates.map(date => dailyCounts[date].Session),
                    backgroundColor: '#06b6d4'
                },
                {
                    label: 'Errors',
                    data: recent30Dates.map(date => dailyCounts[date].Error),
                    backgroundColor: '#ef4444'
                }
            ];

            const activeDatasets = datasets.filter(dataset => dataset.data.some(value => value > 0));

            new Chart(document.getElementById("overallActivityChart"), {
                type: 'bar',
                data: {
                    labels: recent30Dates,
                    datasets: activeDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Operations Count'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    }
                }
            });
        }
    });
</script>