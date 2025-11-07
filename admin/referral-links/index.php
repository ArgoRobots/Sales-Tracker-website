<?php
session_start();
require_once '../../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Set page variables for the header
$page_title = "Referral Link Tracking";
$page_description = "Create and manage referral links to track ad/sponsor performance";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = get_db_connection();

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $source_code = trim($_POST['source_code']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $target_url = trim($_POST['target_url']);

            $stmt = $db->prepare('INSERT INTO referral_links (source_code, name, description, target_url) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $source_code, $name, $description, $target_url);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = 'Referral link created successfully!';
            header('Location: index.php');
            exit;
        } elseif ($_POST['action'] === 'update') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $target_url = trim($_POST['target_url']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmt = $db->prepare('UPDATE referral_links SET name = ?, description = ?, target_url = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('sssii', $name, $description, $target_url, $is_active, $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = 'Referral link updated successfully!';
            header('Location: index.php');
            exit;
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];

            $stmt = $db->prepare('DELETE FROM referral_links WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = 'Referral link deleted successfully!';
            header('Location: index.php');
            exit;
        }
    }
}

// Function to get all referral links
function get_referral_links()
{
    $db = get_db_connection();
    $query = "
        SELECT
            rl.*,
            COUNT(DISTINCT rv.id) as total_visits,
            SUM(CASE WHEN rv.converted = 1 THEN 1 ELSE 0 END) as conversions
        FROM referral_links rl
        LEFT JOIN referral_visits rv ON rl.source_code = rv.source_code
        GROUP BY rl.id
        ORDER BY total_visits DESC, rl.created_at DESC";

    $result = $db->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// Function to get referral visits by source
function get_visits_by_source($limit = 10)
{
    $db = get_db_connection();
    $query = "
        SELECT
            source_code,
            COUNT(*) as visit_count,
            SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions,
            COUNT(DISTINCT ip_address) as unique_visitors
        FROM referral_visits
        GROUP BY source_code
        ORDER BY visit_count DESC
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

// Function to get visits over time by source
function get_visits_over_time($period = 'day', $limit = 30, $source_code = null)
{
    $db = get_db_connection();

    $sql_period = '';
    $display_format = '';
    switch ($period) {
        case 'day':
            $sql_period = 'DATE(visited_at)';
            $display_format = 'DATE(visited_at)';
            break;
        case 'week':
            $sql_period = 'YEARWEEK(visited_at)';
            $display_format = 'CONCAT("Week ", WEEK(visited_at), ", ", YEAR(visited_at))';
            break;
        case 'month':
            $sql_period = 'DATE_FORMAT(visited_at, "%Y-%m")';
            $display_format = 'DATE_FORMAT(visited_at, "%b %Y")';
            break;
    }

    if ($source_code) {
        $query = "
            SELECT
                $sql_period as period,
                $display_format as display_period,
                COUNT(*) as count,
                SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions
            FROM referral_visits
            WHERE source_code = ?
            GROUP BY period
            ORDER BY period DESC
            LIMIT ?";

        $stmt = $db->prepare($query);
        $stmt->bind_param('si', $source_code, $limit);
    } else {
        $query = "
            SELECT
                $sql_period as period,
                $display_format as display_period,
                COUNT(*) as count,
                SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions
            FROM referral_visits
            GROUP BY period
            ORDER BY period DESC
            LIMIT ?";

        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $limit);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

// Function to get geographic distribution
function get_referral_countries($limit = 10)
{
    $db = get_db_connection();
    $query = "
        SELECT
            country_code,
            COUNT(*) as visit_count,
            SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions
        FROM referral_visits
        WHERE country_code IS NOT NULL AND country_code != ''
        GROUP BY country_code
        ORDER BY visit_count DESC
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

// Get statistics
$referral_links = get_referral_links();
$visits_by_source = get_visits_by_source(15);
$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$allowed_periods = ['day', 'week', 'month'];
if (!in_array($period, $allowed_periods)) {
    $period = 'day';
}

$visits_over_time = get_visits_over_time($period, 30);
$referral_countries = get_referral_countries();

// Prepare data for charts
$source_labels = [];
$source_visit_counts = [];
$source_conversion_counts = [];

foreach ($visits_by_source as $item) {
    $source_labels[] = $item['source_code'];
    $source_visit_counts[] = (int)$item['visit_count'];
    $source_conversion_counts[] = (int)$item['conversions'];
}

// Prepare time series data
$time_labels = [];
$time_visit_counts = [];
$time_conversion_counts = [];

$visits_over_time = array_reverse($visits_over_time);
foreach ($visits_over_time as $item) {
    $time_labels[] = isset($item['display_period']) ? $item['display_period'] : $item['period'];
    $time_visit_counts[] = (int)$item['count'];
    $time_conversion_counts[] = (int)$item['conversions'];
}

// Prepare country data
$country_labels = [];
$country_visit_counts = [];
$country_conversion_counts = [];

// Country code to name mapping
$country_name_map = [
    'US' => 'United States', 'CA' => 'Canada', 'GB' => 'United Kingdom',
    'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France', 'JP' => 'Japan',
    'CN' => 'China', 'IN' => 'India', 'BR' => 'Brazil', 'MX' => 'Mexico',
    'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands', 'SE' => 'Sweden',
    'CH' => 'Switzerland', 'PL' => 'Poland', 'BE' => 'Belgium', 'NO' => 'Norway',
    'AT' => 'Austria', 'DK' => 'Denmark', 'FI' => 'Finland', 'IE' => 'Ireland',
    'NZ' => 'New Zealand', 'SG' => 'Singapore', 'HK' => 'Hong Kong', 'KR' => 'South Korea',
    'RU' => 'Russia', 'ZA' => 'South Africa', 'AR' => 'Argentina', 'CL' => 'Chile'
];

foreach ($referral_countries as $country) {
    $code = $country['country_code'];
    $country_labels[] = $country_name_map[$code] ?? $code;
    $country_visit_counts[] = (int)$country['visit_count'];
    $country_conversion_counts[] = (int)$country['conversions'];
}

// Calculate total stats
$total_visits = array_sum($source_visit_counts);
$total_conversions = array_sum($source_conversion_counts);
$conversion_rate = $total_visits > 0 ? round(($total_conversions / $total_visits) * 100, 1) : 0;

include '../admin_header.php';
?>

<div class="container">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Summary Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Visits</h3>
            <div class="value"><?php echo number_format($total_visits); ?></div>
            <div class="subtext">from referral sources</div>
        </div>
        <div class="stat-card">
            <h3>Total Conversions</h3>
            <div class="value"><?php echo number_format($total_conversions); ?></div>
            <div class="subtext">license purchases</div>
        </div>
        <div class="stat-card">
            <h3>Conversion Rate</h3>
            <div class="value"><?php echo $conversion_rate; ?>%</div>
            <div class="subtext">visit to purchase</div>
        </div>
        <div class="stat-card">
            <h3>Active Sources</h3>
            <div class="value"><?php echo count($visits_by_source); ?></div>
            <div class="subtext">referral sources</div>
        </div>
    </div>

    <!-- Period selection for time series chart -->
    <div class="period-selection">
        <span>Time Period:</span>
        <div class="period-buttons">
            <?php
            $periods = [
                'day' => 'Daily',
                'week' => 'Weekly',
                'month' => 'Monthly'
            ];

            foreach ($periods as $periodKey => $periodName) {
                $activeClass = ($period === $periodKey) ? 'active' : '';
                echo "<a href=\"?period={$periodKey}\" class=\"period-btn {$activeClass}\">{$periodName}</a>";
            }
            ?>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-row">
        <div class="chart-container">
            <h2>Visits by Source</h2>
            <canvas id="sourceVisitsChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Conversions by Source</h2>
            <canvas id="sourceConversionsChart"></canvas>
        </div>
    </div>

    <div class="chart-row">
        <div class="chart-container">
            <h2>Visits Over Time</h2>
            <canvas id="visitsTimeChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Conversions Over Time</h2>
            <canvas id="conversionsTimeChart"></canvas>
        </div>
    </div>

    <div class="chart-row">
        <div class="chart-container">
            <h2>Top Countries</h2>
            <canvas id="countriesChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Conversion Rate by Source</h2>
            <canvas id="conversionRateChart"></canvas>
        </div>
    </div>

    <!-- Referral Links Management -->
    <div class="table-container">
        <div class="table-header-actions">
            <h2>Manage Referral Links</h2>
            <button id="createLinkBtn" class="btn btn-blue">Create New Link</button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Source Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Target URL</th>
                        <th>Visits</th>
                        <th>Conversions</th>
                        <th>Rate</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referral_links as $link): ?>
                        <?php
                        $conv_rate = $link['total_visits'] > 0 ? round(($link['conversions'] / $link['total_visits']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($link['source_code']); ?></code></td>
                            <td><?php echo htmlspecialchars($link['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($link['description'], 0, 50)) . (strlen($link['description']) > 50 ? '...' : ''); ?></td>
                            <td><a href="<?php echo htmlspecialchars($link['target_url']); ?>" target="_blank" class="link-preview"><?php echo htmlspecialchars(substr($link['target_url'], 0, 30)) . (strlen($link['target_url']) > 30 ? '...' : ''); ?></a></td>
                            <td><?php echo number_format($link['total_visits']); ?></td>
                            <td><?php echo number_format($link['conversions']); ?></td>
                            <td><?php echo $conv_rate; ?>%</td>
                            <td>
                                <span class="status-badge <?php echo $link['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $link['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button onclick="copyLink('<?php echo htmlspecialchars($link['target_url']); ?>', '<?php echo htmlspecialchars($link['source_code']); ?>')" class="btn-small btn-blue" title="Copy link with source parameter">Copy Link</button>
                                <button onclick="editLink(<?php echo htmlspecialchars(json_encode($link)); ?>)" class="btn-small btn-yellow" title="Edit">Edit</button>
                                <button onclick="deleteLink(<?php echo $link['id']; ?>)" class="btn-small btn-red" title="Delete">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="linkModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Create Referral Link</h2>

        <form id="linkForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="linkId">

            <div class="form-group">
                <label for="source_code">Source Code *</label>
                <input type="text" name="source_code" id="source_code" required pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, hyphens, and underscores allowed">
                <small>Used in URL: ?source=CODE (alphanumeric, hyphens, underscores only)</small>
            </div>

            <div class="form-group">
                <label for="name">Display Name *</label>
                <input type="text" name="name" id="name" required>
                <small>A friendly name for this referral source</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="3"></textarea>
                <small>Optional notes about this referral source</small>
            </div>

            <div class="form-group">
                <label for="target_url">Target URL *</label>
                <input type="url" name="target_url" id="target_url" required>
                <small>The page users will land on (usually your homepage)</small>
            </div>

            <div class="form-group checkbox-group" id="activeCheckboxGroup" style="display: none;">
                <label>
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-blue">Save</button>
                <button type="button" class="btn btn-gray" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card .value {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.stat-card .subtext {
    font-size: 14px;
    color: #9ca3af;
}

.period-selection {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.period-selection span {
    font-weight: 600;
    color: #4b5563;
}

.period-buttons {
    display: flex;
    gap: 10px;
}

.period-btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    color: #4b5563;
    background: #f3f4f6;
    font-weight: 500;
    transition: all 0.2s;
}

.period-btn:hover {
    background: #e5e7eb;
}

.period-btn.active {
    background: #3b82f6;
    color: white;
}

.chart-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.chart-container {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    min-height: 400px;
}

.chart-container h2 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.chart-container canvas {
    max-height: 350px;
}

.table-container {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.table-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header-actions h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table thead {
    background: #f9fafb;
}

table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}

table tbody tr:hover {
    background: #f9fafb;
}

code {
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #dc2626;
}

.link-preview {
    color: #3b82f6;
    text-decoration: none;
}

.link-preview:hover {
    text-decoration: underline;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn, .btn-small {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-small {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-blue, .btn-small.btn-blue {
    background: #3b82f6;
    color: white;
}

.btn-blue:hover, .btn-small.btn-blue:hover {
    background: #2563eb;
}

.btn-yellow, .btn-small.btn-yellow {
    background: #f59e0b;
    color: white;
}

.btn-yellow:hover, .btn-small.btn-yellow:hover {
    background: #d97706;
}

.btn-red, .btn-small.btn-red {
    background: #ef4444;
    color: white;
}

.btn-red:hover, .btn-small.btn-red:hover {
    background: #dc2626;
}

.btn-gray {
    background: #6b7280;
    color: white;
}

.btn-gray:hover {
    background: #4b5563;
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 32px;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-close {
    color: #9ca3af;
    float: right;
    font-size: 32px;
    font-weight: bold;
    line-height: 1;
    cursor: pointer;
}

.modal-close:hover {
    color: #4b5563;
}

#modalTitle {
    margin-top: 0;
    margin-bottom: 24px;
    color: #1f2937;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

.form-group input[type="text"],
.form-group input[type="url"],
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group small {
    display: block;
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.form-actions .btn {
    flex: 1;
}
</style>

<script>
    const sourceLabels = <?php echo json_encode($source_labels); ?>;
    const sourceVisitCounts = <?php echo json_encode($source_visit_counts); ?>;
    const sourceConversionCounts = <?php echo json_encode($source_conversion_counts); ?>;
    const timeLabels = <?php echo json_encode($time_labels); ?>;
    const timeVisitCounts = <?php echo json_encode($time_visit_counts); ?>;
    const timeConversionCounts = <?php echo json_encode($time_conversion_counts); ?>;
    const countryLabels = <?php echo json_encode($country_labels); ?>;
    const countryVisitCounts = <?php echo json_encode($country_visit_counts); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // Visits by Source Chart
        const ctxSourceVisits = document.getElementById('sourceVisitsChart').getContext('2d');
        new Chart(ctxSourceVisits, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Visits',
                    data: sourceVisitCounts,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
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
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Visits: ${context.raw.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });

        // Conversions by Source Chart
        const ctxSourceConversions = document.getElementById('sourceConversionsChart').getContext('2d');
        new Chart(ctxSourceConversions, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Conversions',
                    data: sourceConversionCounts,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
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
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Conversions: ${context.raw.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });

        // Visits Over Time Chart
        const ctxVisitsTime = document.getElementById('visitsTimeChart').getContext('2d');
        new Chart(ctxVisitsTime, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Visits',
                    data: timeVisitCounts,
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(245, 158, 11, 1)'
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
                        position: 'top'
                    }
                }
            }
        });

        // Conversions Over Time Chart
        const ctxConversionsTime = document.getElementById('conversionsTimeChart').getContext('2d');
        new Chart(ctxConversionsTime, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Conversions',
                    data: timeConversionCounts,
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)'
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
                        position: 'top'
                    }
                }
            }
        });

        // Countries Chart
        const ctxCountries = document.getElementById('countriesChart').getContext('2d');
        new Chart(ctxCountries, {
            type: 'bar',
            data: {
                labels: countryLabels,
                datasets: [{
                    label: 'Visits',
                    data: countryVisitCounts,
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
                        display: false
                    }
                }
            }
        });

        // Conversion Rate by Source Chart
        const conversionRates = sourceLabels.map((label, index) => {
            const visits = sourceVisitCounts[index];
            const conversions = sourceConversionCounts[index];
            return visits > 0 ? (conversions / visits) * 100 : 0;
        });

        const ctxConversionRate = document.getElementById('conversionRateChart').getContext('2d');
        new Chart(ctxConversionRate, {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Conversion Rate (%)',
                    data: conversionRates,
                    backgroundColor: 'rgba(139, 92, 246, 0.7)',
                    borderColor: 'rgba(139, 92, 246, 1)',
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
                            callback: function(value) {
                                return value.toFixed(1) + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Conversion Rate: ${context.raw.toFixed(2)}%`;
                            }
                        }
                    }
                }
            }
        });
    });

    // Modal Functions
    function openModal() {
        document.getElementById('linkModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('linkModal').style.display = 'none';
        document.getElementById('linkForm').reset();
        document.getElementById('formAction').value = 'create';
        document.getElementById('modalTitle').textContent = 'Create Referral Link';
        document.getElementById('source_code').removeAttribute('readonly');
        document.getElementById('activeCheckboxGroup').style.display = 'none';
    }

    function copyLink(targetUrl, sourceCode) {
        const url = new URL(targetUrl);
        url.searchParams.set('source', sourceCode);
        const fullUrl = url.toString();

        navigator.clipboard.writeText(fullUrl).then(() => {
            alert('Link copied to clipboard:\n' + fullUrl);
        }).catch(err => {
            prompt('Copy this link:', fullUrl);
        });
    }

    function editLink(link) {
        document.getElementById('formAction').value = 'update';
        document.getElementById('linkId').value = link.id;
        document.getElementById('source_code').value = link.source_code;
        document.getElementById('source_code').setAttribute('readonly', 'readonly');
        document.getElementById('name').value = link.name;
        document.getElementById('description').value = link.description;
        document.getElementById('target_url').value = link.target_url;
        document.getElementById('is_active').checked = link.is_active == 1;
        document.getElementById('activeCheckboxGroup').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Edit Referral Link';
        openModal();
    }

    function deleteLink(id) {
        if (confirm('Are you sure you want to delete this referral link? This will not delete visit history.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    document.getElementById('createLinkBtn').addEventListener('click', openModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('linkModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    // Restore scroll position
    if (sessionStorage.getItem('scrollPosition')) {
        window.scrollTo(0, sessionStorage.getItem('scrollPosition'));
        sessionStorage.removeItem('scrollPosition');
    }

    // Save scroll position when clicking period links
    const links = document.querySelectorAll('a[href^="?period="]');
    links.forEach(link => {
        link.addEventListener('click', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });
    });
</script>

<?php
// Footer is typically included in admin_header.php or handled separately
?>
