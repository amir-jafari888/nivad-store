-- Read-only Phase 9 preflight. Run before the migration on nivad_store.
SELECT DATABASE() AS current_database;

SELECT TABLE_NAME, TABLE_TYPE, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'admin_security_events',
      'login_attempts',
      'audit_logs',
      'admin_users',
      'refunds',
      'payments'
  )
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (TABLE_NAME = 'login_attempts' AND COLUMN_NAME IN ('identifier', 'ip_address', 'status', 'created_at'))
      OR (TABLE_NAME = 'audit_logs' AND COLUMN_NAME IN ('id', 'admin_user_id', 'action', 'module', 'entity_type', 'entity_id', 'created_at'))
      OR (TABLE_NAME = 'admin_users' AND COLUMN_NAME IN ('id', 'first_name', 'last_name'))
      OR (TABLE_NAME = 'refunds' AND COLUMN_NAME IN ('id', 'payment_id', 'amount', 'status'))
      OR (TABLE_NAME = 'payments' AND COLUMN_NAME IN ('id', 'order_id', 'amount'))
      OR (TABLE_NAME = 'admin_security_events' AND COLUMN_NAME IN ('id', 'fingerprint', 'event_key', 'status'))
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('login_attempts', 'audit_logs', 'refunds', 'payments')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
