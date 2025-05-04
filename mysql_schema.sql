-- Create users table
CREATE TABLE IF NOT EXISTS community_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user',
    email_verified BOOLEAN DEFAULT 0,
    verification_code VARCHAR(10),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create posts table
CREATE TABLE IF NOT EXISTS community_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_name VARCHAR(50) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    post_type VARCHAR(10) NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    votes INT DEFAULT 0,
    views INT DEFAULT 0,
    metadata TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL,
    CHECK (post_type IN ('bug', 'feature')),
    CHECK (status IN ('open', 'in_progress', 'completed', 'declined'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create comments table
CREATE TABLE IF NOT EXISTS community_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT,
    user_name VARCHAR(50) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    votes INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create votes table
CREATE TABLE IF NOT EXISTS community_votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT,
    user_email VARCHAR(100) NOT NULL,
    vote_type TINYINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL,
    UNIQUE KEY (post_id, user_email),
    CHECK (vote_type IN (-1, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create comment votes table
CREATE TABLE IF NOT EXISTS comment_votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id INT,
    user_email VARCHAR(100) NOT NULL,
    vote_type TINYINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES community_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL,
    UNIQUE KEY (comment_id, user_email),
    CHECK (vote_type IN (-1, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create post edit history
CREATE TABLE IF NOT EXISTS post_edit_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT,
    title VARCHAR(255),
    content TEXT,
    metadata TEXT,
    edited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rate limits table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action_type VARCHAR(20) NOT NULL,
    count INT DEFAULT 1,
    period_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_action_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create remember tokens table
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin notification settings
CREATE TABLE IF NOT EXISTS admin_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notify_new_posts BOOLEAN DEFAULT 1,
    notify_new_comments BOOLEAN DEFAULT 1,
    notification_email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_users_username ON community_users(username);
CREATE INDEX idx_users_email ON community_users(email);
CREATE INDEX idx_posts_user_id ON community_posts(user_id);
CREATE INDEX idx_posts_user_email ON community_posts(user_email);
CREATE INDEX idx_posts_post_type ON community_posts(post_type);
CREATE INDEX idx_posts_status ON community_posts(status);
CREATE INDEX idx_posts_created_at ON community_posts(created_at);
CREATE INDEX idx_comments_post_id ON community_comments(post_id);
CREATE INDEX idx_comments_user_id ON community_comments(user_id);
CREATE INDEX idx_comments_user_email ON community_comments(user_email);
CREATE INDEX idx_votes_post_id ON community_votes(post_id);
CREATE INDEX idx_votes_user_id ON community_votes(user_id);
CREATE INDEX idx_votes_user_email ON community_votes(user_email);
CREATE INDEX idx_comment_votes_comment_id ON comment_votes(comment_id);
CREATE INDEX idx_comment_votes_user_id ON comment_votes(user_id);
CREATE INDEX idx_comment_votes_user_email ON comment_votes(user_email);
CREATE INDEX idx_rate_limits_user_action ON rate_limits(user_id, action_type);
CREATE INDEX idx_remember_tokens_token ON remember_tokens(token);
CREATE INDEX idx_remember_tokens_user_id ON remember_tokens(user_id);
CREATE INDEX idx_notification_settings_user_id ON admin_notification_settings(user_id);