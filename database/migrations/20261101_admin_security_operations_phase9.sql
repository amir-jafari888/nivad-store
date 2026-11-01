-- Admin Phase 9 — Anti-Fraud and Security Operations
-- Additive only. Review and back up the target database before applying.
CREATE TABLE IF NOT EXISTS admin_security_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    fingerprint CHAR(64) NOT NULL,
    event_key VARCHAR(120) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'warning',
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    subject_type VARCHAR(80) NOT NULL,
    subject_id VARCHAR(120) NOT NULL,
    summary VARCHAR(255) NOT NULL,
    context_json LONGTEXT NULL,
    occurrence_count INT UNSIGNED NOT NULL DEFAULT 1,
    first_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by_admin_id BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    review_note VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_security_event_fingerprint (fingerprint),
    KEY idx_admin_security_event_queue (status, severity, last_seen_at),
    KEY idx_admin_security_event_subject (subject_type, subject_id, last_seen_at),
    KEY idx_admin_security_event_key (event_key, last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
