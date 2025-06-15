<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set page variables for the header
$page_title = "Statistics Dashboard";
$page_description = "View comprehensive analytics, user statistics, and performance metrics";

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

    $query = "
        SELECT 
            $sql_period as period, 
            $display_format as display_period,
            COUNT(*) as count 
        FROM statistics 
        WHERE event_type = 'download'
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
function get_user_countries($limit = 10)
{
    $db = get_db_connection();
    $query = "
        SELECT 
            country_code,
            COUNT(DISTINCT ip_address) as count
        FROM statistics
        WHERE country_code IS NOT NULL AND country_code != ''
        GROUP BY country_code
        ORDER BY count DESC
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
    $post_type_counts[] = (int)$type['count'];
    $post_type_views[] = (int)$type['total_views'];
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

foreach ($user_agents as $browser) {
    $browser_labels[] = $browser['browser'];
    $browser_counts[] = (int)$browser['count'];
}

// Map all ISO 3166-1 alpha-2 codes to full country names
$country_name_map = [
    'AF' => 'Afghanistan',
    'AX' => 'Åland Islands',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AQ' => 'Antarctica',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BQ' => 'Bonaire',
    'BA' => 'Bosnia and Herzegovina',
    'BW' => 'Botswana',
    'BV' => 'Bouvet Island',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island',
    'CC' => 'Cocos (Keeling) Islands',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CD' => 'Congo (DRC)',
    'CG' => 'Congo (Republic)',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => "Côte d'Ivoire",
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CW' => 'Curaçao',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'SZ' => 'Eswatini',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'TF' => 'French Southern Territories',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GG' => 'Guernsey',
    'GN' => 'Guinea',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HM' => 'Heard Island and McDonald Islands',
    'VA' => 'Vatican City',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran',
    'IQ' => 'Iraq',
    'IE' => 'Ireland',
    'IM' => 'Isle of Man',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JE' => 'Jersey',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KP' => 'North Korea',
    'KR' => 'South Korea',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Laos',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macau',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MH' => 'Marshall Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte',
    'MX' => 'Mexico',
    'FM' => 'Micronesia',
    'MD' => 'Moldova',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'ME' => 'Montenegro',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NU' => 'Niue',
    'NF' => 'Norfolk Island',
    'MK' => 'North Macedonia',
    'MP' => 'Northern Mariana Islands',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PS' => 'Palestine',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn Islands',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'RE' => 'Réunion',
    'RO' => 'Romania',
    'RU' => 'Russia',
    'RW' => 'Rwanda',
    'BL' => 'Saint Barthélemy',
    'SH' => 'Saint Helena',
    'KN' => 'Saint Kitts and Nevis',
    'LC' => 'Saint Lucia',
    'MF' => 'Saint Martin',
    'PM' => 'Saint Pierre and Miquelon',
    'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'São Tomé and Príncipe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SX' => 'Sint Maarten',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia and the South Sandwich Islands',
    'SS' => 'South Sudan',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SJ' => 'Svalbard and Jan Mayen',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syria',
    'TW' => 'Taiwan',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania',
    'TH' => 'Thailand',
    'TL' => 'Timor-Leste',
    'TG' => 'Togo',
    'TK' => 'Tokelau',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'GB' => 'United Kingdom',
    'US' => 'United States',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VE' => 'Venezuela',
    'VN' => 'Vietnam',
    'VG' => 'British Virgin Islands',
    'VI' => 'U.S. Virgin Islands',
    'WF' => 'Wallis and Futuna',
    'EH' => 'Western Sahara',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
];

// Prepare country data for charts
$country_labels = [];
$country_counts = [];

foreach ($user_countries as $country) {
    $code = $country['country_code'];
    $country_labels[] = $country_name_map[$code] ?? $code;
    $country_counts[] = $country['count'];
}

include 'admin_header.php';
?>

<div class="container">
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

    <!-- Statistics Cards -->
    <div class="stats-grid" id="statsGrid">
        <!-- Will be populated by JavaScript -->
    </div>

    <!-- Charts -->
    <div class="chart-row">
        <div class="chart-container">
            <h2>License Activation Rate</h2>
            <canvas id="activationChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Growth Trends</h2>
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    <div class="chart-row">
        <div class="chart-container">
            <h2>Community Post Types</h2>
            <canvas id="postTypeChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Post Views by Type</h2>
            <canvas id="postViewsChart"></canvas>
        </div>
    </div>

    <div class="chart-row">
        <div class="chart-container">
            <h2>Top 10 User Countries</h2>
            <canvas id="countryChart"></canvas>
        </div>
        <div class="chart-container">
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
            <button id="exportCSV" class="btn btn-blue">Export as CSV</button>
            <button id="exportJSON" class="btn btn-blue">Export as JSON</button>
        </div>
    </div>
</div>

<script>
    // Helper function to sum arrays since array_sum is a PHP function
    const sumArray = (arr) => arr.reduce((sum, val) => sum + (Number(val) || 0), 0);

    document.addEventListener('DOMContentLoaded', function() {
        // Chart data
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const downloadsData = <?php echo json_encode($downloads_data); ?>;
        const registrationsData = <?php echo json_encode($registrations_data); ?>;
        const pageViewsData = <?php echo json_encode($page_views_data); ?>;
        const activationData = <?php echo json_encode([
                                    (int)$activation_rate['activated'],
                                    (int)($activation_rate['total'] - $activation_rate['activated'])
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

        generateStatistics();

        // Calculate growth data
        const growthData = [];
        for (let i = 1; i < downloadsData.length; i++) {
            const previous = downloadsData[i - 1];
            const current = downloadsData[i];
            const growth = previous > 0 ? ((current - previous) / previous) * 100 : 0;
            growthData.push(growth.toFixed(1));
        }

        function generateStatistics() {
            const statsGrid = document.getElementById('statsGrid');

            const totalDownloads = sumArray(downloadsData);
            const totalRegistrations = sumArray(registrationsData);
            const totalPageViews = sumArray(pageViewsData);
            const activationRate = <?php echo $activation_percentage; ?>;
            const growthRate = <?php echo $latest_growth; ?>;
            const postViews = <?php echo str_replace(',', '', $total_post_views); ?>;
            const avgViewsPerPost = <?php echo $avg_post_views; ?>;
            const mostViewed = <?php echo str_replace(',', '', $most_viewed); ?>;

            const stats = [{
                    title: 'Total Downloads',
                    value: totalDownloads.toLocaleString(),
                    subtext: 'operations'
                },
                {
                    title: 'Registrations',
                    value: totalRegistrations.toLocaleString(),
                    subtext: 'users'
                },
                {
                    title: 'Activation Rate',
                    value: activationRate + '%',
                    subtext: 'license activation'
                },
                {
                    title: 'Growth Rate',
                    value: (growthRate >= 0 ? '+' : '') + growthRate + '%',
                    subtext: 'period over period'
                },
                {
                    title: 'Page Views',
                    value: totalPageViews.toLocaleString(),
                    subtext: 'total views'
                },
                {
                    title: 'Post Views',
                    value: postViews.toLocaleString(),
                    subtext: 'community posts'
                },
                {
                    title: 'Avg Views/Post',
                    value: avgViewsPerPost.toLocaleString(),
                    subtext: 'average engagement'
                },
                {
                    title: 'Most Viewed Post',
                    value: mostViewed.toLocaleString(),
                    subtext: 'single post views'
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

        // Activation rate chart
        const ctxActivation = document.getElementById('activationChart').getContext('2d');
        new Chart(ctxActivation, {
            type: 'pie',
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
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = Number(context.raw) || 0;
                                const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} licenses (${percentage}%)`;
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
                        bottom: 40
                    }
                }
            }
        });

        // Post Type chart
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
                                const value = Number(context.raw) || 0;
                                const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} posts (${percentage}%)`;
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

        // Post Views by Type chart
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = Number(context.raw) || 0;
                                const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value.toLocaleString()} views (${percentage}%)`;
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

        // Country chart
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = Number(context.raw) || 0;
                                const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} users (${percentage}%)`;
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

        // Browser chart
        const ctxBrowser = document.getElementById('browserChart').getContext('2d');
        new Chart(ctxBrowser, {
            type: 'pie',
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
                        bottom: 40
                    }
                }
            }
        });

        // Export functions
        document.getElementById('exportCSV').addEventListener('click', function() {
            // Create CSV content for time series data
            let csvContent = 'data:text/csv;charset=utf-8,';

            // Add header for time series
            csvContent += 'TIME SERIES DATA\n';
            csvContent += 'Period,Downloads,Registrations,Page Views,Growth Rate (%)\n';

            for (let i = 0; i < chartLabels.length; i++) {
                const row = [
                    chartLabels[i],
                    downloadsData[i] || 0,
                    registrationsData[i] || 0,
                    pageViewsData[i] || 0,
                    i > 0 ? (growthData[i - 1] || 0) : 'N/A'
                ].join(',');
                csvContent += row + '\n';
            }

            // Add summary data
            csvContent += '\nSUMMARY DATA\n';
            csvContent += 'Metric,Value\n';
            csvContent += `Total Downloads,${sumArray(downloadsData)}\n`;
            csvContent += `Total Registrations,${sumArray(registrationsData)}\n`;
            csvContent += `Total Page Views,${sumArray(pageViewsData)}\n`;
            csvContent += `Activation Rate,${<?php echo $activation_percentage; ?>}%\n`;
            csvContent += `Latest Growth Rate,${<?php echo $latest_growth; ?>}%\n`;
            csvContent += `Total Post Views,${<?php echo str_replace(',', '', $total_post_views); ?>}\n`;
            csvContent += `Average Views per Post,${<?php echo $avg_post_views; ?>}\n`;
            csvContent += `Most Viewed Post,${<?php echo str_replace(',', '', $most_viewed); ?>}\n`;

            // Add post type data
            csvContent += '\nPOST TYPE DATA\n';
            csvContent += 'Post Type,Count,Total Views\n';
            for (let i = 0; i < postTypeLabels.length; i++) {
                csvContent += `${postTypeLabels[i]},${postTypeCounts[i]},${postTypeViews[i]}\n`;
            }

            // Add country data
            csvContent += '\nUSER COUNTRIES\n';
            csvContent += 'Country Code,User Count\n';
            for (let i = 0; i < countryLabels.length; i++) {
                csvContent += `${countryLabels[i]},${countryCounts[i]}\n`;
            }

            // Add browser data
            csvContent += '\nBROWSER DISTRIBUTION\n';
            csvContent += 'Browser,User Count\n';
            for (let i = 0; i < browserLabels.length; i++) {
                csvContent += `${browserLabels[i]},${browserCounts[i]}\n`;
            }

            // Add most active users data
            csvContent += '\nMOST ACTIVE USERS\n';
            csvContent += 'Username,Email,Posts,Comments,Total Views,Activity Score\n';

            <?php foreach ($active_users as $user): ?>
                csvContent += `<?php echo str_replace('"', '""', $user['username']); ?>,<?php echo str_replace('"', '""', $user['email']); ?>,<?php echo $user['post_count']; ?>,<?php echo $user['comment_count']; ?>,<?php echo isset($user['total_views']) ? (int)$user['total_views'] : 0; ?>,<?php echo $user['activity_score']; ?>\n`;
            <?php endforeach; ?>

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
            // Create comprehensive JSON content with all dashboard data
            const jsonData = {
                summary: {
                    total_downloads: sumArray(downloadsData),
                    total_registrations: sumArray(registrationsData),
                    total_page_views: sumArray(pageViewsData),
                    activation_rate: <?php echo $activation_percentage; ?>,
                    growth_rate: <?php echo $latest_growth; ?>,
                    post_views: {
                        total: <?php echo str_replace(',', '', $total_post_views); ?>,
                        average_per_post: <?php echo $avg_post_views; ?>,
                        most_viewed: <?php echo str_replace(',', '', $most_viewed); ?>
                    }
                },
                time_series: [],
                growth_trends: [],
                license_activation: {
                    activated: activationData[0],
                    not_activated: activationData[1]
                },
                post_types: [],
                countries: [],
                browsers: [],
                conversion: {
                    downloads: conversionData[0],
                    registrations: conversionData[1],
                    purchases: conversionData[2],
                    registration_to_purchase_rate: <?php echo $conversion_data['registration_to_purchase']; ?>
                },
                most_active_users: []
            };

            // Add time series data
            for (let i = 0; i < chartLabels.length; i++) {
                jsonData.time_series.push({
                    period: chartLabels[i],
                    downloads: downloadsData[i] || 0,
                    registrations: registrationsData[i] || 0,
                    page_views: pageViewsData[i] || 0
                });

                // Add growth data starting from the second period
                if (i > 0) {
                    jsonData.growth_trends.push({
                        period: chartLabels[i],
                        growth_rate: parseFloat(growthData[i - 1])
                    });
                }
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

            // Add most active users data
            <?php foreach ($active_users as $user): ?>
                jsonData.most_active_users.push({
                    username: "<?php echo addslashes($user['username']); ?>",
                    email: "<?php echo addslashes($user['email']); ?>",
                    post_count: <?php echo isset($user['post_count']) ? (int)$user['post_count'] : 0; ?>,
                    comment_count: <?php echo isset($user['comment_count']) ? (int)$user['comment_count'] : 0; ?>,
                    total_views: <?php echo isset($user['total_views']) ? (int)$user['total_views'] : 0; ?>,
                    activity_score: <?php echo isset($user['activity_score']) ? (int)$user['activity_score'] : 0; ?>
                });
            <?php endforeach; ?>

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