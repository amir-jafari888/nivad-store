-- Admin Phase 7 Support Center — read-only verification
-- Run after applying 20261031_admin_support_phase7.sql.
-- Select the intended database in phpMyAdmin before execution.

SELECT DATABASE() AS current_database;

SELECT TABLE_NAME, TABLE_TYPE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'admin_support_tickets',
    'admin_support_messages',
    'admin_support_status_history',
    'admin_support_sla_policies'
  )
ORDER BY TABLE_NAME;

SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'admin_support_tickets'
  AND COLUMN_NAME IN (
    'ticket_key', 'ticket_number', 'customer_id', 'user_id', 'category',
    'priority', 'status', 'subject', 'description', 'assigned_admin_id',
    'created_by_admin_id', 'last_message_at', 'resolved_at', 'closed_at',
    'created_at', 'updated_at'
  )
ORDER BY ORDINAL_POSITION;

SELECT priority, first_response_minutes, resolution_minutes, is_active, updated_at
FROM admin_support_sla_policies
ORDER BY FIELD(priority, 'urgent', 'high', 'normal', 'low');

SELECT
    (SELECT COUNT(*) FROM admin_support_tickets) AS total_tickets,
    (SELECT COUNT(*) FROM admin_support_messages) AS total_messages,
    (SELECT COUNT(*) FROM admin_support_status_history) AS recorded_status_transitions,
    (SELECT COUNT(*) FROM admin_support_sla_policies WHERE is_active = 1) AS active_sla_policies;

-- Existing tickets are intentionally not given fabricated status-history rows.
-- SLA metrics are not meaningful until real response/resolution targets are configured.
