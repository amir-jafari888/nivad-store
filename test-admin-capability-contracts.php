<?php

declare(strict_types=1);

/**
 * Pure contract tests for the non-UI Admin Panel capability layer.
 * Run: php test-admin-capability-contracts.php
 * No database, filesystem upload, SMTP, or network operation is performed.
 */

$passed = 0;
$failed = 0;

function adminCapabilityCheck(string $name, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$name}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$name}\n";
}

require_once __DIR__ . '/app/Core/AdminPermissionCatalog.php';
require_once __DIR__ . '/app/Core/AdminFeatureRegistry.php';
require_once __DIR__ . '/app/Core/AdminCapabilityContractRegistry.php';
require_once __DIR__ . '/app/Core/AdminFoundationRepositoryInterface.php';
require_once __DIR__ . '/app/Core/AdminSearchProviderInterface.php';
require_once __DIR__ . '/app/Core/AdminBulkOperationRegistry.php';
require_once __DIR__ . '/app/Services/AdminFeatureContractValidator.php';
require_once __DIR__ . '/app/Services/AdminAccountPolicy.php';
require_once __DIR__ . '/app/Services/AdminSessionService.php';
require_once __DIR__ . '/app/Services/AdminAuditSnapshotService.php';
require_once __DIR__ . '/app/Services/AdminNotificationPolicy.php';
require_once __DIR__ . '/app/Services/AdminSupportTicketPolicy.php';
require_once __DIR__ . '/app/Services/AdminOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminBulkOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminGlobalSearchService.php';
require_once __DIR__ . '/app/Services/AdminReportExportService.php';
require_once __DIR__ . '/app/Services/AdminFoundationService.php';
require_once __DIR__ . '/app/Services/AdminFeatureAccessService.php';
require_once __DIR__ . '/app/Services/AdminCapabilityReadinessService.php';
require_once __DIR__ . '/app/Core/Exceptions/AppException.php';
require_once __DIR__ . '/app/Core/Exceptions/AuthorizationException.php';

$registry = AdminCapabilityContractRegistry::all();
$readiness = new AdminCapabilityReadinessService();
$validation = $readiness->validateContracts();

adminCapabilityCheck('Capability contract registry contains the expected 14 non-UI domains', count($registry) === 14);
adminCapabilityCheck('Capability contract registry is internally valid', $validation['valid'] === true && $validation['errors'] === []);
adminCapabilityCheck('Capability contract registry exposes a non-zero operation set', $validation['operation_count'] >= 60);
adminCapabilityCheck('Capability contract registry references only known ADM feature IDs', count(array_filter($registry, static fn(array $item): bool => AdminFeatureRegistry::find($item['feature_id']) !== null)) === 14);

$known = $readiness->status('search', 'search');
$blocked = $readiness->status('admin_accounts', 'create');
$unknown = $readiness->status('does_not_exist', 'run');

adminCapabilityCheck('Non-database search command is explicitly marked ready', $known['status'] === 'non_database_ready' && $known['database_required'] === false);
adminCapabilityCheck('Database-backed account creation is explicitly blocked', $blocked['status'] === 'database_blocked' && $blocked['database_required'] === true);
adminCapabilityCheck('Unknown capability commands fail closed', $unknown['status'] === 'unknown' && $unknown['database_required'] === null);

$readyOperations = $readiness->nonDatabaseOperations();
$readyKeys = array_map(
    static fn(array $item): string => $item['domain'] . '.' . $item['operation'],
    $readyOperations
);

adminCapabilityCheck('Read-only audit operations are non-database contracts', in_array('audit.list', $readyKeys, true) && in_array('audit.diff', $readyKeys, true));
adminCapabilityCheck('Search commands remain available at the contract layer without DB access', in_array('search.search', $readyKeys, true) && in_array('search.suggest', $readyKeys, true));
adminCapabilityCheck('Media validation/storage is separated from DB metadata registration', in_array('media.validate_upload', $readyKeys, true) && in_array('media.store_file', $readyKeys, true) && !in_array('media.register_metadata', $readyKeys, true));
adminCapabilityCheck('Database-backed mutations require an explicit DB boundary', !in_array('admin_accounts.create', $readyKeys, true) && !in_array('content.publish', $readyKeys, true) && !in_array('approvals.approve', $readyKeys, true));

$publish = AdminCapabilityContractRegistry::getOperation('content', 'publish');
$revoke = AdminCapabilityContractRegistry::getOperation('admin_sessions', 'revoke');
$export = AdminCapabilityContractRegistry::getOperation('reports', 'export_csv');

adminCapabilityCheck('Publishing declares its dedicated permission and audit requirement', $publish['permission'] === 'admin.content.publish' && $publish['audit_required'] === true);
adminCapabilityCheck('Session revocation declares its dedicated permission and audit requirement', $revoke['permission'] === 'admin.sessions.revoke' && $revoke['audit_required'] === true);
adminCapabilityCheck('Report CSV export is a read contract and does not claim DB mutation', $export['kind'] === 'read' && $export['database_required'] === false);

$featureValidation = (new AdminFeatureContractValidator())->validate();
adminCapabilityCheck('Existing feature registry remains valid after adding capability contracts', $featureValidation['valid'] === true && $featureValidation['database_accessed'] === false);

if ($failed > 0) {
    echo "SUMMARY: {$passed} PASS, {$failed} FAIL\n";
    exit(1);
}

echo "SUMMARY: {$passed} PASS, {$failed} FAIL\n";
exit(0);
