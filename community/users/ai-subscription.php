<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

// Ensure user is logged in
require_login();

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

$success_message = '';
$error_message = '';

if (isset($_SESSION['subscription_success'])) {
    $success_message = $_SESSION['subscription_success'];
    unset($_SESSION['subscription_success']);
}

if (isset($_SESSION['subscription_error'])) {
    $error_message = $_SESSION['subscription_error'];
    unset($_SESSION['subscription_error']);
}

// Get subscription info
$ai_subscription = get_user_ai_subscription($user_id);

// Get payment history
$payment_history = [];
if ($ai_subscription) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM ai_subscription_payments
            WHERE subscription_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$ai_subscription['subscription_id']]);
        $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail - payment history not critical
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage your AI Subscription - Argo Community">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>AI Subscription - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="ai-subscription.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="subscription-page-container">
        <div class="page-header">
            <div class="title-container">
                <h1>AI Subscription</h1>
            </div>

            <div class="button-container">
                <a href="profile.php" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="25" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Profile
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Subscription Status Section -->
        <div class="subscription-section">
            <h2>Subscription Status</h2>

            <?php if ($ai_subscription): ?>
                <div class="subscription-card">
                    <div class="subscription-header">
                        <div class="subscription-plan">
                            <span class="plan-name">Argo AI Features</span>
                            <span class="billing-cycle"><?php echo ucfirst($ai_subscription['billing_cycle']); ?> Plan</span>
                        </div>
                        <div class="subscription-status <?php echo $ai_subscription['status']; ?>">
                            <span class="status-badge"><?php echo ucfirst($ai_subscription['status']); ?></span>
                        </div>
                    </div>

                    <div class="subscription-details">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Subscription ID</span>
                                <span class="detail-value monospace"><?php echo htmlspecialchars($ai_subscription['subscription_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">$<?php echo number_format($ai_subscription['amount'], 2); ?> <?php echo $ai_subscription['currency']; ?>/<?php echo $ai_subscription['billing_cycle'] === 'yearly' ? 'year' : 'month'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Started</span>
                                <span class="detail-value"><?php echo date('F j, Y', strtotime($ai_subscription['start_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><?php echo $ai_subscription['status'] === 'active' ? 'Next Billing Date' : 'Access Until'; ?></span>
                                <span class="detail-value"><?php echo date('F j, Y', strtotime($ai_subscription['end_date'])); ?></span>
                            </div>
                            <?php if ($ai_subscription['discount_applied']): ?>
                            <div class="detail-item">
                                <span class="detail-label">Discount</span>
                                <span class="detail-value discount">$20 Premium Discount Applied</span>
                            </div>
                            <?php endif; ?>
                            <?php
                            $creditBalance = floatval($ai_subscription['credit_balance'] ?? 0);
                            $originalCredit = floatval($ai_subscription['original_credit'] ?? 0);
                            if ($creditBalance > 0):
                                $monthsRemaining = floor($creditBalance / 5); // $5/month
                            ?>
                            <div class="detail-item">
                                <span class="detail-label">Credit Balance</span>
                                <span class="detail-value credit-balance">$<?php echo number_format($creditBalance, 2); ?> CAD</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Months Covered</span>
                                <span class="detail-value"><?php echo $monthsRemaining; ?> month<?php echo $monthsRemaining !== 1 ? 's' : ''; ?> remaining</span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <span class="detail-label">Payment Method</span>
                                <span class="detail-value"><?php echo ucfirst($ai_subscription['payment_method']); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($creditBalance > 0 && $ai_subscription['status'] === 'active'): ?>
                        <div class="subscription-notice credit-notice">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <div>
                                <p><strong>Credit Balance Active</strong></p>
                                <p class="notice-detail">You have $<?php echo number_format($creditBalance, 2); ?> in credit covering your next <?php echo $monthsRemaining; ?> month<?php echo $monthsRemaining !== 1 ? 's' : ''; ?>. You won't be charged until your credit is depleted.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($ai_subscription['status'] === 'active'): ?>
                        <div class="subscription-actions">
                            <a href="cancel-subscription.php" class="btn btn-outline-red btn-cancel">Cancel Subscription</a>
                        </div>
                    <?php elseif ($ai_subscription['status'] === 'cancelled'): ?>
                        <div class="subscription-notice cancelled">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <div>
                                <p>Your subscription has been cancelled.</p>
                                <p class="notice-detail">AI features will remain active until <strong><?php echo date('F j, Y', strtotime($ai_subscription['end_date'])); ?></strong>.</p>
                            </div>
                        </div>
                        <div class="subscription-actions">
                            <?php if (strtotime($ai_subscription['end_date']) > time()): ?>
                                <a href="reactivate-subscription.php" class="btn btn-purple btn-reactivate">Reactivate Subscription</a>
                            <?php else: ?>
                                <a href="../../upgrade/ai/" class="btn btn-purple">Resubscribe</a>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($ai_subscription['status'] === 'payment_failed'): ?>
                        <div class="subscription-notice payment-failed">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <div>
                                <p><strong>Payment Failed</strong></p>
                                <p class="notice-detail">We were unable to process your renewal payment. Please update your payment method to continue your subscription.</p>
                            </div>
                        </div>
                        <div class="subscription-actions payment-failed-actions">
                            <a href="../../upgrade/ai/" class="btn btn-purple">Update Payment Method</a>
                            <a href="reactivate-subscription.php" class="btn btn-outline">Retry with Existing Method</a>
                        </div>
                    <?php elseif ($ai_subscription['status'] === 'expired'): ?>
                        <div class="subscription-notice expired">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <div>
                                <p>Your subscription has expired.</p>
                                <p class="notice-detail">Renew to continue using AI-powered features.</p>
                            </div>
                        </div>
                        <div class="subscription-actions">
                            <a href="../../upgrade/ai/" class="btn btn-blue">Renew Subscription</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Features Included -->
                <div class="features-section">
                    <h3>Features Included</h3>
                    <div class="features-grid">
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                            </svg>
                            <span>AI Receipt Scanning</span>
                        </div>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>Predictive Analysis</span>
                        </div>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <span>AI Business Insights</span>
                        </div>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <span>Natural Language Search</span>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="no-subscription-card">
                    <div class="no-subscription-icon">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3>No Active Subscription</h3>
                    <p>Get access to AI-powered features like receipt scanning, predictive analysis, and natural language search.</p>
                    <div class="pricing-preview">
                        <span class="price">$5</span>
                        <span class="period">CAD/month</span>
                        <span class="divider">or</span>
                        <span class="price">$50</span>
                        <span class="period">CAD/year (save $10)</span>
                    </div>
                    <a href="../../upgrade/ai/" class="btn btn-purple btn-subscribe">Subscribe to AI Features</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment History Section -->
        <div class="subscription-section">
            <h2>Payment History</h2>

            <?php if (!empty($payment_history)): ?>
                <div class="payment-history-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_history as $payment): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                <td>
                                    <span class="payment-type <?php echo $payment['payment_type'] ?? 'initial'; ?>">
                                        <?php
                                        $paymentTypeDisplay = $payment['payment_type'] ?? 'Initial';
                                        if ($paymentTypeDisplay === 'credit') {
                                            echo 'Credit Applied';
                                        } else {
                                            echo ucfirst($paymentTypeDisplay);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (floatval($payment['amount']) == 0 && ($payment['payment_type'] ?? '') === 'credit'): ?>
                                        <span class="credit-payment">$0.00</span>
                                    <?php else: ?>
                                        $<?php echo number_format($payment['amount'], 2); ?> <?php echo $payment['currency']; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td>
                                    <span class="payment-status <?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-payment-history">
                    <p>No payment history available.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Help Section -->
        <div class="subscription-section help-section">
            <h2>Need Help?</h2>
            <p>If you have questions about your subscription or need assistance, please contact our support team.</p>
            <a href="../../contact-us/" class="btn btn-outline">Contact Support</a>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
