<?php

declare(strict_types=1);

/**
 * Phase 57 — Admin Core Experience static contract test.
 * Read-only. Run from the repository root.
 */

$root = __DIR__;
$checks = 0;
$passed = 0;
$failed = 0;

function phase57Check(bool $condition, string $label): void
{
    global $checks, $passed, $failed;
    $checks++;
    if ($condition) {
        $passed++;
        echo '[PASS] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . PHP_EOL;
        return;
    }
    $failed++;
    echo '[FAIL] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . PHP_EOL;
}

function phase57Read(string $relative): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? (string)file_get_contents($path) : '';
}

$overview = phase57Read('app/Services/AdminDashboardOverviewService.php');
$health = phase57Read('app/Services/AdminSystemHealthService.php');
$auditRepository = phase57Read('app/Repositories/AuditLogRepository.php');
$auditService = phase57Read('app/Services/AuditLogService.php');
$capabilityRepository = phase57Read('app/Repositories/AdminCapabilityRepository.php');
$capabilityService = phase57Read('app/Services/AdminCapabilityService.php');
$capabilityController = phase57Read('app/Controllers/AdminCapabilityController.php');
$controller = phase57Read('app/Controllers/AdminController.php');
$directEntry = phase57Read('admin/index.php');
$view = phase57Read('pages/admin/dashboard.php');
$shell = phase57Read('admin/includes/auth.php');
$profile = phase57Read('admin/profile/index.php');
$settings = phase57Read('admin/settings/index.php');
$preferences = phase57Read('admin/assets/js/modules/admin-preferences.js');
$mainJs = phase57Read('admin/assets/js/main.js');
$tokens = phase57Read('admin/assets/css/setting.css');
$desktop = phase57Read('admin/assets/css/desktop/dashboard-desktop.css');
$tablet = phase57Read('admin/assets/css/tablet/dashboard-tablet.css');
$mobile = phase57Read('admin/assets/css/mobile/dashboard-mobile.css');
$plan = phase57Read('phase-57-admin-core-experience-plan.md');

phase57Check(
    str_contains($overview, 'AdminDashboardMetricsService') === false
        && str_contains($overview, 'admin.audit-logs.index')
        && str_contains($overview, 'admin.inventory.show')
        && str_contains($controller, 'AdminDashboardOverviewService')
        && str_contains($directEntry, 'AdminDashboardOverviewService'),
    '57.01–57.02 Dashboard overview uses explicit permission and overview contracts'
);
phase57Check(
    str_contains($auditRepository, 'recentAdminActivity')
        && str_contains($auditRepository, 'FROM audit_logs l')
        && str_contains($auditRepository, 'LEFT JOIN admin_users')
        && str_contains($auditRepository, 'WHERE l.admin_user_id IS NOT NULL')
        && str_contains($auditService, 'recentAdminActivity'),
    '57.03 Activity feed reads actual admin audit events only'
);
phase57Check(
    str_contains($overview, 'inventoryLowStockSummary')
        && str_contains($overview, 'inventoryOutOfStockSummary')
        && str_contains($overview, 'countIncidentsByStatus')
        && str_contains($overview, 'countTicketsByStatuses')
        && str_contains($capabilityRepository, 'countTicketsByStatuses')
        && str_contains($capabilityService, 'countTicketsByStatuses'),
    '57.04 Action queue uses existing inventory, incident and ticket data sources'
);
phase57Check(
    str_contains($health, "query('SELECT 1')")
        && str_contains($health, 'PHP_VERSION')
        && str_contains($health, 'date_default_timezone_get')
        && str_contains($capabilityController, 'AdminSystemHealthService'),
    '57.05 System health probes are centralized and API-compatible'
);
phase57Check(
    str_contains($overview, 'admin.products.store')
        && str_contains($overview, 'admin.orders.index')
        && str_contains($overview, 'admin.access.admins.index')
        && str_contains($view, 'quickActions')
        && str_contains($view, 'admin-dashboard-quick-actions'),
    '57.06 Quick actions are permission-aware and rendered from existing routes'
);
phase57Check(
    str_contains($shell, 'admin-shared-sidebar')
        && str_contains($shell, 'admin/profile/index.php')
        && !str_contains($shell, 'stripos($html, \'admin-dashboard-page\') !== false')
        && str_contains($controller, '/admin/includes/auth.php')
        && str_contains($view, 'admin-dashboard-main')
        && !str_contains($view, 'admin-dashboard-sidebar'),
    '57.07 Dashboard uses the shared authenticated shell without duplicate sidebar'
);
phase57Check(
    str_contains($profile, "['first_name']")
        && str_contains($profile, "['last_name']")
        && str_contains($profile, "['id']")
        && !str_contains($profile, "['email']")
        && !str_contains($profile, "['username']"),
    '57.08 Profile displays only safe session identity fields'
);
phase57Check(
    str_contains($settings, 'حساب مدیر ذخیره می‌شوند')
        && str_contains($settings, 'پوسته روشن')
        && str_contains($preferences, 'saveRemote')
        && str_contains($preferences, 'localStorage'),
    '57.09 Settings explain account persistence and browser-local fallback accurately'
);
phase57Check(
    str_contains($tokens, 'color-scheme: light')
        && !str_contains($tokens, 'data-admin-theme="dark"')
        && !str_contains($mainJs, 'modules/theme.js')
        && str_contains($desktop, 'admin-dashboard-ops-grid')
        && str_contains($tablet, 'admin-dashboard-ops-grid')
        && str_contains($mobile, 'admin-dashboard-ops-grid')
        && str_contains($plan, '57.10'),
    '57.10 Light-only theme, three responsive bundles and ten-step plan are present'
);

echo PHP_EOL . 'Phase 57 checks: ' . $passed . '/' . $checks . ' PASS; ' . $failed . ' FAIL.' . PHP_EOL;
echo $failed === 0
    ? 'STATIC CONTRACT: PASS (runtime/browser verification remains required).' . PHP_EOL
    : 'STATIC CONTRACT: FAIL.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
