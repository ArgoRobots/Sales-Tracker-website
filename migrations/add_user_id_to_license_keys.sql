-- Migration: Add user_id column to license_keys table
-- This links license keys to user accounts for better lookup

ALTER TABLE license_keys
ADD COLUMN user_id INT DEFAULT NULL AFTER email,
ADD INDEX idx_license_keys_user_id (user_id);

-- Optional: Update existing licenses to link to users by email match
-- UPDATE license_keys lk
-- JOIN community_users cu ON LOWER(lk.email) = LOWER(cu.email)
-- SET lk.user_id = cu.id
-- WHERE lk.user_id IS NULL;
