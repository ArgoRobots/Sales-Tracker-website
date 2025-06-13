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
$page_description = "View and analyze anonymous user data from the Sales Tracker application";
$additional_css = ['anonymous_dashboard.css'];

$dataDir = 'data_logs/';
$errorMessage = '';
$aggregatedData = [
    'dataPoints' => [
        'Export' => [],
        'OpenAI' => [],
        'OpenExchangeRates' => [],
        'GoogleSheets' => []
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
            count($aggregatedData['dataPoints']['GoogleSheets']) == 0)
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
            <strong>Latest File:</strong> <?= htmlspecialchars($fileInfo['latest_file']) ?>
            (<?= date('M j, Y g:i A', $fileInfo['latest_modified']) ?>)
            <?php if ($fileInfo['oldest_file'] !== $fileInfo['latest_file']): ?>
                <br>
                <strong>Oldest File:</strong> <?= htmlspecialchars($fileInfo['oldest_file']) ?>
                (<?= date('M j, Y g:i A', $fileInfo['oldest_modified']) ?>)
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsGrid">
            <!-- Will be populated by JavaScript -->
        </div>

        <!-- Export Types Breakdown -->
        <div class="chart-container">
            <h3>Export Types Distribution</h3>
            <div class="export-types-grid" id="exportTypesGrid">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Export Types Charts -->
        <div class="chart-row">
            <div class="chart-container">
                <h3>Average Duration by Export Type</h3>
                <div class="chart-canvas">
                    <canvas id="exportDurationByTypeChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h3>Average File Size by Export Type</h3>
                <div class="chart-canvas">
                    <canvas id="exportFileSizeByTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="chart-row">
            <div class="chart-container">
                <h3>Export Durations Over Time</h3>
                <div class="chart-canvas">
                    <canvas id="exportDurationChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h3>Export File Sizes</h3>
                <div class="chart-canvas">
                    <canvas id="exportFileSizeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <?php if (count($aggregatedData['dataPoints']['OpenAI']) > 0): ?>
            <div class="chart-row">
                <div class="chart-container">
                    <h3>OpenAI API Usage</h3>
                    <div class="chart-canvas">
                        <canvas id="openaiChart"></canvas>
                    </div>
                </div>
                <div class="chart-container">
                    <h3>OpenAI Token Usage</h3>
                    <div class="chart-canvas">
                        <canvas id="openaiTokenChart"></canvas>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Charts Row 3 -->
        <div class="chart-row">
            <?php if (count($aggregatedData['dataPoints']['OpenExchangeRates']) > 0): ?>
                <div class="chart-container">
                    <h3>Exchange Rates API Usage</h3>
                    <div class="chart-canvas">
                        <canvas id="exchangeRatesChart"></canvas>
                    </div>
                </div>
            <?php endif; ?>
            <div class="chart-container">
                <h3>Data Points Over Time</h3>
                <div class="chart-canvas">
                    <canvas id="overallActivityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- File Sources Analysis -->
        <div class="chart-container">
            <h3>Data Sources Timeline</h3>
            <div class="chart-canvas">
                <canvas id="fileSourcesChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawData = <?= $jsonData ?>;
        const exportTypes = ['ExcelSheetsChart', 'GoogleSheetsChart', 'Backup', 'XLSX', 'Receipts'];
        const typeColors = {
            'ExcelSheetsChart': '#3b82f6',
            'GoogleSheetsChart': '#10b981',
            'Backup': '#f59e0b',
            'XLSX': '#ef4444',
            'Receipts': '#8b5cf6'
        };

        if (!rawData.dataPoints) {
            console.log('No data points available');
            return;
        }

        const exportData = rawData.dataPoints.Export || [];
        const openaiData = rawData.dataPoints.OpenAI || [];
        const exchangeRatesData = rawData.dataPoints.OpenExchangeRates || [];
        const googleSheetsData = rawData.dataPoints.GoogleSheets || [];

        console.log('Data loaded:', {
            exports: exportData.length,
            openai: openaiData.length,
            exchangeRates: exchangeRatesData.length,
            googleSheets: googleSheetsData.length
        });

        // Generate statistics
        generateStatistics(exportData, openaiData, exchangeRatesData, googleSheetsData);

        // Generate export types breakdown
        generateExportTypesBreakdown(exportData);

        // Generate charts
        generateExportDurationByTypeChart(exportData);
        generateExportFileSizeByTypeChart(exportData);
        generateExportDurationChart(exportData);
        generateExportFileSizeChart(exportData);
        
        if (openaiData.length > 0) {
            generateOpenAIChart(openaiData);
            generateOpenAITokenChart(openaiData);
        }
        
        if (exchangeRatesData.length > 0) {
            generateExchangeRatesChart(exchangeRatesData);
        }
        
        generateOverallActivityChart(exportData, openaiData, exchangeRatesData, googleSheetsData);
        generateFileSourcesChart(exportData, openaiData, exchangeRatesData, googleSheetsData);

        function generateStatistics(exportData, openaiData, exchangeRatesData, googleSheetsData) {
            const statsGrid = document.getElementById('statsGrid');

            // Calculate statistics
            const totalExports = exportData.length;
            const totalOpenAI = openaiData.length;
            const totalExchangeRates = exchangeRatesData.length;
            const totalGoogleSheets = googleSheetsData.length;

            const avgExportDuration = exportData.length > 0 ?
                exportData.reduce((sum, item) => sum + parseFloat(item.DurationMS || 0), 0) / exportData.length : 0;

            const avgOpenAIDuration = openaiData.length > 0 ?
                openaiData.reduce((sum, item) => sum + parseFloat(item.DurationMS || 0), 0) / openaiData.length : 0;

            const totalTokens = openaiData.reduce((sum, item) => sum + parseInt(item.TokensUsed || 0), 0);

            const uniqueFiles = new Set();
            [...exportData, ...openaiData, ...exchangeRatesData, ...googleSheetsData]
            .forEach(item => {
                if (item.source_file) uniqueFiles.add(item.source_file);
            });

            const stats = [{
                    title: 'Total Exports',
                    value: totalExports.toLocaleString(),
                    subtext: 'operations'
                },
                {
                    title: 'OpenAI Calls',
                    value: totalOpenAI.toLocaleString(),
                    subtext: openaiData.length > 0 ? '' : 'No data'
                },
                {
                    title: 'Exchange Rate Calls',
                    value: totalExchangeRates.toLocaleString()
                },
                {
                    title: 'Google Sheets',
                    value: totalGoogleSheets.toLocaleString(),
                    subtext: googleSheetsData.length > 0 ? '' : 'No data'
                },
                {
                    title: 'Avg Export Time',
                    value: Math.round(avgExportDuration) + ' ms'
                },
                {
                    title: 'Avg OpenAI Time',
                    value: openaiData.length > 0 ? Math.round(avgOpenAIDuration) + ' ms' : 'No data'
                },
                {
                    title: 'Total Tokens',
                    value: openaiData.length > 0 ? totalTokens.toLocaleString() : 'No data'
                },
                {
                    title: 'Source Files',
                    value: uniqueFiles.size
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

        function generateExportTypesBreakdown(exportData) {
            const typesGrid = document.getElementById('exportTypesGrid');

            if (exportData.length === 0) {
                typesGrid.innerHTML = '<div class="export-type-card"><div class="type-name">No Data</div><div class="count">0</div></div>';
                return;
            }

            // Count export types
            const typeCounts = {};
            exportData.forEach(item => {
                const type = item.ExportType || 'Unknown';
                typeCounts[type] = (typeCounts[type] || 0) + 1;
            });

            const sortedTypes = Object.entries(typeCounts)
                .sort(([, a], [, b]) => b - a);

            typesGrid.innerHTML = sortedTypes.map(([type, count]) => `
            <div class="export-type-card" style="border-left: 4px solid ${typeColors[type] || '#9ca3af'}">
                <div class="type-name">${type}</div>
                <div class="count">${count}</div>
            </div>
        `).join('');
        }

        function generateExportDurationByTypeChart(exportData) {
            if (exportData.length === 0) {
                document.getElementById('exportDurationByTypeChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No export data available</div>';
                return;
            }

            // Group data by export type and calculate average duration
            const typeAverages = {};
            const typeCounts = {};
            
            exportData.forEach(item => {
                const type = item.ExportType || 'Unknown';
                const duration = item.DurationMS;
                const durationValue = typeof duration === 'string' ? 
                    parseFloat(duration.replace(/[^\d.]/g, '')) : 
                    parseInt(duration) || 0;
                
                if (!typeAverages[type]) {
                    typeAverages[type] = 0;
                    typeCounts[type] = 0;
                }
                
                typeAverages[type] += durationValue;
                typeCounts[type]++;
            });

            // Calculate averages
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
                        },
                        title: {
                            display: true,
                            text: 'Average duration by export type'
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

            // Group data by export type and calculate average file size
            const typeAverages = {};
            const typeCounts = {};
            
            filteredData.forEach(item => {
                const type = item.type;
                if (!typeAverages[type]) {
                    typeAverages[type] = 0;
                    typeCounts[type] = 0;
                }
                
                typeAverages[type] += item.size;
                typeCounts[type]++;
            });

            // Calculate averages
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
                        },
                        title: {
                            display: true,
                            text: 'Average file size by export type'
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

            // Use more data points for better visualization, limit to last 100
            const recentData = exportData.slice(-100);

            const labels = recentData.map((d, index) => {
                const date = new Date(d.timestamp);
                if (recentData.length > 50) {
                    // Show fewer labels for many data points
                    return index % Math.ceil(recentData.length / 20) === 0 ?
                        `${date.getMonth() + 1}/${date.getDate()}` : '';
                } else {
                    return `${date.getMonth() + 1}/${date.getDate()} ${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`;
                }
            });

            const durations = recentData.map(d => {
                const duration = d.DurationMS;
                if (typeof duration === 'string') {
                    return parseFloat(duration.replace(/[^\d.]/g, ''));
                }
                return parseInt(duration) || 0;
            });

            const backgroundColors = recentData.map(d => {
                const type = d.ExportType || 'Unknown';
                return typeColors[type] || '#9ca3af';
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
                        },
                        title: {
                            display: true,
                            text: `Showing ${recentData.length} most recent exports`
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

            // Group by type for better visualization
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
                        },
                        title: {
                            display: true,
                            text: `${fileSizes.length} exports with file size data`
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
                    }
                }
            });
        }

        function generateOpenAIChart(openaiData) {
            if (openaiData.length === 0) {
                document.getElementById('openaiChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No OpenAI usage data available</div>';
                return;
            }

            // Group by model
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
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6',
                            '#06b6d4'
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
                        title: {
                            display: true,
                            text: `${openaiData.length} total OpenAI API calls`
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
                        },
                        title: {
                            display: true,
                            text: `Token usage (last ${recentData.length} calls)`
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
            const labels = recentData.map((d, index) => {
                const date = new Date(d.timestamp);
                return `${date.getMonth() + 1}/${date.getDate()}`;
            });
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
                        },
                        title: {
                            display: true,
                            text: `${exchangeRatesData.length} total exchange rate calls`
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
                    }
                }
            });
        }

        function generateOverallActivityChart(exportData, openaiData, exchangeRatesData, googleSheetsData) {
            // Combine all data and group by date
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
                }))
            ];

            if (allData.length === 0) {
                document.getElementById('overallActivityChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No activity data available</div>';
                return;
            }

            // Group by date
            const dailyCounts = {};
            allData.forEach(item => {
                const date = new Date(item.timestamp);
                const dateKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                if (!dailyCounts[dateKey]) {
                    dailyCounts[dateKey] = {
                        Export: 0,
                        OpenAI: 0,
                        'Exchange Rates': 0,
                        'Google Sheets': 0
                    };
                }
                dailyCounts[dateKey][item.type]++;
            });

            const sortedDates = Object.keys(dailyCounts).sort();
            const recent30Dates = sortedDates.slice(-30);

            const datasets = [{
                    label: 'Exports',
                    data: recent30Dates.map(date => dailyCounts[date].Export),
                    backgroundColor: '#3b82f6',
                    borderColor: '#2563eb'
                },
                {
                    label: 'OpenAI',
                    data: recent30Dates.map(date => dailyCounts[date].OpenAI),
                    backgroundColor: '#8b5cf6',
                    borderColor: '#7c3aed'
                },
                {
                    label: 'Exchange Rates',
                    data: recent30Dates.map(date => dailyCounts[date]['Exchange Rates']),
                    backgroundColor: '#f59e0b',
                    borderColor: '#d97706'
                },
                {
                    label: 'Google Sheets',
                    data: recent30Dates.map(date => dailyCounts[date]['Google Sheets']),
                    backgroundColor: '#10b981',
                    borderColor: '#059669'
                }
            ];

            // Only include datasets that have data
            const activeDatasets = datasets.filter(dataset => dataset.data.some(value => value > 0));

            new Chart(document.getElementById("overallActivityChart"), {
                type: 'bar',
                data: {
                    labels: recent30Dates.map(date => {
                        const d = new Date(date);
                        return `${d.getMonth() + 1}/${d.getDate()}`;
                    }),
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
                        },
                        title: {
                            display: true,
                            text: `Activity overview (${sortedDates.length} days total)`
                        }
                    }
                }
            });
        }

        function generateFileSourcesChart(exportData, openaiData, exchangeRatesData, googleSheetsData) {
            // Combine all data to analyze file sources
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
                }))
            ];

            // Group by source file
            const fileCounts = {};
            allData.forEach(item => {
                const file = item.source_file || 'Unknown';
                if (!fileCounts[file]) {
                    fileCounts[file] = {
                        Export: 0,
                        OpenAI: 0,
                        'Exchange Rates': 0,
                        'Google Sheets': 0
                    };
                }
                fileCounts[file][item.type]++;
            });

            const fileNames = Object.keys(fileCounts).sort();

            if (fileNames.length === 0) {
                document.getElementById('fileSourcesChart').parentElement.innerHTML =
                    '<div class="chart-no-data">No file source data available</div>';
                return;
            }

            const datasets = [{
                    label: 'Exports',
                    data: fileNames.map(file => fileCounts[file].Export),
                    backgroundColor: '#3b82f6'
                },
                {
                    label: 'OpenAI',
                    data: fileNames.map(file => fileCounts[file].OpenAI),
                    backgroundColor: '#8b5cf6'
                },
                {
                    label: 'Exchange Rates',
                    data: fileNames.map(file => fileCounts[file]['Exchange Rates']),
                    backgroundColor: '#f59e0b'
                },
                {
                    label: 'Google Sheets',
                    data: fileNames.map(file => fileCounts[file]['Google Sheets']),
                    backgroundColor: '#10b981'
                }
            ];

            // Only include datasets that have data
            const activeDatasets = datasets.filter(dataset => dataset.data.some(value => value > 0));

            new Chart(document.getElementById("fileSourcesChart"), {
                type: 'bar',
                data: {
                    labels: fileNames.map(name => name.length > 20 ? name.substring(0, 17) + '...' : name),
                    datasets: activeDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                maxRotation: 45
                            }
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
                        },
                        title: {
                            display: true,
                            text: `Data distribution across ${fileNames.length} source files`
                        }
                    }
                }
            });
        }
    });
</script>