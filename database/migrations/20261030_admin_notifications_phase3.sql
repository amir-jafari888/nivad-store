-- Phase 3: Admin notification center, preferences and durable policy state.
-- Additive only. Apply manually after a verified backup.
-- Canonical notification storage is admin_notifications + admin_notification_recipients
-- from 20261029_admin_panel_foundation.sql. No legacy columns are introduced.
CREATE TABLE IF NOT EXISTS admin_notification_preferences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    event_key VARCHAR(120) NOT NULL DEFAULT '*',
    in_app_enabled TINYINT(1) NOT NULL DEFAULT 1,
    email_enabled TINYINT(1) NOT NULL DEFAULT 0,
    minimum_severity VARCHAR(20) NOT NULL DEFAULT 'info',
    quiet_hours_start TIME NULL DEFAULT NULL,
    quiet_hours_end TIME NULL DEFAULT NULL,
    timezone VARCHAR(64) NOT NULL DEFAULT 'UTC',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_notification_preference (admin_user_id, event_key),
    KEY idx_admin_notification_preference_event (event_key, in_app_enabled, email_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notification_event_policies (
    event_key VARCHAR(120) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    severity VARCHAR(20) NOT NULL DEFAULT 'info',
    email_enabled TINYINT(1) NOT NULL DEFAULT 0,
    dedupe_seconds INT UNSIGNED NOT NULL DEFAULT 300,
    maximum_per_window INT UNSIGNED NOT NULL DEFAULT 20,
    window_seconds INT UNSIGNED NOT NULL DEFAULT 900,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notification_dedupe_state (
    dedupe_hash CHAR(64) NOT NULL,
    last_created_at TIMESTAMP NULL DEFAULT NULL,
    last_notification_id BIGINT UNSIGNED NULL DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (dedupe_hash),
    KEY idx_admin_notification_dedupe_last_created (last_created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notification_rate_state (
    rate_hash CHAR(64) NOT NULL,
    window_started_at TIMESTAMP NULL DEFAULT NULL,
    window_count INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rate_hash),
    KEY idx_admin_notification_rate_window (window_started_at, window_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO admin_notification_event_policies
(event_key, enabled, severity, email_enabled, dedupe_seconds, maximum_per_window, window_seconds)
VALUES
('commerce.order.created', 1, 'info', 0, 60, 30, 900),
('commerce.order.status_changed', 1, 'info', 0, 60, 30, 900),
('commerce.payment.failed', 1, 'critical', 1, 30, 60, 900),
('commerce.payment.succeeded', 1, 'success', 0, 60, 30, 900),
('inventory.stock.low', 1, 'warning', 1, 300, 10, 900),
('inventory.stock.out', 1, 'critical', 1, 60, 20, 900),
('content.review.pending', 1, 'info', 0, 300, 10, 900),
('content.question.pending', 1, 'info', 0, 300, 10, 900),
('system.incident.created', 1, 'critical', 1, 30, 60, 900),
('admin.manual.created', 1, 'info', 0, 60, 20, 900);
