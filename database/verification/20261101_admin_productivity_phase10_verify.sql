-- Phase 10 verification. Run after applying the additive migration.
SELECT DATABASE() AS current_database;

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'admin_saved_views'
  AND COLUMN_NAME IN ('module_key', 'sort_json', 'is_default');

SELECT
    COUNT(*) AS total_saved_views,
    SUM(CASE WHEN is_default = 1 THEN 1 ELSE 0 END) AS default_saved_views
FROM admin_saved_views;

SELECT
    CASE
        WHEN EXISTS (
            SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'admin_saved_views'
              AND COLUMN_NAME = 'is_default'
        ) THEN 'PASS'
        ELSE 'FAIL'
    END AS phase10_saved_view_schema_status;
