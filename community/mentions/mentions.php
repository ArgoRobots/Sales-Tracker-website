<?php

/**
 * Process @mentions in post and comment content and convert them to links
 * 
 * @param string $content The content to process
 * @param string $link_class The CSS class to apply to the mention link
 * @return string The processed content with mentions converted to links
 */
function process_mentions($content)
{
    // First remove any existing mention spans or links
    $clean_content = preg_replace('/<(?:span|a) class="link"[^>]*>(@\w+)<\/(?:span|a)>/', '$1', $content);

    // Then process mentions properly - create actual clickable links
    $pattern = '/@(\w+)/';
    $processed_content = preg_replace_callback(
        $pattern,
        function ($matches) {
            $username = $matches[1];
            $user_id = get_user_id_by_username($username);

            if ($user_id) {
                // Create actual clickable link to user profile
                return '<a class="link-no-underline" href="users/profile.php?username=' . htmlspecialchars($username) . '" data-user-id="' . $user_id . '">@' . htmlspecialchars($username) . '</a>';
            } else {
                // Return the plain text if user doesn't exist
                return '@' . htmlspecialchars($username);
            }
        },
        $clean_content
    );

    return $processed_content;
}

/**
 * Extract @mentions from content
 * 
 * @param string $content The content to extract mentions from
 * @return array Array of usernames mentioned in the content
 */
function extract_mentions($content)
{
    $pattern = '/@(\w+)/';
    $mentions = [];

    if (preg_match_all($pattern, $content, $matches)) {
        $mentions = $matches[1];
    }

    return $mentions;
}

/**
 * Get a user ID by username
 * 
 * @param string $username The username to look up
 * @return int|null The user ID if found, null otherwise
 */
function get_user_id_by_username($username)
{
    $db = get_db_connection();

    $stmt = $db->prepare('SELECT id FROM community_users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }

    $stmt->close();
    return null;
}

/**
 * Create mention notifications for mentioned users
 * 
 * @param array $mentions Array of usernames mentioned
 * @param int $post_id The post ID where the mention occurred
 * @param int $comment_id The comment ID where the mention occurred (0 if in post)
 * @param int $author_id The ID of the user who created the mention
 * @return void
 */
function create_mention_notifications($mentions, $post_id, $comment_id = 0, $author_id = 0)
{
    if (empty($mentions)) {
        return;
    }

    $db = get_db_connection();

    // Check if notifications table exists, if not, create it
    $result = $db->query("SHOW TABLES LIKE 'user_notifications'");
    if ($result->num_rows == 0) {
        // Create notifications table
        $sql = "CREATE TABLE IF NOT EXISTS user_notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255),
            is_read BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->query($sql);
    }

    // Insert notifications for each mentioned user
    $insert_stmt = $db->prepare("
        INSERT INTO user_notifications (user_id, type, message, link, is_read)
        VALUES (?, 'mention', ?, ?, 0)
    ");

    foreach ($mentions as $username) {
        $user_id = get_user_id_by_username($username);

        // Skip if user doesn't exist or is the author
        if (!$user_id || $user_id == $author_id) {
            continue;
        }

        // Get author username
        $author_name = 'Someone';
        if ($author_id) {
            $author_stmt = $db->prepare('SELECT username FROM community_users WHERE id = ? LIMIT 1');
            $author_stmt->bind_param('i', $author_id);
            $author_stmt->execute();
            $author_result = $author_stmt->get_result();

            if ($author_row = $author_result->fetch_assoc()) {
                $author_name = $author_row['username'];
            }

            $author_stmt->close();
        }

        // Create notification message and link
        if ($comment_id > 0) {
            $message = $author_name . ' mentioned you in a comment';
            $link = 'view_post.php?id=' . $post_id . '#comment-' . $comment_id;
        } else {
            $message = $author_name . ' mentioned you in a post';
            $link = 'view_post.php?id=' . $post_id;
        }

        // Insert notification
        $insert_stmt->bind_param('iss', $user_id, $message, $link);
        $insert_stmt->execute();
    }

    $insert_stmt->close();
}
