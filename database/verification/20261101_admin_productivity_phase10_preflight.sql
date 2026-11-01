-- Read-only Phase 10 preflight. Run against nivad_store before migration.
SELECT DATABASE() AS current_database;

SELECT TABLE_NAME, TABLE_TYPE, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'admin_saved_views',
      'admin_operation_jobs',
      'admin_email_outbox',
      'payment_attempts',
      'inventory',
      'admin_cms_pages',
      'admin_cms_sections'
  )
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (TABLE_NAME = 'admin_saved_views' AND COLUMN_NAME IN ('id', 'admin_user_id', 'module_key', 'name', 'filters_json', 'sort_json', 'is_default', 'is_shared'))
      OR (TABLE_NAME = 'inventory' AND COLUMN_NAME IN ('id', 'variant_id', 'quantity', 'reserved_quantity', 'low_stock_threshold', 'updated_at'))
      OR (TABLE_NAME = 'payment_attempts' AND COLUMN_NAME IN ('id', 'gateway', 'status', 'created_at'))
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT
    CASE WHEN EXISTS (
        SELECT 1 FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_saved_views'
    ) THEN 'PRESENT' ELSE 'MISSING' END AS admin_saved_views_table_status;
