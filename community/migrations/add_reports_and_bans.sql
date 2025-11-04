-- Content reports table
CREATE TABLE IF NOT EXISTS content_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_user_id INT,
    reporter_email VARCHAR(100) NOT NULL,
    content_type ENUM('post', 'comment') NOT NULL,
    content_id INT NOT NULL,
    violation_type VARCHAR(50) NOT NULL,
    additional_info TEXT,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    resolved_by INT,
    resolved_at DATETIME,
    resolution_action VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_user_id) REFERENCES community_users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES community_users(id) ON DELETE SET NULL,
    INDEX idx_content_type_id (content_type, content_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User bans table
CREATE TABLE IF NOT EXISTS user_bans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    banned_by INT NOT NULL,
    ban_reason TEXT NOT NULL,
    ban_duration VARCHAR(20) NOT NULL,
    banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT 1,
    unbanned_at DATETIME,
    unbanned_by INT,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE,
    FOREIGN KEY (banned_by) REFERENCES community_users(id) ON DELETE SET NULL,
    FOREIGN KEY (unbanned_by) REFERENCES community_users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
