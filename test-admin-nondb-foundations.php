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
require_once __DIR__ . '/app/Core/SensitiveDataRedactor.php';
require_once __DIR__ . '/app/Core/AdminFeatureRegistry.php';
require_once __DIR__ . '/app/Core/AdminBulkOperationRegistry.php';
require_once __DIR__ . '/app/Core/AdminSearchProviderInterface.php';
require_once __DIR__ . '/app/Services/AdminGlobalSearchService.php';
require_once __DIR__ . '/app/Core/AdminRequestContext.php';
require_once __DIR__ . '/app/Core/AdminActorContext.php';
require_once __DIR__ . '/app/Core/Exceptions/AppException.php';
require_once __DIR__ . '/app/Core/Exceptions/AuthorizationException.php';
require_once __DIR__ . '/app/Services/AdminFeatureContractValidator.php';
require_once __DIR__ . '/app/Services/AdminAuditSnapshotService.php';
require_once __DIR__ . '/app/Services/AdminOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminNotificationPolicy.php';
require_once __DIR__ . '/app/Services/AdminHealthCheckService.php';
require_once __DIR__ . '/app/Services/AdminMediaStorageService.php';
require_once __DIR__ . '/app/Services/AdminPreviewTokenService.php';
require_once __DIR__ . '/app/Services/AdminSavedViewValidator.php';
require_once __DIR__ . '/app/Services/AdminContentPublishingPolicy.php';
require_once __DIR__ . '/app/Services/AdminReportExportService.php';
require_once __DIR__ . '/app/Services/AdminBulkOperationPolicy.php';
require_once __DIR__ . '/app/Services/GmailSmtpTransport.php';

$registry = AdminFeatureRegistry::all();
$contract = (new AdminFeatureContractValidator())->validate();

adminNonDbCheck('Exactly 44 canonical Admin Panel feature contracts exist', count($registry) === 44);
adminNonDbCheck('Every feature ID follows the ADM-NNN contract', count(array_filter(array_keys($registry), static fn($id) => preg_match('/^ADM-\d{3}$/', $id) === 1)) === 44);
adminNonDbCheck('Every feature has a permission and dependency metadata', count(array_filter($registry, static fn($item) => isset($item['permission'], $item['dependencies'], $item['database_required']))) === 44);
adminNonDbCheck('All feature permissions exist in the canonical permission catalog', $contract['permission_count'] >= 46 && count(array_filter($contract['errors'], static fn($error) => $error['code'] === 'permission_missing_from_catalog')) === 0);
adminNonDbCheck('All feature dependencies exist and the dependency graph is acyclic', $contract['valid'] === true && $contract['dependency_edges_checked'] > 0);
adminNonDbCheck('Feature contract validator does not access the database', $contract['database_accessed'] === false);

$redacted = SensitiveDataRedactor::redact([
    'password' => 'never-log-this',
    'profile' => [
        'access_token' => 'never-log-this-either',
        'display_name' => 'Nivad Admin',
    ],
    'normal_value' => 'safe',
]);
$redactedJson = json_encode($redacted, JSON_THROW_ON_ERROR);
adminNonDbCheck('Central redactor removes secrets recursively', $redacted['password'] === '[REDACTED]' && $redacted['profile']['access_token'] === '[REDACTED]');
adminNonDbCheck('Central redactor preserves non-sensitive values', $redacted['profile']['display_name'] === 'Nivad Admin' && $redacted['normal_value'] === 'safe');
adminNonDbCheck('Central redactor output does not contain supplied secret values', !str_contains($redactedJson, 'never-log-this'));

$snapshotService = new AdminAuditSnapshotService();
$snapshot = $snapshotService->compare(
    ['status' => 'draft', 'price' => 100, 'credentials' => ['password' => 'never-store-this']],
    ['status' => 'published', 'price' => 120, 'new_field' => true, 'credentials' => ['password' => 'another-secret']]
);
$snapshotPaths = array_column($snapshot['changes'], 'path');
$snapshotJson = json_encode($snapshot, JSON_THROW_ON_ERROR);
adminNonDbCheck('Audit snapshot service produces before/after diffs', in_array('status', $snapshotPaths, true) && in_array('price', $snapshotPaths, true) && in_array('new_field', $snapshotPaths, true));
adminNonDbCheck('Audit snapshot service redacts secrets before creating diffs', !str_contains($snapshotJson, 'never-store-this') && !str_contains($snapshotJson, 'another-secret') && str_contains($snapshotJson, '[REDACTED]'));

$actorContext = new AdminActorContext(static fn() => 7, static fn() => true);
adminNonDbCheck('Actor context uses the validated current admin identity', $actorContext->requireCurrentAdmin(7) === 7);
$actorMismatchDenied = false;
try {
    $actorContext->requireCurrentAdmin(8);
} catch (AuthorizationException $exception) {
    $actorMismatchDenied = true;
}
adminNonDbCheck('Actor context rejects spoofed admin IDs', $actorMismatchDenied);
$noSessionActor = new AdminActorContext(static fn() => 7, static fn() => false);
adminNonDbCheck('Actor context rejects an unvalidated session', $noSessionActor->currentAdminId() === null);

$notificationPolicy = new AdminNotificationPolicy();
$notificationAllowed = $notificationPolicy->evaluate('order.status.changed', 'warning', 0, null, 1000);
$notificationThrottled = $notificationPolicy->evaluate('order.status.changed', 'info', 3, null, 1000);
$dedupeOne = $notificationPolicy->deduplicationKey('order.status.changed', 'order', 15, [9, 3, 9]);
$dedupeTwo = $notificationPolicy->deduplicationKey('order.status.changed', 'order', 15, [3, 9]);
adminNonDbCheck('Notification policy validates event keys and priorities', $notificationAllowed['allowed'] === true && $notificationAllowed['priority'] === 3);
adminNonDbCheck('Notification policy identifies rate-limited events', $notificationThrottled['allowed'] === false && in_array('rate_limit_exceeded', $notificationThrottled['errors'], true));
adminNonDbCheck('Notification deduplication keys are stable across recipient order and duplicates', hash_equals($dedupeOne, $dedupeTwo));

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

putenv('NIVAD_ADMIN_PREVIEW_SECRET=test-preview-secret-32-bytes-minimum');
$searchProvider = new class implements AdminSearchProviderInterface {
    public int $calls = 0;

    public function featureId(): string
    {
        return 'ADM-011';
    }

    public function search(string $query, int $limit): array
    {
        $this->calls++;
        return [
            ['id' => 1, 'type' => 'product', 'title' => 'Phone A', 'subtitle' => '128 GB', 'url' => '/admin/products/1', 'score' => 0.9],
            ['id' => 2, 'type' => 'product', 'title' => 'Phone B', 'url' => 'https://evil.example/admin/products/2', 'score' => 0.8],
            ['id' => 1, 'type' => 'product', 'title' => 'Duplicate Phone A', 'url' => '/admin/products/1', 'score' => 0.7],
        ];
    }
};
$searchService = new AdminGlobalSearchService(
    [$searchProvider],
    static fn(int $adminId, string $featureId): bool => $adminId === 7 && $featureId === 'ADM-011'
);
$searchResult = $searchService->search(7, 'phone', 10);
adminNonDbCheck('Global search enforces feature access and rejects external result URLs', $searchResult['total'] === 1 && $searchResult['results'][0]['url'] === '/admin/products/1');
adminNonDbCheck('Global search deduplicates results by type and ID', count($searchResult['results']) === 1);
$deniedSearchProvider = new class implements AdminSearchProviderInterface {
    public int $calls = 0;
    public function featureId(): string { return 'ADM-011'; }
    public function search(string $query, int $limit): array { $this->calls++; return []; }
};
$deniedSearch = new AdminGlobalSearchService(
    [$deniedSearchProvider],
    static fn(int $adminId, string $featureId): bool => false
);
$deniedSearchResult = $deniedSearch->search(7, 'phone');
adminNonDbCheck('Global search never queries a provider without feature permission', $deniedSearchResult['total'] === 0 && $deniedSearchProvider->calls === 0);

$previewService = new AdminPreviewTokenService();
$previewToken = $previewService->issue('product', 'product-15', 7, 900, 1000);
$previewClaims = $previewService->verify($previewToken, 7, 'product', 'product-15', 1500);
$wrongAdminPreview = $previewService->verify($previewToken, 8, 'product', 'product-15', 1500);
$expiredPreview = $previewService->verify($previewToken, 7, 'product', 'product-15', 1900);
$tamperedPreview = $previewService->verify($previewToken . 'x', 7, 'product', 'product-15', 1500);
adminNonDbCheck('Preview tokens verify signature, scope, actor and expiry', is_array($previewClaims) && $previewClaims['entity_id'] === 'product-15' && $wrongAdminPreview === null && $expiredPreview === null && $tamperedPreview === null);
putenv('NIVAD_ADMIN_PREVIEW_SECRET');

$csvService = new AdminReportExportService();
$csv = $csvService->toCsv(
    ['title' => 'عنوان', 'amount' => 'مبلغ'],
    [
        ['title' => '=HYPERLINK("https://evil.example","click")', 'amount' => 12],
        ['title' => 'گوشی، مدل A', 'amount' => 0],
    ]
);
$csvFormulaSafe = str_contains($csv, "'=HYPERLINK(");
$csvPersianSafe = str_contains($csv, 'گوشی، مدل A');
$csvRowLimitRejected = false;
try {
    $csvService->toCsv(['title' => 'Title'], array_fill(0, 10001, ['title' => 'row']), false);
} catch (InvalidArgumentException $exception) {
    $csvRowLimitRejected = true;
}
adminNonDbCheck('CSV export neutralizes spreadsheet formulas and preserves Persian text', $csvFormulaSafe && $csvPersianSafe && str_starts_with($csv, "\xEF\xBB\xBF"));
adminNonDbCheck('CSV export enforces a hard row limit', $csvRowLimitRejected);

$bulkPolicy = new AdminBulkOperationPolicy();
$validBulk = $bulkPolicy->validate('catalog.products.status', ['ids' => [1, '2', 2], 'access_token' => 'never-store-this'], true);
$sensitiveBulk = $bulkPolicy->validate('inventory.adjust', ['ids' => [4, 5]], true, 17);
$unknownBulkRejected = false;
try {
    $bulkPolicy->validate('arbitrary.sql', ['ids' => [1]], true);
} catch (InvalidArgumentException $exception) {
    $unknownBulkRejected = true;
}
$unconfirmedBulkRejected = false;
try {
    $bulkPolicy->validate('catalog.products.status', ['ids' => [1]], false);
} catch (InvalidArgumentException $exception) {
    $unconfirmedBulkRejected = true;
}
$missingApprovalRejected = false;
try {
    $bulkPolicy->validate('inventory.adjust', ['ids' => [1]], true);
} catch (InvalidArgumentException $exception) {
    $missingApprovalRejected = true;
}
adminNonDbCheck('Bulk operation policy allowlists job types and deduplicates IDs', $validBulk['payload']['ids'] === [1, 2] && $validBulk['job_type'] === 'catalog.products.status');
adminNonDbCheck('Sensitive bulk operations require an approval reference', $sensitiveBulk['requires_approval'] === true && $sensitiveBulk['approval_verification_required_at_execution'] === true && $missingApprovalRejected);
adminNonDbCheck('Bulk operation policy rejects unknown jobs and missing confirmation', $unknownBulkRejected && $unconfirmedBulkRejected);
adminNonDbCheck('Bulk operation payload redacts sensitive fields', $validBulk['payload']['access_token'] === '[REDACTED]');

$publishingPolicy = new AdminContentPublishingPolicy();
$normalizedSchedule = $publishingPolicy->normalizeSchedule('scheduled', '2099-01-01T00:00:00+03:30');
$invalidScheduleRejected = false;
try {
    $publishingPolicy->normalizeSchedule('scheduled', '2099-01-01 00:00:00');
} catch (InvalidArgumentException $exception) {
    $invalidScheduleRejected = true;
}
$pastScheduleRejected = false;
try {
    $publishingPolicy->normalizeSchedule('scheduled', '2000-01-01T00:00:00Z');
} catch (InvalidArgumentException $exception) {
    $pastScheduleRejected = true;
}
adminNonDbCheck('Publishing policy normalizes explicit-offset schedules to UTC', $normalizedSchedule === '2098-12-31 20:30:00');
adminNonDbCheck('Publishing policy rejects ambiguous or past schedule times', $invalidScheduleRejected && $pastScheduleRejected);
adminNonDbCheck('Publishing state machine blocks invalid transitions', $publishingPolicy->canTransition('draft', 'published') && !$publishingPolicy->canTransition('archived', 'published'));

$savedViewValidator = new AdminSavedViewValidator();
$validFilters = $savedViewValidator->validateFilters([
    ['field' => 'status', 'operator' => 'eq', 'value' => 'active'],
    ['field' => 'created_at', 'operator' => 'gte', 'value' => '2026-01-01'],
]);
$validSort = $savedViewValidator->validateSort([
    ['field' => 'created_at', 'direction' => 'desc'],
]);
$unsafeSavedViewRejected = false;
try {
    $savedViewValidator->validateFilters([
        ['field' => 'id;DROP_TABLE', 'operator' => 'eq', 'value' => 1],
    ]);
} catch (InvalidArgumentException $exception) {
    $unsafeSavedViewRejected = true;
}
$unsafeOperatorRejected = false;
try {
    $savedViewValidator->validateFilters([
        ['field' => 'status', 'operator' => 'or 1=1', 'value' => 'active'],
    ]);
} catch (InvalidArgumentException $exception) {
    $unsafeOperatorRejected = true;
}
adminNonDbCheck('Saved-view validator accepts safe filter/sort contracts', count($validFilters) === 2 && $validSort[0]['direction'] === 'desc');
adminNonDbCheck('Saved-view validator rejects unsafe fields and operators', $unsafeSavedViewRejected && $unsafeOperatorRejected);

$mediaPath = tempnam(sys_get_temp_dir(), 'nivad-admin-media-');
$mediaPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jS8sAAAAASUVORK5CYII=', true);
$mediaInspectPassed = false;
$mediaFilenameSafe = false;
if (is_string($mediaPath) && is_string($mediaPng) && file_put_contents($mediaPath, $mediaPng) !== false) {
    try {
        $mediaInfo = (new AdminMediaStorageService())->inspectFile($mediaPath, '../../private/photo.png');
        $mediaInspectPassed = $mediaInfo['mime_type'] === 'image/png'
            && $mediaInfo['width'] === 1
            && $mediaInfo['height'] === 1
            && preg_match('/^[a-f0-9]{64}$/', $mediaInfo['sha256']) === 1;
        $mediaFilenameSafe = $mediaInfo['original_name'] === 'photo.png';
    } catch (Throwable $exception) {
        $mediaInspectPassed = false;
    }
}
if (is_string($mediaPath) && is_file($mediaPath)) {
    @unlink($mediaPath);
}
adminNonDbCheck('Media inspection verifies actual image MIME, dimensions and SHA-256', $mediaInspectPassed);
adminNonDbCheck('Media filenames are reduced to a safe basename', $mediaFilenameSafe);

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

putenv('NIVAD_GMAIL_SMTP_USER=admin@example.com');
putenv('NIVAD_GMAIL_SMTP_APP_PASSWORD=placeholder-app-password');
putenv('NIVAD_GMAIL_SMTP_HOST=smtp.gmail.com');
putenv('NIVAD_GMAIL_SMTP_PORT=587');
$gmailTransport = new GmailSmtpTransport();
$invalidMessage = $gmailTransport->send('admin@example.com', '', 'body');
$invalidRecipient = $gmailTransport->send('not-an-email', 'Subject', 'body');
adminNonDbCheck('Gmail transport rejects invalid message and recipient before network access', $invalidMessage['error_code'] === 'invalid_message' && $invalidRecipient['error_code'] === 'invalid_recipient');
foreach (['NIVAD_GMAIL_SMTP_USER', 'NIVAD_GMAIL_SMTP_APP_PASSWORD', 'NIVAD_GMAIL_SMTP_HOST', 'NIVAD_GMAIL_SMTP_PORT'] as $key) {
    putenv($key);
    unset($_ENV[$key], $_SERVER[$key]);
}

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: non-database contract/unit checks; no SQL, network, email, or application data changes.\n";

exit($failed === 0 ? 0 : 1);
