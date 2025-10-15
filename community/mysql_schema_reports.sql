CREATE TABLE community_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_user_id INT,
    reported_user_id INT,
    content_type ENUM('post', 'comment', 'user_bio'),
    content_id INT,
    reason ENUM('spam', 'harassment', 'hate_speech', 'violence', 'intellectual_property', 'other'),
    details TEXT,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    action_taken ENUM('none', 'content_removed', 'user_warned', 'user_banned') DEFAULT 'none',
    FOREIGN KEY (reporter_user_id) REFERENCES community_users(id),
    FOREIGN KEY (reported_user_id) REFERENCES community_users(id),
    FOREIGN KEY (reviewed_by) REFERENCES community_users(id)
);

CREATE TABLE community_user_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    banned_by INT,
    reason TEXT,
    ban_duration ENUM('30_days', '1_year', 'permanent'),
    banned_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES community_users(id),
    FOREIGN KEY (banned_by) REFERENCES community_users(id)
);
