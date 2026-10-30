-- Phase 4: Incident & Error Detection.
-- Additive only. Apply manually after a verified backup and schema inspection.
-- Canonical incident records remain in admin_system_incidents from the foundation migration.
-- No parallel incident master table is created.
-- Audited persisted queues: admin_operation_jobs and admin_email_outbox only.
CREATE TABLE IF NOT EXISTS admin_incident_metadata (
    incident_id BIGINT UNSIGNED NOT NULL,
    assigned_admin_id BIGINT UNSIGNED NULL DEFAULT NULL,
    last_notification_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (incident_id),
    KEY idx_admin_incident_metadata_assignee (assigned_admin_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_incident_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    incident_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(80) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'warning',
    source VARCHAR(120) NOT NULL,
    request_id VARCHAR(64) NULL DEFAULT NULL,
    http_status SMALLINT UNSIGNED NULL DEFAULT NULL,
    request_method VARCHAR(10) NULL DEFAULT NULL,
    route_path VARCHAR(255) NULL DEFAULT NULL,
    exception_type VARCHAR(160) NULL DEFAULT NULL,
    file_basename VARCHAR(255) NULL DEFAULT NULL,
    line_number INT UNSIGNED NULL DEFAULT NULL,
    context_json LONGTEXT NULL,
    occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_incident_events_timeline (incident_id, occurred_at, id),
    KEY idx_admin_incident_events_source (source, occurred_at),
    KEY idx_admin_incident_events_severity (severity, occurred_at),
    KEY idx_admin_incident_events_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
