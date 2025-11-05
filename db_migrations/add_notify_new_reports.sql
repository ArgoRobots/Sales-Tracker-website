-- Migration: Add notify_new_reports to admin_notification_settings
-- Date: 2025-01-XX
-- Description: Adds notify_new_reports column to allow admins to opt-in to report notifications

ALTER TABLE admin_notification_settings
ADD COLUMN IF NOT EXISTS notify_new_reports BOOLEAN DEFAULT 1 AFTER notify_new_comments;

-- This migration is safe to run multiple times
