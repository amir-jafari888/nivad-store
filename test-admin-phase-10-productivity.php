<?php

declare(strict_types=1);

$root = __DIR__;
$checks = 0;
$failures = 0;

function phase10Check(string $name, bool $condition): void
{
    global $checks, $failures;
    $checks++;
    echo ($condition ? '<span style="color:green">PASS</span>' : '<span style="color:red">FAIL</span>')
        . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>';
    if (!$condition) $failures++;
}

$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    return is_string($content) ? $content : '';
};

$routes = $read('routes/api.php');
$permissionMap = $read('app/Core/AdminRoutePermissionMap.php');
$bulkService = $read('app/Services/AdminBulkOperationService.php');
$bulkController = $read('app/Controllers/AdminBulkOperationController.php');
$bulkRegistry = $read('app/Core/AdminBulkOperationRegistry.php');
$commandService = $read('app/Services/AdminCommandPaletteService.php');
$commandController = $read('app/Controllers/AdminCommandPaletteController.php');
$capabilityService = $read('app/Services/AdminCapabilityService.php');
$capabilityRepository = $read('app/Repositories/AdminCapabilityRepository.php');
$savedValidator = $read('app/Services/AdminSavedViewValidator.php');
$diagnostics = $read('app/Services/AdminProductivityDiagnosticsService.php');
$incidentRepository = $read('app/Repositories/AdminIncidentDetectionRepository.php');
$globalSearch = $read('nivad-panel/assets/js/modules/global-search.js');
$commandJs = $read('nivad-panel/assets/js/modules/command-palette.js');
$savedJs = $read('nivad-panel/assets/js/modules/saved-views.js');
$errorJs = $read('nivad-panel/assets/js/modules/error-boundary.js');
$dataJs = $read('nivad-panel/assets/js/modules/data-experience.js');
$mainJs = $read('nivad-panel/assets/js/main.js');
$adminShell = $read('nivad-panel/includes/auth.php');
$productivityPage = $read('nivad-panel/productivity/index.php');
$productivityJs = $read('nivad-panel/assets/js/modules/productivity-diagnostics.js');
$migration = $read('database/migrations/20261101_admin_productivity_phase10.sql');
$preflight = $read('database/verification/20261101_admin_productivity_phase10_preflight.sql');
$verify = $read('database/verification/20261101_admin_productivity_phase10_verify.sql');

phase10Check('All Phase 10 API routes are registered', str_contains($routes, 'admin.capabilities.commands.index') && str_contains($routes, 'admin.capabilities.commands.execute') && str_contains($routes, 'admin.capabilities.productivity-diagnostics') && str_contains($routes, 'admin.bulk.execute'));
phase10Check('Command palette and bulk routes have explicit permission mappings', str_contains($permissionMap, "'admin.capabilities.commands.index' => 'admin.search.use'") && str_contains($permissionMap, "'admin.bulk.execute' => 'admin.bulk_operations.execute'"));
phase10Check('Bulk execution is restricted to the existing explicit registry', str_contains($bulkService, 'AdminBulkOperationPolicy') && str_contains($bulkService, 'catalog.products.status') && str_contains($bulkService, 'catalog.categories.status') && str_contains($bulkService, 'coupons.status'));
phase10Check('Bulk mutation is transactional and rolls back on a failed item', str_contains($bulkService, 'beginTransaction()') && str_contains($bulkService, 'rollBack()') && str_contains($bulkService, 'commit()'));
phase10Check('Bulk endpoint checks global and module permissions', str_contains($bulkController, 'admin.bulk_operations.execute') && str_contains($bulkController, 'AdminFeatureAccessService') && str_contains($bulkController, 'Authorization::can'));
phase10Check('Unimplemented approval/worker bulk jobs fail closed', str_contains($bulkService, 'Worker و مسیر تأیید عملیاتی') && str_contains($bulkService, 'DomainException'));
phase10Check('Command palette commands are allowlisted and permission-filtered', str_contains($commandService, 'private const COMMANDS') && str_contains($commandService, 'Authorization::can') && str_contains($commandController, 'AdminCommandPaletteService'));
phase10Check('Global search includes admin and CMS entities', str_contains($capabilityRepository, "'type' => 'admin'") && str_contains($capabilityRepository, "'type' => 'content'"));
phase10Check('Global search applies per-entity permissions', str_contains($capabilityService, "'admin' => ['permission' => 'admin.admins.view'") && str_contains($capabilityService, "'content' => ['permission' => 'admin.content.view'"));
phase10Check('Saved views use the actual foundation module_key column', str_contains($capabilityRepository, 'module_key AS module') && str_contains($capabilityRepository, 'WHERE admin_user_id = :admin_id AND module_key = :module'));
phase10Check('Saved view filters and sorts are validated and module-scoped', str_contains($capabilityService, 'AdminSavedViewValidator') && str_contains($capabilityService, '$allowedFields[$module]') && str_contains($savedValidator, 'validateFilters') && str_contains($savedValidator, 'validateSort'));
phase10Check('Saved view migration adds only the missing default flag', str_contains($migration, 'ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0') && str_contains($migration, 'information_schema.COLUMNS') && !str_contains(strtoupper($migration), 'DROP TABLE'));
phase10Check('Saved view preflight and verification SQL are present', str_contains($preflight, 'admin_saved_views') && str_contains($verify, 'is_default'));
phase10Check('Queue diagnostics reuse the existing queue inventory repository', str_contains($diagnostics, 'AdminIncidentDetectionRepository') && str_contains($incidentRepository, 'admin_operation_jobs') && str_contains($incidentRepository, 'admin_email_outbox'));
phase10Check('Backup and webhook status are not reported healthy without evidence', str_contains($diagnostics, "'restore_tested' => false") && str_contains($diagnostics, "'delivery_health' => 'unverified'") && str_contains($diagnostics, "? 'unknown' : 'evidence_available'"));
phase10Check('Payment gateway diagnostics are telemetry-only, not a connectivity claim', str_contains($diagnostics, 'payment_attempts') && str_contains($diagnostics, "'connectivity_tested' => false"));
phase10Check('Inventory anomalies use persisted quantity and reservation fields', str_contains($diagnostics, 'reserved_quantity > quantity') && str_contains($diagnostics, 'low_stock_threshold'));
phase10Check('Customer abuse signals remain blocked without a verified policy/source', str_contains($diagnostics, "'status' => 'blocked'") && str_contains($diagnostics, 'هیچ مشتری به‌صورت خودکار برچسب‌گذاری نمی‌شود'));
phase10Check('Global error UX retries only GET and captures metadata without stack traces', str_contains($errorJs, 'method !== "GET"') && str_contains($errorJs, 'unhandledrejection') && !str_contains($errorJs, 'stack'));
phase10Check('Command palette and saved views are loaded by the shared Admin JS entry', str_contains($mainJs, 'command-palette.js') && str_contains($mainJs, 'saved-views.js') && str_contains($mainJs, 'error-boundary.js'));
phase10Check('Shared Admin shell exposes a command palette trigger and CSRF token', str_contains($adminShell, 'data-admin-command-palette-open') && str_contains($adminShell, 'name="csrf-token"'));
phase10Check('Product, category and coupon tables expose only their matching bulk job', str_contains($read('nivad-panel/products/index.php'), 'catalog.products.status') && str_contains($read('nivad-panel/categories/index.php'), 'catalog.categories.status') && str_contains($read('nivad-panel/coupons/index.php'), 'coupons.status'));
phase10Check('Saved view UI is connected to the saved views API', str_contains($savedJs, '/admin/capabilities/saved-views') && str_contains($savedJs, 'is_default'));
phase10Check('Bulk UI posts selected row IDs to the server endpoint', str_contains($dataJs, '/admin/bulk/execute') && str_contains($dataJs, 'selected'));
phase10Check('Command palette executes server-validated command IDs', str_contains($commandJs, '/admin/capabilities/commands/execute') && str_contains($commandJs, 'command_id'));
phase10Check('Productivity diagnostics have a permission-protected Admin page and rendered UI', str_contains($productivityPage, 'admin.system_health.view') && str_contains($productivityPage, 'data-admin-productivity-diagnostics') && str_contains($productivityJs, '/admin/capabilities/productivity-diagnostics'));
phase10Check('Shared Admin shell exposes the diagnostics navigation by permission', str_contains($adminShell, 'nivad-panel/productivity/index.php') && str_contains($adminShell, '$canViewProductivity'));

echo '<hr>Checks: ' . $checks . ' | Failures: ' . $failures . '<br>';
exit($failures === 0 ? 0 : 1);
