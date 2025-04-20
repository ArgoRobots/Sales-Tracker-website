<?php
/**
 * Helper functions for the community forum
 */

/**
 * Get all posts with vote counts, ordered by creation date (newest first)
 * 
 * @return array Array of posts
 */
function get_all_posts() {
    $db = get_db_connection();
    
    $result = $db->query('SELECT * FROM community_posts ORDER BY created_at DESC');
    
    $posts = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
function get_post($post_id) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('SELECT * FROM community_posts WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    return $result->fetchArray(SQLITE3_ASSOC);
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
function add_post($user_name, $user_email, $title, $content, $post_type) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('INSERT INTO community_posts (user_name, user_email, title, content, post_type) 
                         VALUES (:user_name, :user_email, :title, :content, :post_type)');
    $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
    $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':post_type', $post_type, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        $post_id = $db->lastInsertRowID();
        
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
    
    return false;
}

/**
 * Update a post's status
 * 
 * @param int $post_id Post ID
 * @param string $status New status ('open', 'in_progress', 'completed', 'declined')
 * @return bool Success status
 */
function update_post_status($post_id, $status) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('UPDATE community_posts SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    
    return $stmt->execute() !== false;
}

/**
 * Delete a post
 * 
 * @param int $post_id Post ID
 * @return bool Success status
 */
function delete_post($post_id) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('DELETE FROM community_posts WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    
    return $stmt->execute() !== false;
}

/**
 * Get all comments for a post
 * 
 * @param int $post_id Post ID
 * @return array Array of comments
 */
function get_post_comments($post_id) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('SELECT * FROM community_comments WHERE post_id = :post_id ORDER BY created_at ASC');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $comments = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $comments[] = $row;
    }
    
    return $comments;
}

/**
 * Get comment count for a post
 * 
 * @param int $post_id Post ID
 * @return int Comment count
 */
function get_comment_count($post_id) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM community_comments WHERE post_id = :post_id');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    return $result['count'];
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
function add_comment($post_id, $user_name, $user_email, $content) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('INSERT INTO community_comments (post_id, user_name, user_email, content) 
                         VALUES (:post_id, :user_name, :user_email, :content)');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
    $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        $comment_id = $db->lastInsertRowID();
        
        // Get the post data
        $post = get_post($post_id);
        
        // Get the new comment
        $stmt = $db->prepare('SELECT * FROM community_comments WHERE id = :id');
        $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);
        $new_comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
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
    
    return false;
}

/**
 * Delete a comment
 * 
 * @param int $comment_id Comment ID
 * @return array|false Post ID and success status or false on failure
 */
function delete_comment($comment_id) {
    $db = get_db_connection();
    
    // Get the post_id before deleting
    $stmt = $db->prepare('SELECT post_id FROM community_comments WHERE id = :id');
    $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if (!$result) {
        return false;
    }
    
    $post_id = $result['post_id'];
    
    // Delete the comment
    $stmt = $db->prepare('DELETE FROM community_comments WHERE id = :id');
    $stmt->bindValue(':id', $comment_id, SQLITE3_INTEGER);
    
    if ($stmt->execute()) {
        return [
            'post_id' => $post_id,
            'success' => true
        ];
    }
    
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
function vote_post($post_id, $user_email, $vote_type) {
    $db = get_db_connection();
    
    // Check if user has already voted
    $stmt = $db->prepare('SELECT vote_type FROM community_votes WHERE post_id = :post_id AND user_email = :user_email');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
    $existing_vote = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($existing_vote) {
        // User already voted, check if they're changing their vote
        if ($existing_vote['vote_type'] == $vote_type) {
            // Remove the vote (cancel)
            $stmt = $db->prepare('DELETE FROM community_votes WHERE post_id = :post_id AND user_email = :user_email');
            $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
            $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
            $stmt->execute();
            
            $user_vote = 0;
        } else {
            // Update the vote
            $stmt = $db->prepare('UPDATE community_votes SET vote_type = :vote_type, created_at = CURRENT_TIMESTAMP 
                                 WHERE post_id = :post_id AND user_email = :user_email');
            $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
            $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
            $stmt->bindValue(':vote_type', $vote_type, SQLITE3_INTEGER);
            $stmt->execute();
            
            $user_vote = $vote_type;
        }
    } else {
        // Add new vote
        $stmt = $db->prepare('INSERT INTO community_votes (post_id, user_email, vote_type) 
                             VALUES (:post_id, :user_email, :vote_type)');
        $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
        $stmt->bindValue(':vote_type', $vote_type, SQLITE3_INTEGER);
        $stmt->execute();
        
        $user_vote = $vote_type;
    }
    
    // Update vote count in posts table
    $stmt = $db->prepare('SELECT SUM(vote_type) as total_votes FROM community_votes WHERE post_id = :post_id');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    $total_votes = $result['total_votes'] ?? 0;
    
    // Update the post's vote count
    $stmt = $db->prepare('UPDATE community_posts SET votes = :votes WHERE id = :id');
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':votes', $total_votes, SQLITE3_INTEGER);
    $stmt->execute();
    
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
function get_user_vote($post_id, $user_email) {
    $db = get_db_connection();
    
    $stmt = $db->prepare('SELECT vote_type FROM community_votes WHERE post_id = :post_id AND user_email = :user_email');
    $stmt->bindValue(':post_id', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    return $result ? $result['vote_type'] : 0;
}

/**
 * Send notification email
 * 
 * @param string $type Notification type ('new_post', 'new_comment')
 * @param array $data Notification data
 * @return bool Success status
 */
function send_notification_email($type, $data) {
    // Hardcoded notification email
    $admin_email = 'contact@argorobots.com';
    
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
            <p>A new {$post_type_text} has been posted on the Argo Sales Tracker Community:</p>
            
            <p><strong>Title:</strong> {$data['title']}</p>
            <p><strong>Posted by:</strong> {$data['user_name']} ({$data['user_email']})</p>
            
            <p>
                <a href=\"{$post_url}\">View Post</a>
            </p>
            
            <hr>
            <p>This is an automated notification from the Argo Sales Tracker Community system.</p>
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
            <p>This is an automated notification from the Argo Sales Tracker Community system.</p>
        </body>
        </html>";
    } else {
        return false; // Unknown notification type
    }
    
    // Send the email
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Community <noreply@argorobots.com>',
        'Reply-To: no-reply@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($admin_email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Get the site URL
 * 
 * @return string Site URL
 */
function get_site_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_dir = dirname(dirname($_SERVER['SCRIPT_NAME']));
    
    // Remove trailing slash if needed
    if ($script_dir !== '/' && substr($script_dir, -1) === '/') {
        $script_dir = rtrim($script_dir, '/');
    }
    
    return $protocol . $host . $script_dir;
}