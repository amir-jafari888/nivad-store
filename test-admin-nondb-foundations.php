<?php

declare(strict_types=1);

/**
 * Pure/static tests for Admin Panel infrastructure that does not need a DB.
 * Run: php test-admin-nondb-foundations.php
 * This test never connects to MySQL or Gmail and never changes app data.
 */

$passed = 0;
$failed = 0;

function adminNonDbCheck(string $name, bool $condition): void
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

require_once __DIR__ . '/app/Core/Request.php';
require_once __DIR__ . '/app/Core/Response.php';
require_once __DIR__ . '/app/Core/AdminPermissionCatalog.php';
require_once __DIR__ . '/app/Core/AdminFeatureRegistry.php';
require_once __DIR__ . '/app/Core/AdminRequestContext.php';
require_once __DIR__ . '/app/Services/AdminFeatureContractValidator.php';
require_once __DIR__ . '/app/Services/AdminOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminHealthCheckService.php';
require_once __DIR__ . '/app/Services/GmailSmtpTransport.php';

$registry = AdminFeatureRegistry::all();
$contract = (new AdminFeatureContractValidator())->validate();

adminNonDbCheck('Exactly 44 canonical Admin Panel feature contracts exist', count($registry) === 44);
adminNonDbCheck('Every feature ID follows the ADM-NNN contract', count(array_filter(array_keys($registry), static fn($id) => preg_match('/^ADM-\d{3}$/', $id) === 1)) === 44);
adminNonDbCheck('Every feature has a permission and dependency metadata', count(array_filter($registry, static fn($item) => isset($item['permission'], $item['dependencies'], $item['database_required']))) === 44);
adminNonDbCheck('All feature permissions exist in the canonical permission catalog', $contract['permission_count'] >= 46 && count(array_filter($contract['errors'], static fn($error) => $error['code'] === 'permission_missing_from_catalog')) === 0);
adminNonDbCheck('All feature dependencies exist and the dependency graph is acyclic', $contract['valid'] === true && $contract['dependency_edges_checked'] > 0);
adminNonDbCheck('Feature contract validator does not access the database', $contract['database_accessed'] === false);

$policy = new AdminOperationPolicy();
$deniedSameReviewer = $policy->validateExecutionContext(
    'payment.refund',
    7,
    true,
    7,
    7,
    time() + 300
);
$allowedSeparateReviewer = $policy->validateExecutionContext(
    'payment.refund',
    7,
    true,
    7,
    8,
    time() + 300
);
$deniedWithoutConfirmation = $policy->validateExecutionContext(
    'admin.disable',
    7,
    false
);

adminNonDbCheck('Sensitive operations require explicit confirmation', $policy->requiresExplicitConfirmation('admin.disable') && $deniedWithoutConfirmation['allowed'] === false);
adminNonDbCheck('Approval-required operations reject same-person review', $deniedSameReviewer['allowed'] === false && in_array('separate_reviewer_required', $deniedSameReviewer['errors'], true));
adminNonDbCheck('Valid separate reviewer and unexpired approval pass policy checks', $allowedSeparateReviewer['allowed'] === true);

$_SERVER['HTTP_X_REQUEST_ID'] = 'request-1234-safe';
AdminRequestContext::resetForTests();
adminNonDbCheck('Safe inbound request IDs are preserved', AdminRequestContext::id() === 'request-1234-safe');

$_SERVER['HTTP_X_REQUEST_ID'] = "unsafe\r\nX-Injected: yes";
AdminRequestContext::resetForTests();
$generatedId = AdminRequestContext::id();
adminNonDbCheck('Unsafe inbound request IDs are replaced with generated IDs', preg_match('/^[a-f0-9]{32}$/', $generatedId) === 1);
unset($_SERVER['HTTP_X_REQUEST_ID']);
AdminRequestContext::resetForTests();

$health = (new AdminHealthCheckService())->run();
adminNonDbCheck('Health inspection explicitly avoids database and network access', $health['database_accessed'] === false && $health['network_accessed'] === false);
adminNonDbCheck('Database readiness is reported as blocked, not falsely healthy', count(array_filter($health['checks'], static fn($item) => $item['key'] === 'database.schema' && $item['status'] === 'blocked')) === 1);
adminNonDbCheck('Health inspection does not return secret values', !str_contains(json_encode($health, JSON_THROW_ON_ERROR), 'NIVAD_GMAIL_SMTP_APP_PASSWORD'));

foreach (['NIVAD_GMAIL_SMTP_USER', 'NIVAD_GMAIL_SMTP_APP_PASSWORD', 'NIVAD_GMAIL_SMTP_HOST', 'NIVAD_GMAIL_SMTP_PORT'] as $key) {
    putenv($key);
    unset($_ENV[$key], $_SERVER[$key]);
}
$mailResult = (new GmailSmtpTransport())->send('admin@example.com', 'test', 'test body');
adminNonDbCheck('Gmail transport fails closed without credentials and does not attempt delivery', $mailResult['success'] === false && $mailResult['error_code'] === 'smtp_not_configured');

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: non-database contract/unit checks; no SQL, network, email, or application data changes.\n";

exit($failed === 0 ? 0 : 1);
