<?php
/**
 * Cron Management UI
 *
 * A secure interface for managing subscription renewals with:
 * - TOTP authentication (uses admin 2FA system)
 * - Dashboard showing subscriptions due for renewal
 * - Manual renewal execution
 * - Log viewer
 */

require_once __DIR__ . '/auth.php';

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$error = '';
$success = '';

// Handle logout
if (isset($_GET['logout'])) {
    clear_cron_authentication();
    header('Location: index.php');
    exit;
}

// Check if session expired
if (is_cron_authenticated() && is_cron_auth_expired()) {
    clear_cron_authentication();
    $error = 'Session expired. Please authenticate again.';
}

// Handle TOTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_totp'])) {
    $code = trim($_POST['totp_code'] ?? '');

    if (empty($code)) {
        $error = 'Please enter the verification code.';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $error = 'Invalid code format. Please enter a 6-digit code.';
    } elseif (verify_cron_totp($code)) {
        set_cron_authenticated(true);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid verification code. Please try again.';
    }
}

// Handle manual renewal execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_renewal']) && is_cron_authenticated()) {
    // Execute the renewal script
    $cronSecret = $_ENV['CRON_SECRET'] ?? '';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    // Get the correct path by using the current script's directory
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $renewalUrl = $baseUrl . $scriptDir . '/subscription_renewal.php?key=' . urlencode($cronSecret);

    // Use cURL to execute the renewal
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $renewalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minute timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Post/Redirect/Get pattern to prevent duplicate submissions on refresh
    if ($httpCode === 200) {
        $_SESSION['cron_success'] = 'Renewal process completed successfully. Check the logs for details.';
    } else {
        $_SESSION['cron_error'] = 'Failed to execute renewal process. HTTP Code: ' . $httpCode;
    }
    header('Location: index.php');
    exit;
}

// Check for flash messages from redirect
if (isset($_SESSION['cron_success'])) {
    $success = $_SESSION['cron_success'];
    unset($_SESSION['cron_success']);
}
if (isset($_SESSION['cron_error'])) {
    $error = $_SESSION['cron_error'];
    unset($_SESSION['cron_error']);
}

// Get subscriptions data if authenticated
$pendingRenewals = [];
$recentPayments = [];
$stats = [];

if (is_cron_authenticated()) {
    try {
        // Get subscriptions due for renewal (within next 7 days)
        $stmt = $pdo->prepare("
            SELECT
                s.*,
                u.username,
                u.email as user_email,
                DATEDIFF(s.end_date, NOW()) as days_until_renewal
            FROM ai_subscriptions s
            JOIN community_users u ON s.user_id = u.id
            WHERE s.status = 'active'
            AND s.end_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND s.auto_renew = 1
            ORDER BY s.end_date ASC
        ");
        $stmt->execute();
        $pendingRenewals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get recent payment history
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                s.email,
                u.username
            FROM ai_subscription_payments p
            JOIN ai_subscriptions s ON p.subscription_id = s.subscription_id
            JOIN community_users u ON s.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stmt->execute();
        $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get stats
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ai_subscriptions WHERE status = 'active'");
        $stats['active_subscriptions'] = $stmt->fetch()['count'];

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ai_subscriptions WHERE status = 'active' AND end_date <= DATE_ADD(NOW(), INTERVAL 1 DAY) AND auto_renew = 1");
        $stats['due_today'] = $stmt->fetch()['count'];

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ai_subscription_payments WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['successful_30d'] = $stmt->fetch()['count'];

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM ai_subscription_payments WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['failed_30d'] = $stmt->fetch()['count'];

    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get available log files
$logFiles = [];
$logsDir = __DIR__ . '/logs';
if (is_dir($logsDir)) {
    $files = glob($logsDir . '/subscription_renewal_*.log');
    rsort($files); // Most recent first
    $logFiles = array_slice($files, 0, 10); // Last 10 log files
}

// Handle log viewing
$selectedLog = '';
$logContent = '';
if (isset($_GET['view_log']) && is_cron_authenticated()) {
    $requestedLog = basename($_GET['view_log']);
    $logPath = $logsDir . '/' . $requestedLog;
    if (file_exists($logPath) && strpos($requestedLog, 'subscription_renewal_') === 0) {
        $selectedLog = $requestedLog;
        $logContent = file_get_contents($logPath);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Subscription Renewal Management - Argo Books</title>

    <link rel="stylesheet" href="../admin/common-style.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">

    <style>
        body {
            background: #f3f4f6;
            min-height: 100vh;
        }

        .cron-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cron-header {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cron-header h1 {
            color: white;
            margin: 0;
            font-size: 1.75rem;
        }

        .cron-header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
        }

        .stat-card .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .stat-card.warning .stat-value {
            color: #f59e0b;
        }

        .stat-card.danger .stat-value {
            color: #ef4444;
        }

        .stat-card.success .stat-value {
            color: #10b981;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .panel-header {
            background: #f8fafc;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1.125rem;
            color: #374151;
        }

        .panel-content {
            padding: 20px;
        }

        .renewal-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .renewal-item:last-child {
            border-bottom: none;
        }

        .renewal-info h4 {
            margin: 0 0 4px;
            font-size: 0.95rem;
        }

        .renewal-info p {
            margin: 0;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .renewal-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-due-now {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-due-soon {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        .payment-item:last-child {
            border-bottom: none;
        }

        .payment-status {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .log-selector {
            margin-bottom: 15px;
        }

        .log-selector select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .log-viewer {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.75rem;
            line-height: 1.5;
            max-height: 400px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .no-data svg {
            width: 48px;
            height: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .action-panel {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .action-info h3 {
            margin: 0 0 5px;
            color: #374151;
        }

        .action-info p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        /* Login styles */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-box h1 {
            color: #7c3aed;
            margin-bottom: 10px;
        }

        .login-box p {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .totp-input {
            width: 200px;
            padding: 15px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 0.5em;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            margin: 0 auto 20px;
            display: block;
            font-family: monospace;
        }

        .totp-input:focus {
            outline: none;
            border-color: #7c3aed;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: #6b7280;
            text-decoration: none;
        }

        .back-link:hover {
            color: #374151;
        }
    </style>
</head>
<body>
<?php if (!is_cron_authenticated()): ?>
    <!-- TOTP Login Form -->
    <div class="login-wrapper">
        <div class="login-box">
            <h1>Subscription Renewal</h1>
            <p>Enter your authenticator code to access the renewal management dashboard</p>

            <?php if ($error): ?>
                <div class="error-message" style="margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="number"
                       name="totp_code"
                       class="totp-input"
                       placeholder="000000"
                       maxlength="6"
                       autofocus
                       required>

                <input type="hidden" name="verify_totp" value="1">

                <button type="submit" class="btn btn-blue" style="width: 100%;">Verify</button>
            </form>

            <a href="../admin/" class="back-link">Back to Admin</a>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits entered
        document.querySelector('.totp-input').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                setTimeout(() => this.form.submit(), 300);
            }
        });
    </script>

<?php else: ?>
    <!-- Main Dashboard -->
    <div class="cron-container">
        <div class="cron-header">
            <div>
                <h1>Subscription Renewal Management</h1>
                <p>Monitor and manage AI subscription renewals</p>
            </div>
            <div class="header-actions">
                <a href="?logout=1" class="btn btn-small btn-red">Logout</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['active_subscriptions'] ?? 0; ?></div>
                <div class="stat-label">Active Subscriptions</div>
            </div>
            <div class="stat-card <?php echo ($stats['due_today'] ?? 0) > 0 ? 'warning' : ''; ?>">
                <div class="stat-value"><?php echo $stats['due_today'] ?? 0; ?></div>
                <div class="stat-label">Due Today</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?php echo $stats['successful_30d'] ?? 0; ?></div>
                <div class="stat-label">Successful (30 days)</div>
            </div>
            <div class="stat-card <?php echo ($stats['failed_30d'] ?? 0) > 0 ? 'danger' : ''; ?>">
                <div class="stat-value"><?php echo $stats['failed_30d'] ?? 0; ?></div>
                <div class="stat-label">Failed (30 days)</div>
            </div>
        </div>

        <!-- Manual Execution -->
        <div class="action-panel">
            <div class="action-info">
                <h3>Manual Renewal Process</h3>
                <p>Run the subscription renewal process manually to check and process pending renewals now.</p>
            </div>
            <form method="post" onsubmit="return confirm('Are you sure you want to run the renewal process now?');">
                <input type="hidden" name="run_renewal" value="1">
                <button type="submit" class="btn btn-green">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 5px;">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    Run Now
                </button>
            </form>
        </div>

        <div class="content-grid">
            <!-- Pending Renewals -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Upcoming Renewals (7 days)</h2>
                    <span style="color: #6b7280; font-size: 0.875rem;"><?php echo count($pendingRenewals); ?> subscriptions</span>
                </div>
                <div class="panel-content">
                    <?php if (empty($pendingRenewals)): ?>
                        <div class="no-data">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <p>No renewals due in the next 7 days</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pendingRenewals as $renewal): ?>
                            <?php
                            $daysUntil = $renewal['days_until_renewal'];
                            if ($daysUntil <= 0) {
                                $badgeClass = 'badge-due-now';
                                $badgeText = 'Due Now';
                            } elseif ($daysUntil <= 1) {
                                $badgeClass = 'badge-due-soon';
                                $badgeText = 'Tomorrow';
                            } else {
                                $badgeClass = 'badge-upcoming';
                                $badgeText = $daysUntil . ' days';
                            }
                            ?>
                            <div class="renewal-item">
                                <div class="renewal-info">
                                    <h4><?php echo htmlspecialchars($renewal['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($renewal['email']); ?> - <?php echo ucfirst($renewal['billing_cycle']); ?> ($<?php echo number_format($renewal['billing_cycle'] === 'yearly' ? 50 : 5, 2); ?>)</p>
                                    <p>Credit: $<?php echo number_format($renewal['credit_balance'] ?? 0, 2); ?></p>
                                </div>
                                <span class="renewal-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Payment Activity</h2>
                </div>
                <div class="panel-content">
                    <?php if (empty($recentPayments)): ?>
                        <div class="no-data">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                            </svg>
                            <p>No recent payment activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="payment-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($payment['username']); ?></strong>
                                    <?php if ($payment['payment_type'] === 'credit' && floatval($payment['amount']) == 0): ?>
                                        <span style="color: #7c3aed; margin-left: 5px; font-style: italic;">Credit (discount)</span>
                                    <?php else: ?>
                                        <span style="color: #6b7280; margin-left: 5px;">$<?php echo number_format($payment['amount'], 2); ?></span>
                                    <?php endif; ?>
                                    <br><span style="color: #9ca3af; font-size: 0.75rem; font-family: monospace;"><?php echo htmlspecialchars($payment['subscription_id']); ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="color: #9ca3af; font-size: 0.75rem;">
                                        <?php echo date('M j, g:i A', strtotime($payment['created_at'])); ?>
                                    </span>
                                    <span class="payment-status status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Logs Viewer -->
            <div class="panel full-width" id="logs">
                <div class="panel-header">
                    <h2>Renewal Logs</h2>
                </div>
                <div class="panel-content">
                    <?php if (empty($logFiles)): ?>
                        <div class="no-data">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                            </svg>
                            <p>No log files available yet. Logs are created when the renewal process runs.</p>
                        </div>
                    <?php else: ?>
                        <div class="log-selector">
                            <form method="get" action="#logs">
                                <select name="view_log" onchange="this.form.submit()">
                                    <option value="">Select a log file...</option>
                                    <?php foreach ($logFiles as $logFile): ?>
                                        <?php $fileName = basename($logFile); ?>
                                        <option value="<?php echo htmlspecialchars($fileName); ?>" <?php echo $selectedLog === $fileName ? 'selected' : ''; ?>>
                                            <?php echo $fileName; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>

                        <?php if ($logContent): ?>
                            <div class="log-viewer"><?php echo htmlspecialchars($logContent); ?></div>
                        <?php else: ?>
                            <p style="color: #6b7280; text-align: center;">Select a log file to view its contents.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
