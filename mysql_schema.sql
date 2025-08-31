-- License keys table
CREATE TABLE IF NOT EXISTS license_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    activated TINYINT(1) DEFAULT 0,
    activation_date DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    transaction_id VARCHAR(100),
    order_id VARCHAR(100),
    payment_method VARCHAR(50),
    payment_intent VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    two_factor_secret VARCHAR(100),
    two_factor_enabled TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment transactions table to log all payment details
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(100) NOT NULL,
    order_id VARCHAR(100),
    email VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'CAD',
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    license_key VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (license_key) REFERENCES license_keys(license_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletion_scheduled_at DATETIME DEFAULT NULL
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

-- Create a view for user profiles
CREATE OR REPLACE VIEW community_user_profiles AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.bio,
    u.avatar,
    u.role,
    u.created_at,
    COUNT(DISTINCT p.id) AS post_count,
    COUNT(DISTINCT c.id) AS comment_count
FROM
    community_users u
LEFT JOIN
    community_posts p ON u.id = p.user_id
LEFT JOIN
    community_comments c ON u.id = c.user_id
GROUP BY
    u.id, u.username, u.email, u.bio, u.avatar, u.role, u.created_at;

-- Create statistics table for more detailed tracking
CREATE TABLE IF NOT EXISTS statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(50) NOT NULL, -- 'download', 'page_view', etc.
    event_data VARCHAR(255), -- Additional data like version, page, etc.
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    country_code VARCHAR(2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    INDEX idx_country_code (country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create version_history table to track software versions
CREATE TABLE IF NOT EXISTS version_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version VARCHAR(20) NOT NULL,
    release_date DATETIME NOT NULL,
    changelog TEXT,
    download_count INT DEFAULT 0,
    is_current BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_version_number (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for license_keys table
CREATE INDEX idx_license_keys_transaction_id ON license_keys(transaction_id);
CREATE INDEX idx_license_keys_email ON license_keys(email);
CREATE INDEX idx_license_keys_payment_intent ON license_keys(payment_intent);

-- Add indexes for payment_transactions table
CREATE INDEX idx_payment_transactions_transaction_id ON payment_transactions(transaction_id);
CREATE INDEX idx_payment_transactions_email ON payment_transactions(email);
CREATE INDEX idx_payment_transactions_license_key ON payment_transactions(license_key);

-- Add indexes for community tables
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
CREATE INDEX idx_comments_created_at ON community_comments(created_at);
CREATE INDEX idx_votes_post_id ON community_votes(post_id);
CREATE INDEX idx_votes_user_id ON community_votes(user_id);
CREATE INDEX idx_votes_user_email ON community_votes(user_email);
CREATE INDEX idx_comment_votes_comment_id ON comment_votes(comment_id);
CREATE INDEX idx_comment_votes_user_id ON comment_votes(user_id);
CREATE INDEX idx_comment_votes_user_email ON comment_votes(user_email);
CREATE INDEX idx_post_edit_history_post_id ON post_edit_history(post_id);
CREATE INDEX idx_rate_limits_user_action ON rate_limits(user_id, action_type);
CREATE INDEX idx_remember_tokens_token ON remember_tokens(token);
CREATE INDEX idx_remember_tokens_user_id ON remember_tokens(user_id);
CREATE INDEX idx_notification_settings_user_id ON admin_notification_settings(user_id);