-- Nivad Admin future capability storage contracts
-- MariaDB 10.4 compatible. Schema-only, additive migration.
-- No existing table or row is modified or deleted.
-- Email/Gmail delivery is intentionally excluded.
-- Apply after 20261030_001_admin_route_permissions.sql.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS admin_notifications (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    recipient_admin_user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    category ENUM('system','security','orders','inventory','customer_support','content','errors') NOT NULL DEFAULT 'system',
    severity ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    source_type VARCHAR(100) DEFAULT NULL,
    source_id BIGINT(20) UNSIGNED DEFAULT NULL,
    dedupe_key CHAR(64) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    acknowledged_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_notifications_dedupe_key (dedupe_key),
    KEY idx_admin_notifications_recipient_read_created (recipient_admin_user_id, is_read, created_at),
    KEY idx_admin_notifications_category_severity_created (category, severity, created_at),
    KEY idx_admin_notifications_source (source_type, source_id),
    CONSTRAINT fk_admin_notifications_recipient
        FOREIGN KEY (recipient_admin_user_id) REFERENCES admin_users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_incidents (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    fingerprint CHAR(64) NOT NULL,
    source VARCHAR(100) NOT NULL,
    severity ENUM('info','warning','critical') NOT NULL DEFAULT 'warning',
    status ENUM('open','acknowledged','resolved','ignored') NOT NULL DEFAULT 'open',
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    occurrence_count INT(10) UNSIGNED NOT NULL DEFAULT 1,
    assigned_admin_id BIGINT(20) UNSIGNED DEFAULT NULL,
    resolved_by_admin_id BIGINT(20) UNSIGNED DEFAULT NULL,
    resolved_at DATETIME DEFAULT NULL,
    last_notification_at DATETIME DEFAULT NULL,
    context_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
        CHECK (context_json IS NULL OR JSON_VALID(context_json)),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_incidents_fingerprint (fingerprint),
    KEY idx_admin_incidents_status_severity_last_seen (status, severity, last_seen_at),
    KEY idx_admin_incidents_assigned_admin (assigned_admin_id, status),
    KEY idx_admin_incidents_source_last_seen (source, last_seen_at),
    CONSTRAINT fk_admin_incidents_assigned_admin
        FOREIGN KEY (assigned_admin_id) REFERENCES admin_users (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_admin_incidents_resolved_by_admin
        FOREIGN KEY (resolved_by_admin_id) REFERENCES admin_users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_support_tickets (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_number VARCHAR(50) NOT NULL,
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'general',
    priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    status ENUM('open','pending_customer','pending_admin','resolved','closed') NOT NULL DEFAULT 'open',
    subject VARCHAR(255) NOT NULL,
    assigned_admin_id BIGINT(20) UNSIGNED DEFAULT NULL,
    last_message_at DATETIME DEFAULT NULL,
    resolved_at DATETIME DEFAULT NULL,
    closed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_support_tickets_number (ticket_number),
    KEY idx_admin_support_tickets_status_priority_updated (status, priority, updated_at),
    KEY idx_admin_support_tickets_user (user_id, created_at),
    KEY idx_admin_support_tickets_assignee (assigned_admin_id, status),
    CONSTRAINT fk_admin_support_tickets_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_admin_support_tickets_assigned_admin
        FOREIGN KEY (assigned_admin_id) REFERENCES admin_users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_support_messages (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_id BIGINT(20) UNSIGNED NOT NULL,
    sender_type ENUM('customer','admin','system') NOT NULL,
    sender_user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    sender_admin_id BIGINT(20) UNSIGNED DEFAULT NULL,
    message TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_support_messages_ticket_created (ticket_id, created_at),
    KEY idx_admin_support_messages_sender_user (sender_user_id),
    KEY idx_admin_support_messages_sender_admin (sender_admin_id),
    CONSTRAINT fk_admin_support_messages_ticket
        FOREIGN KEY (ticket_id) REFERENCES admin_support_tickets (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_admin_support_messages_user
        FOREIGN KEY (sender_user_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_admin_support_messages_admin
        FOREIGN KEY (sender_admin_id) REFERENCES admin_users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_analytics_events (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    event_name VARCHAR(60) NOT NULL,
    product_id BIGINT(20) UNSIGNED DEFAULT NULL,
    variant_id BIGINT(20) UNSIGNED DEFAULT NULL,
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    visitor_hash CHAR(64) DEFAULT NULL,
    session_hash CHAR(64) DEFAULT NULL,
    search_term_hash CHAR(64) DEFAULT NULL,
    source VARCHAR(100) DEFAULT NULL,
    context_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
        CHECK (context_json IS NULL OR JSON_VALID(context_json)),
    occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_product_analytics_product_event_time (product_id, event_name, occurred_at),
    KEY idx_product_analytics_event_time (event_name, occurred_at),
    KEY idx_product_analytics_user_time (user_id, occurred_at),
    KEY idx_product_analytics_visitor_time (visitor_hash, occurred_at),
    KEY idx_product_analytics_session_time (session_hash, occurred_at),
    CONSTRAINT fk_product_analytics_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_product_analytics_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_product_analytics_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_preferences (
    admin_user_id BIGINT(20) UNSIGNED NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
        CHECK (JSON_VALID(preference_value)),
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (admin_user_id, preference_key),
    CONSTRAINT fk_admin_preferences_admin
        FOREIGN KEY (admin_user_id) REFERENCES admin_users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_saved_views (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_user_id BIGINT(20) UNSIGNED NOT NULL,
    module VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    filters_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
        CHECK (JSON_VALID(filters_json)),
    sort_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
        CHECK (sort_json IS NULL OR JSON_VALID(sort_json)),
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_saved_views_owner_module_name (admin_user_id, module, name),
    KEY idx_admin_saved_views_owner_module (admin_user_id, module),
    CONSTRAINT fk_admin_saved_views_admin
        FOREIGN KEY (admin_user_id) REFERENCES admin_users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_content_revisions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT(20) UNSIGNED NOT NULL,
    revision_number INT(10) UNSIGNED NOT NULL,
    actor_admin_id BIGINT(20) UNSIGNED DEFAULT NULL,
    snapshot_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
        CHECK (JSON_VALID(snapshot_json)),
    change_summary VARCHAR(1000) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_content_revisions_entity_revision (entity_type, entity_id, revision_number),
    KEY idx_admin_content_revisions_actor_created (actor_admin_id, created_at),
    KEY idx_admin_content_revisions_entity_created (entity_type, entity_id, created_at),
    CONSTRAINT fk_admin_content_revisions_actor
        FOREIGN KEY (actor_admin_id) REFERENCES admin_users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
