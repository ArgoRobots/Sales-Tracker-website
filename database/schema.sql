-- License keys table
CREATE TABLE IF NOT EXISTS license_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    license_key TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    activated INTEGER DEFAULT 0,
    activation_date DATETIME DEFAULT NULL,
    ip_address TEXT DEFAULT NULL,
    transaction_id TEXT,
    order_id TEXT,
    payment_method TEXT,
    payment_intent TEXT
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    email TEXT,
    two_factor_secret TEXT,
    two_factor_enabled INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);

-- Payment transactions table to log all payment details
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_id TEXT NOT NULL,
    order_id TEXT,
    email TEXT NOT NULL,
    amount TEXT NOT NULL,
    currency TEXT NOT NULL DEFAULT 'CAD',
    payment_method TEXT NOT NULL,
    status TEXT NOT NULL,
    license_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (license_key) REFERENCES license_keys(license_key) ON DELETE CASCADE
);

-- Add indexes for faster queries
CREATE INDEX IF NOT EXISTS idx_payment_transactions_transaction_id ON payment_transactions(transaction_id);
CREATE INDEX IF NOT EXISTS idx_payment_transactions_email ON payment_transactions(email);
CREATE INDEX IF NOT EXISTS idx_payment_transactions_license_key ON payment_transactions(license_key);
CREATE INDEX IF NOT EXISTS idx_license_keys_transaction_id ON license_keys(transaction_id);
CREATE INDEX IF NOT EXISTS idx_license_keys_email ON license_keys(email);
CREATE INDEX IF NOT EXISTS idx_license_keys_payment_intent ON license_keys(payment_intent);

-- Community forum tables
-- Create users table
CREATE TABLE IF NOT EXISTS community_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    bio TEXT,
    avatar TEXT,
    role TEXT DEFAULT 'user',
    email_verified BOOLEAN DEFAULT 0,
    verification_token TEXT,
    reset_token TEXT,
    reset_token_expiry TIMESTAMP,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add indexes on username and email for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_username ON community_users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON community_users(email);

-- Create posts table
CREATE TABLE IF NOT EXISTS community_posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    user_name TEXT NOT NULL,
    user_email TEXT NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    post_type TEXT NOT NULL CHECK(post_type IN ('bug', 'feature')),
    status TEXT DEFAULT 'open' CHECK(status IN ('open', 'in_progress', 'completed', 'declined')),
    votes INTEGER DEFAULT 0,
    views INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL
);

-- Add indexes for posts
CREATE INDEX IF NOT EXISTS idx_posts_user_id ON community_posts(user_id);
CREATE INDEX IF NOT EXISTS idx_posts_user_email ON community_posts(user_email);
CREATE INDEX IF NOT EXISTS idx_posts_post_type ON community_posts(post_type);
CREATE INDEX IF NOT EXISTS idx_posts_status ON community_posts(status);
CREATE INDEX IF NOT EXISTS idx_posts_created_at ON community_posts(created_at);

-- Create comments table
CREATE TABLE IF NOT EXISTS community_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER,
    user_name TEXT NOT NULL,
    user_email TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL
);

-- Add indexes for comments
CREATE INDEX IF NOT EXISTS idx_comments_post_id ON community_comments(post_id);
CREATE INDEX IF NOT EXISTS idx_comments_user_id ON community_comments(user_id);
CREATE INDEX IF NOT EXISTS idx_comments_user_email ON community_comments(user_email);
CREATE INDEX IF NOT EXISTS idx_comments_created_at ON community_comments(created_at);

-- Create votes table
CREATE TABLE IF NOT EXISTS community_votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER,
    user_email TEXT NOT NULL,
    vote_type INTEGER NOT NULL CHECK(vote_type IN (-1, 1)), -- -1 for downvote, 1 for upvote
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE SET NULL,
    UNIQUE(post_id, user_email) -- Ensure one vote per user per post
);

-- Add indexes for votes
CREATE INDEX IF NOT EXISTS idx_votes_post_id ON community_votes(post_id);
CREATE INDEX IF NOT EXISTS idx_votes_user_id ON community_votes(user_id);
CREATE INDEX IF NOT EXISTS idx_votes_user_email ON community_votes(user_email);

-- Create view for user profiles with post and comment counts
CREATE VIEW IF NOT EXISTS community_user_profiles AS
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
    u.id;

-- Insert default admin user
INSERT OR IGNORE INTO community_users 
    (username, email, password_hash, role, email_verified) 
VALUES 
    ('admin', 'admin@argorobots.com', '$2y$10$8QLEr4QVu1KmOVkBHZq97.bN9Nt5sUwdvxdxhUl5wMdcpIVih5WH2', 'admin', 1);
-- Note: The password hash above is for 'admin123' - change this in production!