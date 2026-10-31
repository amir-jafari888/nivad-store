<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
$adminAuth = new AdminAuthService();
if (!$adminAuth->validateCurrentSession() || !$adminAuth->can('admin.content.manage')) { http_response_code(403); exit('Forbidden'); }
?><?php
$previewSection = null;
$previewId = (int)($_GET['section_id'] ?? 0);
if ($previewId > 0) {
    try { $previewSection = (new AdminContentRepository())->section($previewId, true); } catch (Throwable $exception) { $previewSection = null; }
}
?><!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="<?= e(url('/assets/css/main.css')) ?>">
<style>html,body{margin:0;min-height:100%;background:#fff}.cms-preview-root{min-height:100vh}.cms-preview-placeholder{display:grid;place-items:center;min-height:60vh;padding:32px;color:#64748b;text-align:center;font:16px/2 Tahoma,Arial,sans-serif}</style>
</head><body><div class="cms-preview-root" data-preview-root data-api-url="<?= e(url('/api/index.php')) ?>"><?php if (is_array($previewSection)): ?><?= CmsContentRenderer::renderAdminPreview($previewSection) ?><?php else: ?><div class="cms-preview-placeholder">با انتخاب یا ویرایش سکشن، پیش‌نمایش زنده در این قسمت نمایش داده می‌شود.</div><?php endif; ?></div>
<script type="module" src="<?= e(url('/admin/assets/js/modules/admin-content-preview.js')) ?>"></script></body></html>
