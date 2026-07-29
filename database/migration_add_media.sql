-- ============================================================
-- Migration: add media (images/videos) support to lesson content
-- Run this ONCE against an existing hpv_hsv_compass database that
-- was created before this feature existed. Safe to skip if you're
-- installing fresh from schema.sql, which already includes this table.
-- ============================================================
USE hpv_hsv_compass;

CREATE TABLE IF NOT EXISTS content_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
) ENGINE=InnoDB;
