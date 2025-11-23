-- Migration: Add recurring billing fields to AI subscriptions
-- Run this migration to add support for automatic subscription renewals

-- Add columns to ai_subscriptions table for recurring billing
ALTER TABLE ai_subscriptions
ADD COLUMN IF NOT EXISTS payment_token VARCHAR(255) NULL COMMENT 'Stored payment method token for recurring billing',
ADD COLUMN IF NOT EXISTS auto_renew TINYINT(1) DEFAULT 1 COMMENT 'Whether to auto-renew the subscription',
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- Add columns to ai_subscription_payments table for better tracking
ALTER TABLE ai_subscription_payments
ADD COLUMN IF NOT EXISTS payment_type ENUM('initial', 'renewal', 'manual') DEFAULT 'initial' COMMENT 'Type of payment',
ADD COLUMN IF NOT EXISTS error_message TEXT NULL COMMENT 'Error message if payment failed';

-- Create index for faster renewal queries
CREATE INDEX IF NOT EXISTS idx_ai_subscriptions_renewal
ON ai_subscriptions (status, end_date, auto_renew);

-- Create index for payment history
CREATE INDEX IF NOT EXISTS idx_ai_subscription_payments_subscription
ON ai_subscription_payments (subscription_id, created_at);
