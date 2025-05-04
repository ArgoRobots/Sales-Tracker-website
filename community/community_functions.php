<?php

/**
 * Helper functions for the community forum
 */

/**
 * Get all posts with vote counts, ordered by creation date (newest first)
 * 
 * @return array Array of posts
 */
function get_all_posts()
{
    $db = get_db_connection();

    // Join with users table to get avatar
    $result = $db->query('SELECT p.*, u.avatar FROM community_posts p 
                         LEFT JOIN community_users u ON p.user_id = u.id 
                         ORDER BY p.created_at DESC');

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    return $posts;
}

/**
 * Get a single post by ID
 * 
 * @param int $post_id Post ID
 * @return array|false Post data or false if not found
 */
function get_post($post_id)
{
    $db = get_db_connection();

    // Join with users table to get avatar
    $stmt = $db->prepare('SELECT p.*, u.avatar FROM community_posts p 
                         LEFT JOIN community_users u ON p.user_id = u.id 
                         WHERE p.id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    $stmt->close();
    return $post;
}

/**
 * Add a new post
 * 
 * @param string $user_name User's name
 * @param string $user_email User's email
 * @param string $title Post title
 * @param string $content Post content
 * @param string $post_type Post type ('bug' or 'feature')
 * @return int|false New post ID or false on failure
 */
function add_post($user_id, $user_name, $user_email, $title, $content, $post_type)
{
    $db = get_db_connection();

    $stmt = $db->prepare('INSERT INTO community_posts 
        (user_id, user_name, user_email, title, content, post_type, views) 
        VALUES (?, ?, ?, ?, ?, ?, 0)');

    $stmt->bind_param('isssss', $user_id, $user_name, $user_email, $title, $content, $post_type);

    if ($stmt->execute()) {
        $post_id = $db->insert_id;
        $stmt->close();

        // Send notification email
        send_notification_email('new_post', [
            'id' => $post_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'title' => $title,
            'post_type' => $post_type
        ]);

        return $post_id;
    }

    $stmt->close();
    return false;
}

/**
 * Update a post's status
 * 
 * @param int $post_id Post ID
 * @param string $status New status ('open', 'in_progress', 'completed', 'declined')
 * @return bool Success status
 */
function update_post_status($post_id, $status)
{
    $db = get_db_connection();

    $stmt = $db->prepare('UPDATE community_posts SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->bind_param('si', $status, $post_id);
    $result = $stmt->execute();

    $stmt->close();
    return $result;
}

/**
 * Delete a post
 * 
 * @param int $post_id Post ID
 * @return bool Success status
 */
function delete_post($post_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('DELETE FROM community_posts WHERE id = ?');
    $stmt->bind_param('i', $post_id);
    $result = $stmt->execute();

    $stmt->close();
    return $result;
}

/**
 * Get all comments for a post
 * 
 * @param int $post_id Post ID
 * @return array Array of comments
 */
function get_post_comments($post_id)
{
    $db = get_db_connection();

    // Join with users table to get avatar
    $stmt = $db->prepare('SELECT c.*, u.avatar FROM community_comments c 
                         LEFT JOIN community_users u ON c.user_id = u.id 
                         WHERE c.post_id = ? ORDER BY c.created_at ASC');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    $stmt->close();
    return $comments;
}

/**
 * Get comment count for a post
 * 
 * @param int $post_id Post ID
 * @return int Comment count
 */
function get_comment_count($post_id)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT COUNT(*) as count FROM community_comments WHERE post_id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];

    $stmt->close();
    return $count;
}

/**
 * Add a new comment
 * 
 * @param int $post_id Post ID
 * @param string $user_name User's name
 * @param string $user_email User's email
 * @param string $content Comment content
 * @return array|false New comment data or false on failure
 */
function add_comment($post_id, $user_name, $user_email, $content)
{
    $db = get_db_connection();

    $stmt = $db->prepare('INSERT INTO community_comments (post_id, user_name, user_email, content, votes) 
                         VALUES (?, ?, ?, ?, 0)');
    $stmt->bind_param('isss', $post_id, $user_name, $user_email, $content);

    if ($stmt->execute()) {
        $comment_id = $db->insert_id;

        // Get the post data
        $post = get_post($post_id);

        // Get the new comment
        $stmt = $db->prepare('SELECT * FROM community_comments WHERE id = ?');
        $stmt->bind_param('i', $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $new_comment = $result->fetch_assoc();

        $stmt->close();

        // Send notification email
        send_notification_email('new_comment', [
            'id' => $comment_id,
            'post_id' => $post_id,
            'post_title' => $post['title'],
            'user_name' => $user_name,
            'user_email' => $user_email
        ]);

        return $new_comment;
    }

    $stmt->close();
    return false;
}

/**
 * Delete a comment
 * 
 * @param int $comment_id Comment ID
 * @return array|false Post ID and success status or false on failure
 */
function delete_comment($comment_id)
{
    $db = get_db_connection();

    // Get the post_id before deleting
    $stmt = $db->prepare('SELECT post_id FROM community_comments WHERE id = ?');
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        $stmt->close();
        return false;
    }

    $post_id = $row['post_id'];

    // Delete the comment
    $stmt = $db->prepare('DELETE FROM community_comments WHERE id = ?');
    $stmt->bind_param('i', $comment_id);

    if ($stmt->execute()) {
        $stmt->close();
        return [
            'post_id' => $post_id,
            'success' => true
        ];
    }

    $stmt->close();
    return false;
}

/**
 * Add or update a vote
 * 
 * @param int $post_id Post ID
 * @param string $user_email User's email
 * @param int $vote_type Vote type (1 for upvote, -1 for downvote)
 * @return array|false New vote count and user's vote or false on failure
 */
function vote_post($post_id, $user_email, $vote_type)
{
    $db = get_db_connection();

    // Check if user has already voted
    $stmt = $db->prepare('SELECT vote_type FROM community_votes WHERE post_id = ? AND user_email = ?');
    $stmt->bind_param('is', $post_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_vote = $result->fetch_assoc();

    if ($existing_vote) {
        // User already voted, check if they're changing their vote
        if ($existing_vote['vote_type'] == $vote_type) {
            // Remove the vote (cancel)
            $stmt = $db->prepare('DELETE FROM community_votes WHERE post_id = ? AND user_email = ?');
            $stmt->bind_param('is', $post_id, $user_email);
            $stmt->execute();

            $user_vote = 0;
        } else {
            // Update the vote
            $stmt = $db->prepare('UPDATE community_votes SET vote_type = ?, created_at = CURRENT_TIMESTAMP 
                                 WHERE post_id = ? AND user_email = ?');
            $stmt->bind_param('iis', $vote_type, $post_id, $user_email);
            $stmt->execute();

            $user_vote = $vote_type;
        }
    } else {
        // Add new vote
        $stmt = $db->prepare('INSERT INTO community_votes (post_id, user_email, vote_type) 
                             VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $post_id, $user_email, $vote_type);
        $stmt->execute();

        $user_vote = $vote_type;
    }

    // Update vote count in posts table
    $stmt = $db->prepare('SELECT SUM(vote_type) as total_votes FROM community_votes WHERE post_id = ?');
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $total_votes = $row['total_votes'] ?? 0;

    // Update the post's vote count
    $stmt = $db->prepare('UPDATE community_posts SET votes = ? WHERE id = ?');
    $stmt->bind_param('ii', $total_votes, $post_id);
    $stmt->execute();

    $stmt->close();

    return [
        'new_vote_count' => $total_votes,
        'user_vote' => $user_vote
    ];
}

/**
 * Get user's vote for a post
 * 
 * @param int $post_id Post ID
 * @param string $user_email User's email
 * @return int|false Vote type (1, -1) or 0 if no vote, or false on failure
 */
function get_user_vote($post_id, $user_email)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT vote_type FROM community_votes WHERE post_id = ? AND user_email = ?');
    $stmt->bind_param('is', $post_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $vote = $row ? $row['vote_type'] : 0;

    $stmt->close();
    return $vote;
}

/**
 * Add or update a vote on a comment
 * 
 * @param int $comment_id Comment ID
 * @param string $user_email User's email
 * @param int $vote_type Vote type (1 for upvote, -1 for downvote)
 * @return array|false New vote count and user's vote or false on failure
 */
function vote_comment($comment_id, $user_email, $vote_type)
{
    $db = get_db_connection();

    // Check if user has already voted
    $stmt = $db->prepare('SELECT vote_type FROM comment_votes WHERE comment_id = ? AND user_email = ?');
    $stmt->bind_param('is', $comment_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_vote = $result->fetch_assoc();

    if ($existing_vote) {
        // User already voted, check if they're changing their vote
        if ($existing_vote['vote_type'] == $vote_type) {
            // Remove the vote (cancel)
            $stmt = $db->prepare('DELETE FROM comment_votes WHERE comment_id = ? AND user_email = ?');
            $stmt->bind_param('is', $comment_id, $user_email);
            $stmt->execute();

            $user_vote = 0;
        } else {
            // Update the vote
            $stmt = $db->prepare('UPDATE comment_votes SET vote_type = ?, created_at = CURRENT_TIMESTAMP 
                                 WHERE comment_id = ? AND user_email = ?');
            $stmt->bind_param('iis', $vote_type, $comment_id, $user_email);
            $stmt->execute();

            $user_vote = $vote_type;
        }
    } else {
        // Add new vote
        $stmt = $db->prepare('INSERT INTO comment_votes (comment_id, user_email, vote_type) 
                             VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $comment_id, $user_email, $vote_type);
        $stmt->execute();

        $user_vote = $vote_type;
    }

    // Update vote count in comments table
    $stmt = $db->prepare('SELECT SUM(vote_type) as total_votes FROM comment_votes WHERE comment_id = ?');
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $total_votes = $row['total_votes'] ?? 0;

    // Update the comment's vote count
    $stmt = $db->prepare('UPDATE community_comments SET votes = ? WHERE id = ?');
    $stmt->bind_param('ii', $total_votes, $comment_id);
    $stmt->execute();

    $stmt->close();

    return [
        'new_vote_count' => $total_votes,
        'user_vote' => $user_vote
    ];
}

/**
 * Get user's vote for a comment
 * 
 * @param int $comment_id Comment ID
 * @param string $user_email User's email
 * @return int Vote type (1, -1) or 0 if no vote
 */
function get_user_comment_vote($comment_id, $user_email)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT vote_type FROM comment_votes WHERE comment_id = ? AND user_email = ?');
    $stmt->bind_param('is', $comment_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $vote = $row ? $row['vote_type'] : 0;

    $stmt->close();
    return $vote;
}

/**
 * Updated notification function for the community forum
 * Respects admin notification settings
 * 
 * @param string $type Notification type ('new_post', 'new_comment')
 * @param array $data Notification data
 * @return bool Success status
 */
function send_notification_email($type, $data)
{
    $db = get_db_connection();

    // Get all admins with the corresponding notification enabled
    $notification_column = ($type === 'new_post') ? 'notify_new_posts' : 'notify_new_comments';

    $stmt = $db->prepare("SELECT u.username, ans.notification_email 
                         FROM admin_notification_settings ans
                         JOIN community_users u ON ans.user_id = u.id
                         WHERE u.role = 'admin' AND ans.$notification_column = 1");
    $stmt->execute();
    $result = $stmt->get_result();

    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row;
    }

    $stmt->close();

    // If no admins have notifications enabled, exit early
    if (empty($recipients)) {
        return true;
    }

    // Prepare email content
    $subject = '';
    $message = '';

    if ($type === 'new_post') {
        $post_type_text = $data['post_type'] === 'bug' ? 'Bug Report' : 'Feature Request';
        $subject = "[Argo Community] New $post_type_text: " . $data['title'];

        $site_url = get_site_url();
        $post_url = "$site_url/community/view_post.php?id=" . $data['id'];

        $message = "
        <html>
        <head>
            <title>New Community Post</title>
        </head>
        <body>
            <h2>New {$post_type_text} Posted</h2>
            <p>A new {$post_type_text} has been posted on the Argo Community:</p>
            
            <p><strong>Title:</strong> {$data['title']}</p>
            <p><strong>Posted by:</strong> {$data['user_name']} ({$data['user_email']})</p>
            
            <p>
                <a href=\"{$post_url}\">View Post</a>
            </p>
            
            <hr>
            <p>This is an automated notification from the Argo Community system.</p>
            <p><small>You received this message because you're an administrator of the Argo Community. 
            You can adjust your notification settings <a href=\"$site_url/community/users/admin_notification_settings.php\">here</a>.</small></p>
        </body>
        </html>";
    } elseif ($type === 'new_comment') {
        $subject = "[Argo Community] New Comment on: " . $data['post_title'];

        $site_url = get_site_url();
        $post_url = "$site_url/community/view_post.php?id=" . $data['post_id'];

        $message = "
        <html>
        <head>
            <title>New Community Comment</title>
        </head>
        <body>
            <h2>New Comment Posted</h2>
            <p>A new comment has been posted on \"{$data['post_title']}\":</p>
            
            <p><strong>Posted by:</strong> {$data['user_name']} ({$data['user_email']})</p>
            
            <p>
                <a href=\"{$post_url}\">View Comment</a>
            </p>
            
            <hr>
            <p>This is an automated notification from the Argo Community system.</p>
            <p><small>You received this message because you're an administrator of the Argo Community. 
            You can adjust your notification settings <a href=\"$site_url/community/users/admin_notification_settings.php\">here</a>.</small></p>
        </body>
        </html>";
    } else {
        return false; // Unknown notification type
    }

    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: no-reply@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Send emails to all recipients
    $success = true;
    foreach ($recipients as $recipient) {
        $personal_message = str_replace(
            'You\'re an administrator',
            'You\'re an administrator (' . $recipient['username'] . ')',
            $message
        );

        $mail_success = mail($recipient['notification_email'], $subject, $personal_message, implode("\r\n", $headers));
        if (!$mail_success) {
            $success = false;
        }
    }

    return $success;
}

/**
 * Get the site URL
 * 
 * @return string Site URL
 */
function get_site_url()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_dir = dirname(dirname($_SERVER['SCRIPT_NAME']));

    // Remove trailing slash if needed
    if ($script_dir !== '/' && substr($script_dir, -1) === '/') {
        $script_dir = rtrim($script_dir, '/');
    }

    return $protocol . $host . $script_dir;
}
