<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
require_once 'user_functions.php';

// Check for remember me cookie and auto-login user if valid
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    check_remember_me();
}

require_login();

$is_logged_in = isset($_SESSION['user_id']);
$requested_username = isset($_GET['username']) ? trim($_GET['username']) : '';
$is_own_profile = false;
$user = null;
$user_not_found = false;
$just_verified = isset($_SESSION['just_verified']) && $_SESSION['just_verified'];

// If the user was just verified, clear the flag
if ($just_verified) {
    unset($_SESSION['just_verified']);
}

if (empty($requested_username)) {
    // If no username specified, show current user's profile
    $user = get_user($_SESSION['user_id']);
    $is_own_profile = true;
} else {
    $db = get_db_connection();

    // MySQL prepared statement 
    $stmt = $db->prepare("SELECT * FROM community_users WHERE username = ?");
    $stmt->bind_param("s", $requested_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $is_own_profile = ($user['id'] == $_SESSION['user_id']);
    } else {
        $user_not_found = true;
    }
}

// If user found, get profile data
if ($user) {
    $db = get_db_connection();

    // MySQL prepared statement for getting post and comment counts
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT p.id) AS post_count,
            COUNT(DISTINCT c.id) AS comment_count
        FROM 
            community_users u
        LEFT JOIN 
            community_posts p ON u.id = p.user_id
        LEFT JOIN 
            community_comments c ON u.id = c.user_id
        WHERE 
            u.id = ?
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();

    if (!$profile) {
        // Create default profile if query fails
        $profile = [
            'post_count' => 0,
            'comment_count' => 0
        ];
    }

    // Calculate reputation based on votes
    // For post upvotes: +10 per upvote
    // For post downvotes: -5 per downvote
    // For downvoting others: -2 per downvote cast
    // For comment upvotes: +2 per upvote
    // For comment downvotes: -1 per downvote

    // First, calculate reputation from post votes received
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN v.vote_type = 1 THEN 10 ELSE -5 END), 0) as post_vote_rep
        FROM 
            community_posts p
        LEFT JOIN 
            community_votes v ON p.id = v.post_id
        WHERE 
            p.user_id = ? AND v.vote_type IS NOT NULL
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $post_rep_result = $result->fetch_assoc();
    $post_reputation = isset($post_rep_result['post_vote_rep']) ? $post_rep_result['post_vote_rep'] : 0;
    $stmt->close();

    // Calculate reputation from downvotes cast by user
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) * -2 as downvote_cost
        FROM 
            community_votes
        WHERE 
            user_id = ? AND vote_type = -1
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $downvote_result = $result->fetch_assoc();
    $downvote_reputation = isset($downvote_result['downvote_cost']) ? $downvote_result['downvote_cost'] : 0;
    $stmt->close();

    // Calculate reputation from comment votes
    $comment_reputation = 0;
    $comment_votes_exist = false;

    // Check if comment_votes table exists
    $result = $db->query("SHOW TABLES LIKE 'comment_votes'");
    if ($result->num_rows > 0) {
        $comment_votes_exist = true;

        // Calculate comment upvote reputation (+2 each)
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN cv.vote_type = 1 THEN 2 ELSE -1 END), 0) as comment_vote_rep
            FROM 
                community_comments c
            LEFT JOIN 
                comment_votes cv ON c.id = cv.comment_id
            WHERE 
                c.user_id = ? AND cv.vote_type IS NOT NULL
        ");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment_rep_result = $result->fetch_assoc();
        $comment_reputation = isset($comment_rep_result['comment_vote_rep']) ? $comment_rep_result['comment_vote_rep'] : 0;
        $stmt->close();
    }

    // Total reputation
    $reputation = $post_reputation + $downvote_reputation + $comment_reputation;

    // Calculate impact (total views on posts)
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(views), 0) AS people_reached
        FROM 
            community_posts
        WHERE 
            user_id = ?
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $impact_result = $result->fetch_assoc();
    $people_reached = isset($impact_result['people_reached']) ? $impact_result['people_reached'] : 0;
    $stmt->close();

    // Get votes cast
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) AS votes_cast
        FROM 
            community_votes
        WHERE 
            user_id = ?
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $votes_result = $result->fetch_assoc();
    $votes_cast = isset($votes_result['votes_cast']) ? $votes_result['votes_cast'] : 0;
    $stmt->close();

    // Get reputation history (will need to calculate from existing data)
    $reputation_history = [];

    // Post upvotes received - each worth +10
    $stmt = $db->prepare("
        SELECT 
            v.id,
            'post_upvote' as action_type,
            p.id as post_id,
            NULL as comment_id,
            v.created_at,
            p.title as post_title,
            10 as rep_change
        FROM 
            community_votes v
        JOIN 
            community_posts p ON v.post_id = p.id
        WHERE 
            p.user_id = ? AND v.vote_type = 1
        ORDER BY 
            v.created_at DESC
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reputation_history[] = $row;
    }
    $stmt->close();

    // Post downvotes received - each worth -5
    $stmt = $db->prepare("
        SELECT 
            v.id,
            'post_downvote' as action_type,
            p.id as post_id,
            NULL as comment_id,
            v.created_at,
            p.title as post_title,
            -5 as rep_change
        FROM 
            community_votes v
        JOIN 
            community_posts p ON v.post_id = p.id
        WHERE 
            p.user_id = ? AND v.vote_type = -1
        ORDER BY 
            v.created_at DESC
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reputation_history[] = $row;
    }
    $stmt->close();

    // Downvotes cast by user - each worth -2
    $stmt = $db->prepare("
        SELECT 
            v.id,
            'downvoted_other' as action_type,
            p.id as post_id,
            NULL as comment_id,
            v.created_at,
            p.title as post_title,
            -2 as rep_change
        FROM 
            community_votes v
        JOIN 
            community_posts p ON v.post_id = p.id
        WHERE 
            v.user_id = ? AND v.vote_type = -1
        ORDER BY 
            v.created_at DESC
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reputation_history[] = $row;
    }
    $stmt->close();

    // Add comment votes if the table exists
    if ($comment_votes_exist) {
        // Comment upvotes received - each worth +2
        $stmt = $db->prepare("
            SELECT 
                cv.id,
                'comment_upvote' as action_type,
                p.id as post_id,
                c.id as comment_id,
                cv.created_at,
                p.title as post_title,
                2 as rep_change
            FROM 
                comment_votes cv
            JOIN 
                community_comments c ON cv.comment_id = c.id
            JOIN
                community_posts p ON c.post_id = p.id
            WHERE 
                c.user_id = ? AND cv.vote_type = 1
            ORDER BY 
                cv.created_at DESC
        ");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reputation_history[] = $row;
        }
        $stmt->close();

        // Comment downvotes received - each worth -1
        $stmt = $db->prepare("
            SELECT 
                cv.id,
                'comment_downvote' as action_type,
                p.id as post_id,
                c.id as comment_id,
                cv.created_at,
                p.title as post_title,
                -1 as rep_change
            FROM 
                comment_votes cv
            JOIN 
                community_comments c ON cv.comment_id = c.id
            JOIN
                community_posts p ON c.post_id = p.id
            WHERE 
                c.user_id = ? AND cv.vote_type = -1
            ORDER BY 
                cv.created_at DESC
        ");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reputation_history[] = $row;
        }
        $stmt->close();
    }

    // Sort reputation history by date (newest first)
    usort($reputation_history, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Get user's posts with sorting options
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
    $sort_query = "";

    switch ($sort) {
        case 'score':
            $sort_query = "ORDER BY p.votes DESC";
            break;
        case 'oldest':
            $sort_query = "ORDER BY p.created_at ASC";
            break;
        case 'newest':
        default:
            $sort_query = "ORDER BY p.created_at DESC";
            break;
    }

    $query = "
        SELECT 
            p.id,
            p.title,
            p.content,
            p.post_type,
            p.status,
            p.votes,
            p.views,
            p.created_at
        FROM 
            community_posts p
        WHERE 
            p.user_id = ?
        $sort_query
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $user_posts = [];
    while ($row = $result->fetch_assoc()) {
        $user_posts[] = $row;
    }
    $stmt->close();

    // Get user's comments
    $comment_sort = isset($_GET['comment_sort']) ? $_GET['comment_sort'] : 'newest';
    $comment_sort_query = "";

    switch ($comment_sort) {
        case 'score':
            $comment_sort_query = "ORDER BY c.votes DESC";
            break;
        case 'oldest':
            $comment_sort_query = "ORDER BY c.created_at ASC";
            break;
        case 'newest':
        default:
            $comment_sort_query = "ORDER BY c.created_at DESC";
            break;
    }

    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.content,
            c.created_at,
            c.votes,
            c.post_id,
            p.title as post_title
        FROM 
            community_comments c
        JOIN
            community_posts p ON c.post_id = p.id
        WHERE 
            c.user_id = ?
        $comment_sort_query
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $user_comments = [];
    while ($row = $result->fetch_assoc()) {
        $user_comments[] = $row;
    }
    $stmt->close();

    // Prepare data for reputation chart
    $rep_by_date = [];
    $running_total = 0;

    // Sort reputation history by date (oldest first for charting)
    usort($reputation_history, function ($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });

    // Group by date and calculate running total
    foreach ($reputation_history as $rep_item) {
        $date = date('Y-m-d', strtotime($rep_item['created_at']));
        $rep_value = isset($rep_item['rep_change']) ? $rep_item['rep_change'] : 0;

        $running_total += $rep_value;

        if (!isset($rep_by_date[$date])) {
            $rep_by_date[$date] = $running_total;
        } else {
            $rep_by_date[$date] = $running_total;
        }
    }

    // Convert to JSON for the chart
    $chart_data = [];
    foreach ($rep_by_date as $date => $total) {
        $chart_data[] = [
            'date' => $date,
            'reputation' => $total
        ];
    }
    $chart_json = json_encode($chart_data);

    // Handle profile update (if own profile)
    $success_message = '';
    $error_message = '';

    if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle profile picture upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $avatar_result = upload_avatar($_SESSION['user_id'], $_FILES['avatar']);
            if ($avatar_result) {
                $user['avatar'] = $avatar_result;
                $success_message = 'Profile picture updated successfully.';
            } else {
                $error_message = 'Failed to upload profile picture. Please ensure it is a valid image (JPG, PNG, GIF) under 2MB.';
            }
        }
    }

    $is_admin = isset($user['role']) && $user['role'] === 'admin';
}

// Check if user has a license key (by user_id or case-insensitive email match)
$has_license = false;
if ($is_own_profile) {
    $db = get_db_connection();
    // Check by user_id first, then by email
    $stmt = $db->prepare('SELECT license_key FROM license_keys WHERE user_id = ? OR LOWER(email) = LOWER(?) LIMIT 1');
    $user_id_check = $user['id'];
    $email_check = $user['email'] ?? '';
    $stmt->bind_param('is', $user_id_check, $email_check);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_license = ($result->num_rows > 0);
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title><?php echo $user_not_found ? 'User Not Found' : htmlspecialchars($user['username']) . "'s Profile"; ?> - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/utc-to-local.js" defer></script>
    <script src="../../resources/notifications/notifications.js" defer></script>

    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
    <link rel="stylesheet" href="../../resources/notifications/notifications.css">
    <link rel="stylesheet" href="../report/report.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <?php if ($user_not_found): ?>
        <!-- User Not Found Display -->
        <div class="profile-container">
            <div class="user-not-found">
                <div class="not-found-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h2>The user "<?php echo htmlspecialchars($requested_username); ?>" could not be found</h2>
                <p>The username you are looking for does not exist or may have been removed.</p>
                <div class="not-found-actions">
                    <a href="../index.php" class="btn btn-blue">Return to Community</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Normal Profile Display -->
        <div class="profile-container">
            <div class="profile-header">
                <h1>
                    <?php echo htmlspecialchars($user['username']); ?>
                    <?php if ($is_admin): ?>
                        <span class="admin-badge">Admin</span>
                    <?php endif; ?>
                    <?php if ($is_logged_in && !$is_own_profile): ?>
                        <button class="report-btn report-btn-user" data-content-type="user" data-content-id="<?php echo $user['id']; ?>" title="Report this user">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                        </button>
                    <?php endif; ?>
                </h1>
            </div>

            <?php if ($just_verified): ?>
                <div class="container">
                    <div class="success-message">
                        <strong>Email verified successfully!</strong> Your account has been created.
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="profile-grid">
                <div class="profile-sidebar">
                    <div class="profile-card">
                        <form method="post" enctype="multipart/form-data" id="avatar-form">
                            <div class="profile-avatar <?php echo $is_own_profile ? 'editable' : ''; ?>" id="profile-avatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s avatar" id="avatar-preview">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder" id="avatar-placeholder">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($is_own_profile): ?>
                                    <div class="avatar-overlay">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                            <circle cx="12" cy="13" r="4"></circle>
                                        </svg>
                                        <span>Change Photo</span>
                                    </div>
                                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" style="display: none;">
                                <?php endif; ?>
                            </div>
                        </form>

                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                            <div class="profile-meta">
                                <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="profile-joined">
                                    Joined <?php echo date('F Y', strtotime($user['created_at'])); ?>
                                </p>
                            </div>

                            <?php if (!empty(trim($user['bio']))): ?>
                                <h3 class="profile-bio-title">BIO</h3>
                                <div class="profile-bio">
                                    <?php echo nl2br(htmlspecialchars($user['bio'])); ?>
                                </div>
                            <?php elseif ($is_own_profile): ?>
                                <h3 class="profile-bio-title">BIO</h3>
                                <div class="profile-bio empty">
                                    Add a bio to tell others about yourself!
                                </div>
                            <?php endif; ?>

                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo number_format($reputation); ?></span>
                                    <span class="stat-label">Reputation</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $profile['post_count']; ?></span>
                                    <span class="stat-label"><?php echo $profile['post_count'] === 1 ? 'Post' : 'Posts'; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $profile['comment_count']; ?></span>
                                    <span class="stat-label"><?php echo $profile['comment_count'] === 1 ? 'Comment' : 'Comments'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <?php if ($is_own_profile): ?>
                                <?php if ($has_license): ?>
                                    <a href="resend_license.php" class="btn btn-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        Resend License Key
                                    </a>
                                <?php endif; ?>

                                <?php if ($is_admin): ?>
                                    <a href="admin_notification_settings.php" class="btn btn-blue">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                        Notification Settings
                                    </a>
                                <?php endif; ?>

                                <a href="ai-subscription.php" class="btn btn-blue">
                                    Manage Subscription
                                </a>
                                <a href="edit_profile.php" class="btn btn-blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Edit Account
                                </a>
                                <a href="logout.php" class="btn btn-gray">Log Out</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Impact Section -->
                    <div class="impact-section">
                        <h3>Impact</h3>
                        <div class="impact-stats">
                            <div class="impact-stat">
                                <div class="impact-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="impact-data">
                                    <span class="impact-value"><?php echo number_format($people_reached); ?></span>
                                    <span class="impact-label">people reached</span>
                                </div>
                            </div>
                            <div class="impact-stat">
                                <div class="impact-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 17l9.2-9.2M17 17V7H7"></path>
                                    </svg>
                                </div>
                                <div class="impact-data">
                                    <span class="impact-value"><?php echo number_format($votes_cast); ?></span>
                                    <span class="impact-label"><?php echo $votes_cast === 1 ? 'vote cast' : 'votes cast'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-content">
                    <!-- Reputation Chart Section -->
                    <div class="reputation-chart-section">
                        <h3>Reputation Overview
                            <a href="reputation_help.php" class="reputation-help-link" title="Learn about the reputation system">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </a>
                        </h3>
                        <div class="reputation-container">
                            <?php if (empty($chart_data)): ?>
                                <div class="empty-state">
                                    <p>No reputation activity yet</p>
                                </div>
                            <?php else: ?>
                                <div class="reputation-chart-container">
                                    <h3>Reputation Chart</h3>
                                    <canvas id="reputationChart"></canvas>
                                </div>
                                <div class="reputation-history-section">
                                    <h3>Recent Reputation Changes</h3>
                                    <?php if (empty($reputation_history)): ?>
                                        <div class="empty-state">
                                            <p>No reputation activity yet</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="reputation-history">
                                            <?php foreach (array_slice($reputation_history, 0, 5) as $rep_item): ?>
                                                <?php
                                                $rep_value = isset($rep_item['rep_change']) ? $rep_item['rep_change'] : 0;
                                                $rep_description = '';

                                                switch ($rep_item['action_type']) {
                                                    case 'post_upvote':
                                                        $rep_description = 'upvote on post';
                                                        break;
                                                    case 'post_downvote':
                                                        $rep_description = 'downvote on post';
                                                        break;
                                                    case 'downvoted_other':
                                                        $rep_description = 'downvoted someone else\'s post';
                                                        break;
                                                    case 'comment_upvote':
                                                        $rep_description = 'upvote on comment';
                                                        break;
                                                    case 'comment_downvote':
                                                        $rep_description = 'downvote on comment';
                                                        break;
                                                }
                                                ?>
                                                <div class="reputation-item">
                                                    <div class="reputation-value <?php echo $rep_value >= 0 ? 'positive' : 'negative'; ?>">
                                                        <?php echo $rep_value > 0 ? '+' . $rep_value : $rep_value; ?>
                                                    </div>
                                                    <div class="reputation-details">
                                                        <div class="reputation-description">
                                                            <?php echo htmlspecialchars($rep_description); ?>
                                                            <?php if (!empty($rep_item['post_title'])): ?>
                                                                <a href="../view_post.php?id=<?php echo $rep_item['post_id']; ?>">
                                                                    <?php echo htmlspecialchars($rep_item['post_title']); ?>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Date -->
                                                        <div class="reputation-date" data-utc-timestamp="<?php echo $rep_item['created_at']; ?>" data-format="full">
                                                            <?php echo date('M j, Y g:i a', strtotime($rep_item['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('reputationChart').getContext('2d');
                                        const chartData = <?php echo $chart_json; ?>;

                                        const labels = chartData.map(item => item.date);
                                        const data = chartData.map(item => item.reputation);

                                        const chart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                    label: 'Reputation',
                                                    data: data,
                                                    borderColor: '#3b82f6',
                                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                    fill: true,
                                                    tension: 0.1,
                                                    pointRadius: 3,
                                                    pointBackgroundColor: '#3b82f6',
                                                    pointBorderColor: '#fff',
                                                    pointBorderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                layout: {
                                                    padding: {
                                                        top: 5,
                                                        right: 10,
                                                        bottom: 40,
                                                        left: 10
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            title: function(context) {
                                                                const date = new Date(context[0].label);
                                                                return date.toLocaleDateString('en-US', {
                                                                    year: 'numeric',
                                                                    month: 'short',
                                                                    day: 'numeric'
                                                                });
                                                            },
                                                            label: function(context) {
                                                                return 'Reputation: ' + context.raw;
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    x: {
                                                        grid: {
                                                            display: false
                                                        },
                                                        ticks: {
                                                            maxRotation: 0,
                                                            autoSkip: true,
                                                            maxTicksLimit: 5
                                                        }
                                                    },
                                                    y: {
                                                        beginAtZero: true,
                                                        grid: {
                                                            color: 'rgba(0, 0, 0, 0.05)'
                                                        },
                                                        ticks: {
                                                            padding: 5,
                                                            precision: 0 // Integer values only
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                </script>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Posts and Comments Container -->
                    <div class="posts-comments-container">
                        <!-- User Posts Section -->
                        <div class="posts-section">
                            <div class="section-header">
                                <h3>Posts</h3>
                                <div class="sort-controls">
                                    <span class="sort-label">Sort by:</span>
                                    <div class="sort-options">
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?>sort=score" class="sort-option <?php echo $sort === 'score' ? 'active' : ''; ?>">Score</a>
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?>sort=newest" class="sort-option <?php echo $sort === 'newest' ? 'active' : ''; ?>">Newest</a>
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?>sort=oldest" class="sort-option <?php echo $sort === 'oldest' ? 'active' : ''; ?>">Oldest</a>
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($user_posts)): ?>
                                <div class="empty-state">
                                    <p>No posts yet</p>
                                </div>
                            <?php else: ?>
                                <div class="user-posts-list">
                                    <?php foreach ($user_posts as $post): ?>
                                        <div class="user-post-item">
                                            <div class="post-score">
                                                <span class="score-value"><?php echo $post['votes']; ?></span>
                                                <span class="score-label"><?php echo abs($post['votes']) === 1 ? 'vote' : 'votes'; ?></span>
                                            </div>
                                            <div class="post-details">
                                                <a href="../view_post.php?id=<?php echo $post['id']; ?>" class="post-title">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </a>
                                                <div class="post-meta-info">
                                                    <span class="post-type <?php echo $post['post_type']; ?>">
                                                        <?php echo $post['post_type'] === 'bug' ? 'Bug Report' : 'Feature Request'; ?>
                                                    </span>
                                                    <span class="post-status <?php echo $post['status']; ?>">
                                                        <?php
                                                        switch ($post['status']) {
                                                            case 'open':
                                                                echo 'Open';
                                                                break;
                                                            case 'in_progress':
                                                                echo 'In Progress';
                                                                break;
                                                            case 'completed':
                                                                echo 'Completed';
                                                                break;
                                                            case 'declined':
                                                                echo 'Declined';
                                                                break;
                                                        }
                                                        ?>
                                                    </span>
                                                    <span class="post-views">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        <?php echo number_format($post['views']); ?> <?php echo $post['views'] === 1 ? 'view' : 'views'; ?>
                                                    </span>

                                                    <!-- Date -->
                                                    <span class="post-date" data-timestamp="<?php echo strtotime($post['created_at']); ?>">
                                                        <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comments Section -->
                        <div id="comments-section" class="comments-section">
                            <div class="section-header">
                                <h3>Comments</h3>
                                <div class="sort-controls">
                                    <span class="sort-label">Sort by:</span>
                                    <div class="sort-options">
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?><?php echo !empty($sort) ? 'sort=' . $sort . '&' : ''; ?>comment_sort=score" class="sort-option <?php echo $comment_sort === 'score' ? 'active' : ''; ?>">Score</a>
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?><?php echo !empty($sort) ? 'sort=' . $sort . '&' : ''; ?>comment_sort=newest" class="sort-option <?php echo $comment_sort === 'newest' ? 'active' : ''; ?>">Newest</a>
                                        <a href="?<?php echo !empty($requested_username) ? 'username=' . urlencode($requested_username) . '&' : ''; ?><?php echo !empty($sort) ? 'sort=' . $sort . '&' : ''; ?>comment_sort=oldest" class="sort-option <?php echo $comment_sort === 'oldest' ? 'active' : ''; ?>">Oldest</a>
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($user_comments)): ?>
                                <div class="empty-state">
                                    <p>No comments yet</p>
                                </div>
                            <?php else: ?>
                                <div class="user-comments-list">
                                    <?php foreach ($user_comments as $comment): ?>
                                        <div class="user-comment-item">
                                            <div class="comment-score">
                                                <span class="score-value"><?php echo isset($comment['votes']) ? $comment['votes'] : 0; ?></span>
                                                <span class="score-label"><?php echo isset($comment['votes']) && abs($comment['votes']) === 1 ? 'vote' : 'votes'; ?></span>
                                            </div>
                                            <div class="comment-details">
                                                <div class="comment-content">
                                                    <?php
                                                    $comment_text = htmlspecialchars($comment['content']);
                                                    echo strlen($comment_text) > 150 ? substr($comment_text, 0, 150) . '...' : $comment_text;
                                                    ?>
                                                </div>
                                                <div class="comment-meta-info">
                                                    <span class="comment-on">
                                                        on post: <a href="../view_post.php?id=<?php echo $comment['post_id']; ?>" class="comment-link">
                                                            <?php echo htmlspecialchars($comment['post_title']); ?>
                                                        </a>
                                                    </span>
                                                    <span class="comment-date">
                                                        <?php echo date('M j, Y', strtotime($comment['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Full Reputation History Section -->
                    <?php if (count($reputation_history) > 5): ?>
                        <div class="reputation-section">
                            <h3>All Reputation History</h3>
                            <div class="reputation-history">
                                <?php foreach ($reputation_history as $rep_item): ?>
                                    <?php
                                    $rep_value = isset($rep_item['rep_change']) ? $rep_item['rep_change'] : 0;
                                    $rep_description = '';

                                    switch ($rep_item['action_type']) {
                                        case 'post_upvote':
                                            $rep_description = 'upvote on post';
                                            break;
                                        case 'post_downvote':
                                            $rep_description = 'downvote on post';
                                            break;
                                        case 'downvoted_other':
                                            $rep_description = 'downvoted someone else\'s post';
                                            break;
                                        case 'comment_upvote':
                                            $rep_description = 'upvote on comment';
                                            break;
                                        case 'comment_downvote':
                                            $rep_description = 'downvote on comment';
                                            break;
                                    }
                                    ?>
                                    <div class="reputation-item">
                                        <div class="reputation-value <?php echo $rep_value >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $rep_value > 0 ? '+' . $rep_value : $rep_value; ?>
                                        </div>
                                        <div class="reputation-details">
                                            <div class="reputation-description">
                                                <?php echo htmlspecialchars($rep_description); ?>
                                                <?php if (!empty($rep_item['post_title'])): ?>
                                                    "<a class="link" href="../view_post.php?id=<?php echo $rep_item['post_id']; ?>">
                                                        <?php echo htmlspecialchars($rep_item['post_title']); ?>
                                                    </a>"
                                                <?php endif; ?>
                                            </div>

                                            <!-- Date -->
                                            <div class="reputation-date" data-timestamp="<?php echo strtotime($rep_item['created_at']); ?>">
                                                <?php echo date('M j, Y g:i a', strtotime($rep_item['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <!-- Report Modal -->
    <div id="reportModal" class="report-modal" style="display: none;">
        <div class="report-modal-content">
            <div class="report-modal-header">
                <h3>Report User</h3>
                <button class="report-modal-close">&times;</button>
            </div>
            <form id="reportForm">
                <input type="hidden" id="reportContentType" name="content_type">
                <input type="hidden" id="reportContentId" name="content_id">

                <div class="form-group">
                    <label for="violationType">Reason for reporting:</label>
                    <select id="violationType" name="violation_type" required>
                        <option value="">Select a reason...</option>
                        <option value="inappropriate_username">Inappropriate username</option>
                        <option value="inappropriate_bio">Inappropriate bio</option>
                        <option value="impersonation">Impersonation</option>
                        <option value="harassment">Harassment</option>
                        <option value="spam">Spam</option>
                        <option value="hateful">Hateful content</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="additionalInfo">Additional information (optional):</label>
                    <textarea id="additionalInfo" name="additional_info" rows="4" placeholder="Please provide any additional details..."></textarea>
                </div>

                <div class="report-modal-actions">
                    <button type="button" class="btn btn-outline report-modal-cancel">Cancel</button>
                    <button type="submit" class="btn btn-red">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Report functionality for profile page
        (function() {
            'use strict';

            const modal = document.getElementById('reportModal');
            const form = document.getElementById('reportForm');
            const closeBtn = document.querySelector('.report-modal-close');
            const cancelBtn = document.querySelector('.report-modal-cancel');
            const contentTypeInput = document.getElementById('reportContentType');
            const contentIdInput = document.getElementById('reportContentId');

            // Open modal when report button is clicked
            document.addEventListener('click', function(e) {
                if (e.target.closest('.report-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.report-btn');
                    const contentType = btn.getAttribute('data-content-type');
                    const contentId = btn.getAttribute('data-content-id');

                    // Set hidden form fields
                    contentTypeInput.value = contentType;
                    contentIdInput.value = contentId;

                    // Show modal
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            });

            // Close modal functions
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                form.reset();
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeModal);
            }

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';

                fetch('../report/report_content.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success notification
                        if (typeof showNotification === 'function') {
                            showNotification('Report submitted successfully. Our team will review it shortly.', 'success');
                        } else {
                            alert('Report submitted successfully. Our team will review it shortly.');
                        }
                        closeModal();
                    } else {
                        // Show error notification
                        if (typeof showNotification === 'function') {
                            showNotification(data.message || 'Failed to submit report. Please try again.', 'error');
                        } else {
                            alert(data.message || 'Failed to submit report. Please try again.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error submitting report:', error);
                    if (typeof showNotification === 'function') {
                        showNotification('An error occurred. Please try again later.', 'error');
                    } else {
                        alert('An error occurred. Please try again later.');
                    }
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });

        })();
    </script>
    <script>
        // Profile Avatar Change Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileAvatar = document.getElementById('profile-avatar');
            const avatarInput = document.getElementById('avatar');
            const profileForm = document.getElementById('profile-form');

            // Only add click handler if user can edit (own profile)
            if (profileAvatar && profileAvatar.classList.contains('editable') && avatarInput) {
                // Click handler for avatar overlay
                profileAvatar.addEventListener('click', function() {
                    avatarInput.click();
                });

                // File change handler
                avatarInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        // Auto-submit the form when file is selected
                        if (profileForm) {
                            profileForm.submit();
                        } else {
                            // If no form found, create one and submit
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.enctype = 'multipart/form-data';

                            // Move the input to the form
                            form.appendChild(this);

                            // Add to document and submit
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>