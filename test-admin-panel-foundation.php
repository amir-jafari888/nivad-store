<?php

declare(strict_types=1);

/**
 * Admin Panel shared-foundation static regression.
 *
 * Run from repository root:
 *   php test-admin-panel-foundation.php
 *
 * This is source/schema-contract validation only. It does not execute SQL,
 * connect to the target database, send email, or test browser/runtime routes.
 */

$passed = 0;
$failed = 0;

function adminFoundationCheck(string $label, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$label}\n";
}

function adminFoundationRead(string $path): string
{
    $fullPath = __DIR__ . DIRECTORY_SEPARATOR
        . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

    if (!is_file($fullPath) || !is_readable($fullPath)) {
        return '';
    }

    $content = file_get_contents($fullPath);
    return is_string($content) ? $content : '';
}

require_once __DIR__ . '/app/Core/AdminPermissionCatalog.php';

$migration = adminFoundationRead('database/migrations/20261029_admin_panel_foundation.sql');
$permissionSeed = adminFoundationRead('database/seeds/20261029_admin_permission_catalog.sql');
$auditRepository = adminFoundationRead('app/Repositories/AuditLogRepository.php');
$auditService = adminFoundationRead('app/Services/AdminActivityAuditService.php');
$sessionRepository = adminFoundationRead('app/Repositories/AdminSessionRepository.php');
$sessionService = adminFoundationRead('app/Services/AdminSessionService.php');
$notificationRepository = adminFoundationRead('app/Repositories/AdminNotificationRepository.php');
$notificationService = adminFoundationRead('app/Services/AdminNotificationService.php');
$foundationRepository = adminFoundationRead('app/Repositories/AdminFoundationRepository.php');
$foundationService = adminFoundationRead('app/Services/AdminFoundationService.php');

adminFoundationCheck('Additive foundation migration exists', $migration !== '');
adminFoundationCheck('Migration declares additive-only/no auto-execution contract', str_contains($migration, 'NOT auto-executed') && str_contains($migration, 'Existing tables are not altered or dropped'));
adminFoundationCheck('Migration does not contain destructive DROP statements', preg_match('/\bDROP\s+(TABLE|DATABASE|COLUMN)\b/i', $migration) !== 1);
adminFoundationCheck('Migration creates all 11 foundation tables', substr_count($migration, 'CREATE TABLE IF NOT EXISTS') === 11);
adminFoundationCheck('Permission catalog seed exists', $permissionSeed !== '');
adminFoundationCheck('Permission seed does not grant roles automatically', !str_contains(strtolower($permissionSeed), 'admin_role_permissions'));

$permissionNames = AdminPermissionCatalog::allNames();
adminFoundationCheck('Canonical permission catalog contains at least 40 permissions', count($permissionNames) >= 40);
adminFoundationCheck('Permission catalog covers admin account management', in_array(AdminPermissionCatalog::ADMINS_MANAGE, $permissionNames, true));
adminFoundationCheck('Permission catalog separates session view and revoke', in_array(AdminPermissionCatalog::SESSIONS_VIEW, $permissionNames, true) && in_array(AdminPermissionCatalog::SESSIONS_REVOKE, $permissionNames, true));
adminFoundationCheck('Permission catalog includes sensitive-operation approvals', in_array(AdminPermissionCatalog::APPROVALS_DECIDE, $permissionNames, true));

adminFoundationCheck('Audit repository persists old/new snapshots', str_contains($auditRepository, 'old_values') && str_contains($auditRepository, 'new_values') && str_contains($auditRepository, 'public function create('));
adminFoundationCheck('Audit service records actor and request metadata', str_contains($auditService, 'AdminAuth::id()') && str_contains($auditService, 'Request::ip()') && str_contains($auditService, 'Request::userAgent()'));
adminFoundationCheck('Audit service redacts nested sensitive values', str_contains($auditService, 'private function redact(') && str_contains($auditService, '[REDACTED]'));
adminFoundationCheck('Audit service bounds large values', str_contains($auditService, '[OMITTED: VALUE TOO LARGE]'));

adminFoundationCheck('Session inventory query exists', str_contains($sessionRepository, 'findForMonitoring('));
adminFoundationCheck('Session inventory does not select session token hashes in its query', preg_match('/public function findForMonitoring\([\s\S]*?public function deleteExpired/s', $sessionRepository, $sessionMatch) === 1 && !str_contains($sessionMatch[0], 'session_token_hash'));
adminFoundationCheck('Session inventory returns active/expired/revoked status', str_contains($sessionRepository, "THEN \\'active\\'") && str_contains($sessionRepository, "\\'expired\\'") && str_contains($sessionRepository, "\\'revoked\\'"));
adminFoundationCheck('Session inventory is exposed through service layer', str_contains($sessionService, 'findForMonitoring('));

adminFoundationCheck('Admin notification recipients are isolated per admin', str_contains($notificationRepository, 'admin_notification_recipients') && str_contains($notificationRepository, 'r.admin_user_id = :admin_user_id'));
adminFoundationCheck('Notification reads are scoped to recipient identity', str_contains($notificationRepository, 'findForAdmin(') && str_contains($notificationRepository, 'markReadForAdmin('));
adminFoundationCheck('Email delivery is queued rather than assumed sent', str_contains($notificationRepository, 'admin_email_outbox') && str_contains($notificationRepository, "'pending'"));
adminFoundationCheck('Notification service validates email address and severity', str_contains($notificationService, 'FILTER_VALIDATE_EMAIL') && str_contains($notificationService, 'SEVERITIES'));

adminFoundationCheck('Foundation repository includes content revisions and saved views', str_contains($foundationRepository, 'admin_content_revisions') && str_contains($foundationRepository, 'admin_saved_views'));
adminFoundationCheck('Foundation repository includes bulk-operation jobs', str_contains($foundationRepository, 'admin_operation_jobs'));
adminFoundationCheck('Foundation repository includes incidents and approvals', str_contains($foundationRepository, 'admin_system_incidents') && str_contains($foundationRepository, 'admin_approval_requests'));
adminFoundationCheck('Approval decisions require a pending request and a different reviewer', str_contains($foundationRepository, "status = \'pending\'") && str_contains($foundationRepository, 'requested_by_admin_id <> :requester_guard'));
adminFoundationCheck('Foundation repository includes media, support and analytics', str_contains($foundationRepository, 'admin_media_assets') && str_contains($foundationRepository, 'admin_support_tickets') && str_contains($foundationRepository, 'admin_analytics_events'));
adminFoundationCheck('Foundation service validates payloads and redacts sensitive keys', str_contains($foundationService, 'private function redact(') && str_contains($foundationService, '[REDACTED]'));
adminFoundationCheck('Media storage key rejects traversal patterns', str_contains($foundationService, 'str_contains($storageKey, \'..\')') && str_contains($foundationService, 'str_starts_with($storageKey, \'/\')'));

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: static source/schema-contract validation only; SQL/runtime/DB/email/browser checks not performed.\n";

exit($failed === 0 ? 0 : 1);
