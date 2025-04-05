-- Create the license_keys table
CREATE TABLE IF NOT EXISTS license_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    license_key TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activated BOOLEAN DEFAULT 0,
    activation_date TIMESTAMP,
    ip_address TEXT
);

-- Create the admin_users table (for a simple admin interface)
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin user (username: admin, password: ChangeMe123!)
-- Only insert if the table is empty
INSERT OR IGNORE INTO admin_users (username, password_hash) 
SELECT 'admin', '$2y$10$8jnmKYUsj5rSYqPgpeQQMe5ZXQa0C9lFqmswCdQX7qMgY4V5EeO9a'
WHERE NOT EXISTS (SELECT 1 FROM admin_users);