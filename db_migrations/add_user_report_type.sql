-- Migration: Add 'user' content type to content_reports table
-- Date: 2025-01-XX
-- Description: Adds 'user' to the content_type ENUM to support user profile reporting

ALTER TABLE content_reports
MODIFY COLUMN content_type ENUM('post', 'comment', 'user') NOT NULL;

-- This migration is safe to run multiple times
-- It will update the ENUM to include 'user' if it doesn't already exist
