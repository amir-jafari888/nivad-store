-- Admin Phase 7 — Support Center
-- Additive only. Apply manually after backup and after the Admin foundation migration.
-- SLA duration is measured in elapsed calendar minutes; business-hours calendars are not defined by the canonical roadmap.
-- No historical status transitions are fabricated. Only transitions observed after this migration are recorded.

SET @nivad_db = DATABASE();

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND COLUMN_NAME='ticket_number')=0,
  'ALTER TABLE admin_support_tickets ADD COLUMN ticket_number VARCHAR(40) NULL',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND COLUMN_NAME='user_id')=0,
  'ALTER TABLE admin_support_tickets ADD COLUMN user_id BIGINT UNSIGNED NULL',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND COLUMN_NAME='category')=0,
  'ALTER TABLE admin_support_tickets ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT ''general''',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND COLUMN_NAME='last_message_at')=0,
  'ALTER TABLE admin_support_tickets ADD COLUMN last_message_at TIMESTAMP NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND COLUMN_NAME='resolved_at')=0,
  'ALTER TABLE admin_support_tickets ADD COLUMN resolved_at TIMESTAMP NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

UPDATE admin_support_tickets
SET ticket_number = COALESCE(NULLIF(ticket_number, ''), NULLIF(ticket_key, ''), CONCAT('NV-', LPAD(id, 10, '0'))),
    user_id = COALESCE(user_id, customer_id),
    last_message_at = COALESCE(last_message_at, updated_at)
WHERE ticket_number IS NULL OR ticket_number = '' OR user_id IS NULL OR last_message_at IS NULL;

SET @nivad_sql = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@nivad_db AND TABLE_NAME='admin_support_tickets' AND INDEX_NAME='uq_admin_support_ticket_number')=0,
  'ALTER TABLE admin_support_tickets ADD UNIQUE KEY uq_admin_support_ticket_number (ticket_number)',
  'SELECT 1'
);
PREPARE nivad_stmt FROM @nivad_sql; EXECUTE nivad_stmt; DEALLOCATE PREPARE nivad_stmt;

CREATE TABLE IF NOT EXISTS admin_support_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_id BIGINT UNSIGNED NOT NULL,
    sender_type VARCHAR(20) NOT NULL,
    sender_user_id BIGINT UNSIGNED NULL,
    sender_admin_id BIGINT UNSIGNED NULL,
    message TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_support_messages_ticket (ticket_id, created_at, id),
    KEY idx_admin_support_messages_sender (sender_type, is_internal, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_support_status_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(20) NULL,
    to_status VARCHAR(20) NOT NULL,
    actor_type VARCHAR(20) NOT NULL,
    actor_admin_id BIGINT UNSIGNED NULL,
    actor_user_id BIGINT UNSIGNED NULL,
    note VARCHAR(1000) NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_support_status_ticket (ticket_id, changed_at, id),
    KEY idx_admin_support_status_actor (actor_admin_id, changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_support_sla_policies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    priority VARCHAR(20) NOT NULL,
    first_response_minutes INT UNSIGNED NULL,
    resolution_minutes INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    updated_by_admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_support_sla_priority (priority),
    KEY idx_admin_support_sla_active (is_active, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO admin_support_sla_policies (priority, first_response_minutes, resolution_minutes, is_active)
VALUES ('urgent', NULL, NULL, 0), ('high', NULL, NULL, 0),
       ('normal', NULL, NULL, 0), ('low', NULL, NULL, 0);
