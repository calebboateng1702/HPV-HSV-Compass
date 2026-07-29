-- ============================================================
-- Migration: add email verification support
-- Run this ONCE against an existing hpv_hsv_compass database that
-- was created before this feature existed. Safe to skip if you're
-- installing fresh from schema.sql, which already includes these columns.
-- ============================================================
USE hpv_hsv_compass;

ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER signins,
  ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL AFTER email_verified,
  ADD COLUMN verification_token_expires DATETIME DEFAULT NULL AFTER verification_token,
  ADD COLUMN verification_sent_at DATETIME DEFAULT NULL AFTER verification_token_expires;

-- Mark any existing accounts (created before this feature existed) as already
-- verified, so nobody who already had access gets locked out retroactively.
UPDATE users SET email_verified = 1 WHERE email_verified = 0;
