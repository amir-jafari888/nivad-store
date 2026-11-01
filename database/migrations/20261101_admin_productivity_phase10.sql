-- Phase 10: add explicit default-view state to the existing Admin saved views table.
-- This migration is additive and intentionally does not alter or remove saved view data.

SET @phase10_saved_view_default_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admin_saved_views'
      AND COLUMN_NAME = 'is_default'
);

SET @phase10_saved_view_default_sql = IF(
    @phase10_saved_view_default_exists = 0,
    'ALTER TABLE admin_saved_views ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER sort_json',
    'SELECT ''admin_saved_views.is_default already exists'' AS migration_status'
);

PREPARE phase10_saved_view_default_stmt FROM @phase10_saved_view_default_sql;
EXECUTE phase10_saved_view_default_stmt;
DEALLOCATE PREPARE phase10_saved_view_default_stmt;
