-- NIVAD Phase 5 CMS — post-migration verification only.
-- READ-ONLY. Run AFTER 20261029_admin_panel_foundation.sql,
-- Phase 3/4 migrations (when required by the current DB), and
-- 20261030_admin_cms_phase5.sql.
-- This file performs no INSERT/UPDATE/DELETE/DDL operations.

-- 01. Required CMS tables
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('admin_cms_pages', 'admin_cms_sections', 'admin_content_revisions', 'admin_media_assets')
ORDER BY TABLE_NAME;

-- 02. Required CMS page count and keys
SELECT page_key, title, route_path, status, sort_order
FROM admin_cms_pages
WHERE deleted_at IS NULL
ORDER BY sort_order, id;

-- 03. Landing section contract and ordering
SELECT s.section_key, s.section_type, s.title, s.sort_order, s.status,
       s.is_visible, s.published_at, s.scheduled_at
FROM admin_cms_sections s
INNER JOIN admin_cms_pages p ON p.id = s.page_id
WHERE p.page_key = 'home'
  AND s.deleted_at IS NULL
ORDER BY COALESCE(s.published_sort_order, s.sort_order), s.id;

-- 04. Every registered public content page must have at least one section.
SELECT p.page_key, COUNT(s.id) AS section_count
FROM admin_cms_pages p
LEFT JOIN admin_cms_sections s
  ON s.page_id = p.id
 AND s.deleted_at IS NULL
WHERE p.deleted_at IS NULL
GROUP BY p.id, p.page_key
ORDER BY p.sort_order, p.id;

-- 05. Find sections whose JSON payload is invalid.
SELECT id, page_id, section_key, 'content_json' AS payload
FROM admin_cms_sections
WHERE JSON_VALID(content_json) = 0
UNION ALL
SELECT id, page_id, section_key, 'style_json' AS payload
FROM admin_cms_sections
WHERE JSON_VALID(style_json) = 0;

-- 06. Published sections must have both published snapshots.
SELECT id, page_id, section_key, status,
       published_content_json IS NOT NULL AS has_published_content,
       published_style_json IS NOT NULL AS has_published_style
FROM admin_cms_sections
WHERE status = 'published'
  AND deleted_at IS NULL
  AND (published_content_json IS NULL OR published_style_json IS NULL);

-- 07. Draft/published isolation: draft rows may differ from published snapshots.
-- This is informational and should return zero rows only when no draft differs.
SELECT id, page_id, section_key, status
FROM admin_cms_sections
WHERE deleted_at IS NULL
  AND status = 'draft'
  AND published_content_json IS NOT NULL
  AND (content_json <> published_content_json OR style_json <> COALESCE(published_style_json, ''));

-- 08. Duplicate active section keys per page must not exist.
SELECT page_id, section_key, COUNT(*) AS duplicate_count
FROM admin_cms_sections
WHERE deleted_at IS NULL
GROUP BY page_id, section_key
HAVING COUNT(*) > 1;

-- 09. Orphaned sections must not exist.
SELECT s.id, s.page_id, s.section_key
FROM admin_cms_sections s
LEFT JOIN admin_cms_pages p ON p.id = s.page_id
WHERE p.id IS NULL;

-- 10. Revision foundation must exist and contain section snapshots if revisions were created.
SELECT COUNT(*) AS revision_count
FROM admin_content_revisions;

-- 11. Media foundation must exist.
SELECT COUNT(*) AS media_asset_count
FROM admin_media_assets;

-- 12. Scheduled rows that are due in UTC. Informational only.
SELECT id, page_id, section_key, scheduled_at, status
FROM admin_cms_sections
WHERE deleted_at IS NULL
  AND scheduled_at IS NOT NULL
  AND scheduled_at <= UTC_TIMESTAMP()
  AND status IN ('draft', 'scheduled');
