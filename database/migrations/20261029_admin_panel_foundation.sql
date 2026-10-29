-- NIVAD Admin Panel Foundation — additive schema only.
-- Review the target database and take a verified backup before applying.
-- This file is NOT auto-executed by application bootstrap.
-- Existing tables are not altered or dropped. No foreign keys are assumed because
-- the exact engine/collation of the current schema has not been verified.
-- Apply only after validating MySQL version, table naming, and backup/restore.

CREATE TABLE IF NOT EXISTS admin_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_key VARCHAR(120) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id VARCHAR(100) NULL,
    payload_json LONGTEXT NULL,
    created_by_admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_notifications_event (event_key, created_at),
    KEY idx_admin_notifications_severity (severity, created_at),
    KEY idx_admin_notifications_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notification_recipients (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    notification_id BIGINT UNSIGNED NOT NULL,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NULL DEFAULT NULL,
    acknowledged_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_notification_recipient (notification_id, admin_user_id),
    KEY idx_admin_recipient_unread (admin_user_id, read_at, created_at),
    KEY idx_admin_recipient_ack (admin_user_id, acknowledged_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_email_outbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    notification_id BIGINT UNSIGNED NULL,
    recipient_admin_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(254) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    next_attempt_at TIMESTAMP NULL DEFAULT NULL,
    last_error VARCHAR(1000) NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_email_outbox_retry (status, next_attempt_at, id),
    KEY idx_admin_email_outbox_notification (notification_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_content_revisions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    snapshot_json LONGTEXT NOT NULL,
    scheduled_at TIMESTAMP NULL DEFAULT NULL,
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_by_admin_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_content_revision (entity_type, entity_id, version_number),
    KEY idx_admin_revision_schedule (status, scheduled_at),
    KEY idx_admin_revision_entity (entity_type, entity_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_saved_views (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    module_key VARCHAR(100) NOT NULL,
    name VARCHAR(160) NOT NULL,
    filters_json LONGTEXT NOT NULL,
    sort_json LONGTEXT NULL,
    is_shared TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_saved_view_owner (admin_user_id, module_key, name),
    KEY idx_admin_saved_view_shared (module_key, is_shared)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_operation_jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    job_type VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'queued',
    payload_json LONGTEXT NOT NULL,
    result_json LONGTEXT NULL,
    progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_by_admin_id BIGINT UNSIGNED NOT NULL,
    started_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    error_summary VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_jobs_queue (status, created_at, id),
    KEY idx_admin_jobs_owner (created_by_admin_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_system_incidents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    incident_key VARCHAR(160) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'warning',
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    source VARCHAR(120) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    context_json LONGTEXT NULL,
    occurrence_count INT UNSIGNED NOT NULL DEFAULT 1,
    first_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_by_admin_id BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_incident_key (incident_key),
    KEY idx_admin_incidents_status (status, severity, last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_approval_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    action_key VARCHAR(120) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    requested_by_admin_id BIGINT UNSIGNED NOT NULL,
    reviewed_by_admin_id BIGINT UNSIGNED NULL,
    request_payload_json LONGTEXT NOT NULL,
    before_snapshot_json LONGTEXT NULL,
    decision_note VARCHAR(1000) NULL,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_admin_approvals_pending (status, requested_at),
    KEY idx_admin_approvals_entity (entity_type, entity_id, requested_at),
    KEY idx_admin_approvals_requester (requested_by_admin_id, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_media_assets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    storage_key VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NOT NULL,
    sha256 CHAR(64) NOT NULL,
    alt_text VARCHAR(500) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    uploaded_by_admin_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_media_storage_key (storage_key),
    KEY idx_admin_media_hash (sha256),
    KEY idx_admin_media_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_support_tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_key VARCHAR(40) NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    priority VARCHAR(20) NOT NULL DEFAULT 'normal',
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    assigned_admin_id BIGINT UNSIGNED NULL,
    created_by_admin_id BIGINT UNSIGNED NULL,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_support_ticket_key (ticket_key),
    KEY idx_admin_support_queue (status, priority, updated_at),
    KEY idx_admin_support_assignee (assigned_admin_id, status, updated_at),
    KEY idx_admin_support_customer (customer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_analytics_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_key VARCHAR(120) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id VARCHAR(100) NULL,
    actor_admin_id BIGINT UNSIGNED NULL,
    value_numeric DECIMAL(20,6) NULL,
    dimensions_json LONGTEXT NULL,
    occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_analytics_event_time (event_key, occurred_at),
    KEY idx_admin_analytics_entity (entity_type, entity_id, occurred_at),
    KEY idx_admin_analytics_actor (actor_admin_id, occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
