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
require_once __DIR__ . '/app/Core/AdminEmailOutboxRepositoryInterface.php';
require_once __DIR__ . '/app/Core/AdminFoundationRepositoryInterface.php';
require_once __DIR__ . '/app/Services/AdminGlobalSearchService.php';
require_once __DIR__ . '/app/Services/AdminFoundationService.php';
require_once __DIR__ . '/app/Services/AdminFeatureAccessService.php';
require_once __DIR__ . '/app/Core/AdminRequestContext.php';
require_once __DIR__ . '/app/Core/AdminActorContext.php';
require_once __DIR__ . '/app/Core/Exceptions/AppException.php';
require_once __DIR__ . '/app/Core/Exceptions/AuthorizationException.php';
require_once __DIR__ . '/app/Services/AdminFeatureContractValidator.php';
require_once __DIR__ . '/app/Services/AdminAuditSnapshotService.php';
require_once __DIR__ . '/app/Services/AdminOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminSecurityEventPolicy.php';
require_once __DIR__ . '/app/Services/AdminNotificationPolicy.php';
require_once __DIR__ . '/app/Core/AdminEmailOutboxRepositoryInterface.php';
require_once __DIR__ . '/app/Services/AdminEmailOutboxWorker.php';
require_once __DIR__ . '/app/Services/AdminHealthCheckService.php';
require_once __DIR__ . '/app/Services/AdminMediaStorageService.php';
require_once __DIR__ . '/app/Services/AdminPreviewTokenService.php';
require_once __DIR__ . '/app/Services/AdminSavedViewValidator.php';
require_once __DIR__ . '/app/Services/AdminContentPublishingPolicy.php';
require_once __DIR__ . '/app/Services/AdminReportExportService.php';
require_once __DIR__ . '/app/Services/AdminBulkOperationPolicy.php';
require_once __DIR__ . '/app/Services/AdminAccountPolicy.php';
require_once __DIR__ . '/app/Services/AdminSupportTicketPolicy.php';
require_once __DIR__ . '/app/Services/GmailSmtpTransport.php';
require_once __DIR__ . '/app/Services/AdminEmailOutboxWorker.php';

$registry = AdminFeatureRegistry::all();
$contract = (new AdminFeatureContractValidator())->validate();

adminNonDbCheck('Exactly 44 canonical Admin Panel feature contracts exist', count($registry) === 44);
adminNonDbCheck('Every feature ID follows the ADM-NNN contract', count(array_filter(array_keys($registry), static fn($id) => preg_match('/^ADM-\d{3}$/', $id) === 1)) === 44);
adminNonDbCheck('Every feature has a permission and dependency metadata', count(array_filter($registry, static fn($item) => isset($item['permission'], $item['dependencies'], $item['database_required']))) === 44);
adminNonDbCheck('All feature permissions exist in the canonical permission catalog', $contract['permission_count'] >= 46 && count(array_filter($contract['errors'], static fn($error) => $error['code'] === 'permission_missing_from_catalog')) === 0);
adminNonDbCheck('All feature dependencies exist and the dependency graph is acyclic', $contract['valid'] === true && $contract['dependency_edges_checked'] > 0);
adminNonDbCheck('Feature contract validator does not access the database', $contract['database_accessed'] === false);

$fakeOutboxRepository = new class implements AdminEmailOutboxRepositoryInterface {
    public array $rows = [];
    public array $sent = [];
    public array $failed = [];

    public function claimDue(int $limit): array
    {
        return array_slice($this->rows, 0, $limit);
    }

    public function markSent(int $outboxId, int $attemptCount, ?string $providerMessageId): bool
    {
        $this->sent[] = ['id' => $outboxId, 'attempts' => $attemptCount, 'message_id' => $providerMessageId];
        return true;
    }

    public function markFailed(int $outboxId, int $attemptCount, string $errorCode, ?DateTimeImmutable $nextAttemptAt, bool $terminal): bool
    {
        $this->failed[] = ['id' => $outboxId, 'attempts' => $attemptCount, 'error_code' => $errorCode, 'next_attempt_at' => $nextAttemptAt?->getTimestamp(), 'terminal' => $terminal];
        return true;
    }
};
$fakeOutboxRepository->rows = [
    ['id' => 10, 'attempts' => 0, 'recipient_email' => 'ok@example.com', 'subject' => 'Test', 'body_text' => 'Body'],
    ['id' => 11, 'attempts' => 0, 'subject' => 'Malformed row'],
    ['id' => 12, 'attempts' => 0, 'recipient_email' => 'throw@example.com', 'subject' => 'Test', 'body_text' => 'Body'],
];
$outboxWorker = new AdminEmailOutboxWorker(
    $fakeOutboxRepository,
    static function (string $email, string $subject, string $body, ?string $name = null): array {
        if ($email === 'throw@example.com') {
            throw new RuntimeException('simulated provider exception');
        }
        return ['success' => true, 'message_id' => 'test-message-1'];
    },
    static fn(): int => 1000
);
$outboxSummary = $outboxWorker->processBatch(20);
adminNonDbCheck('Email outbox worker records successful deliveries', $outboxSummary['sent'] === 1 && count($fakeOutboxRepository->sent) === 1);
adminNonDbCheck('Email outbox worker terminally handles malformed rows', $outboxSummary['invalid_rows'] === 1 && $fakeOutboxRepository->failed[0]['error_code'] === 'invalid_message' && $fakeOutboxRepository->failed[0]['terminal'] === true);
adminNonDbCheck('Email outbox worker retries sender exceptions with backoff', $outboxSummary['retry_scheduled'] === 1 && $fakeOutboxRepository->failed[1]['next_attempt_at'] === 1060 && $fakeOutboxRepository->failed[1]['terminal'] === false);

$supportPolicy = new AdminSupportTicketPolicy();
$validTicket = $supportPolicy->validateCreation('Order delivery', 'Package is delayed.', 'high', 12, 7, null);
$validInternalMessage = $supportPolicy->validateMessage('Internal note', 'internal', 7, null);
$customerInternalMessageRejected = false;
try {
    $supportPolicy->validateMessage('Internal note', 'internal', null, 12);
} catch (InvalidArgumentException $exception) {
    $customerInternalMessageRejected = true;
}
$invalidTransitionRejected = false;
try {
    $supportPolicy->assertTransition('closed', 'resolved');
} catch (InvalidArgumentException $exception) {
    $invalidTransitionRejected = true;
}
adminNonDbCheck('Support ticket policy normalizes valid ticket creation', $validTicket['priority'] === 'high' && $validTicket['status'] === 'open');
adminNonDbCheck('Support ticket policy permits admin-only internal notes and rejects customer internal notes', $validInternalMessage['visibility'] === 'internal' && $customerInternalMessageRejected);
adminNonDbCheck('Support ticket policy rejects invalid lifecycle transitions', $invalidTransitionRejected);

$accountPolicy = new AdminAccountPolicy();
$validAdminCreate = $accountPolicy->validateCreate(
    'Operations Admin',
    'ops-admin@example.com',
    'Strong-Admin-Password-482!',
    ['catalog-admin', 'catalog-admin']
);
$selfDisableRejected = false;
try {
    $accountPolicy->validateLifecycleAction('disable', 7, 7, false, 2);
} catch (InvalidArgumentException $exception) {
    $selfDisableRejected = true;
}
$lastSuperAdminRejected = false;
try {
    $accountPolicy->validateLifecycleAction('delete', 7, 8, true, 1);
} catch (InvalidArgumentException $exception) {
    $lastSuperAdminRejected = true;
}
$roleEscalationRejected = false;
try {
    $accountPolicy->validateRoleAssignment(7, 8, ['super-admin'], false, false, 2);
} catch (InvalidArgumentException $exception) {
    $roleEscalationRejected = true;
}
$weakPasswordRejected = false;
try {
    $accountPolicy->validatePassword('weakpass');
} catch (InvalidArgumentException $exception) {
    $weakPasswordRejected = true;
}
adminNonDbCheck('Admin account policy normalizes email and deduplicates role assignments', $validAdminCreate['email'] === 'ops-admin@example.com' && $validAdminCreate['role_slugs'] === ['catalog-admin']);
adminNonDbCheck('Admin account policy rejects self-disable and last Super Admin removal', $selfDisableRejected && $lastSuperAdminRejected);
adminNonDbCheck('Admin account policy prevents unauthorized Super Admin assignment', $roleEscalationRejected);
adminNonDbCheck('Admin account policy enforces a strong admin password baseline', $weakPasswordRejected);

$securityEventPolicy = new AdminSecurityEventPolicy();
$failedLoginEvent = $securityEventPolicy->requireDefinition('admin.auth.login_failed');
$criticalEvent = $securityEventPolicy->requireDefinition('admin.sensitive_data.exposure_suspected');
adminNonDbCheck('Security event policy defines authentication events consistently', $failedLoginEvent['severity'] === 'warning' && $securityEventPolicy->definition('admin.auth.logout')['severity'] === 'info');
adminNonDbCheck('Critical security events request notification and incident handling', $criticalEvent['severity'] === 'critical' && $criticalEvent['notify_email'] === true && $criticalEvent['open_incident'] === true);
adminNonDbCheck('Unknown security event keys fail closed', $securityEventPolicy->definition('admin.unknown.event') === null);

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

$fakeOutbox = new class implements AdminEmailOutboxRepositoryInterface {
    public array $sent = [];
    public array $failed = [];
    public function claimDue(int $limit): array {
        return [
            ['id' => 1, 'attempts' => 0, 'recipient_email' => 'ok@example.com', 'subject' => 'Hello', 'body_text' => 'Body'],
            ['id' => 2, 'attempts' => 1, 'recipient_email' => 'retry@example.com', 'subject' => 'Hello', 'body_text' => 'Body'],
            ['id' => 3, 'attempts' => 0, 'recipient_email' => 'bad@example.com', 'subject' => 'Hello', 'body_text' => 'Body'],
            ['id' => 0, 'attempts' => 0, 'recipient_email' => 'broken@example.com', 'subject' => 'Hello', 'body_text' => 'Body'],
        ];
    }
    public function markSent(int $outboxId, int $attemptCount, ?string $providerMessageId): bool {
        $this->sent[] = ['id' => $outboxId, 'attempts' => $attemptCount, 'message_id' => $providerMessageId];
        return true;
    }
    public function markFailed(int $outboxId, int $attemptCount, string $errorCode, ?DateTimeImmutable $nextAttemptAt, bool $terminal): bool {
        $this->failed[] = ['id' => $outboxId, 'attempts' => $attemptCount, 'error' => $errorCode, 'next' => $nextAttemptAt?->getTimestamp(), 'terminal' => $terminal];
        return true;
    }
};
$worker = new AdminEmailOutboxWorker(
    $fakeOutbox,
    static function (string $email): array {
        return match ($email) {
            'ok@example.com' => ['success' => true, 'message_id' => 'provider-id-1', 'error_code' => null],
            'retry@example.com' => ['success' => false, 'message_id' => null, 'error_code' => 'smtp_connection_failed'],
            default => ['success' => false, 'message_id' => null, 'error_code' => 'invalid_recipient'],
        };
    },
    static fn(): int => 1000
);
$workerSummary = $worker->processBatch(500);
adminNonDbCheck('Email outbox worker bounds batches and tracks successful delivery attempts', $workerSummary['claimed'] === 4 && $workerSummary['sent'] === 1 && $fakeOutbox->sent[0]['attempts'] === 1);
adminNonDbCheck('Email outbox worker schedules exponential retry without storing SMTP details', $workerSummary['retry_scheduled'] === 1 && $fakeOutbox->failed[0]['next'] === 1120 && $fakeOutbox->failed[0]['terminal'] === false && $fakeOutbox->failed[0]['error'] === 'smtp_connection_failed');
adminNonDbCheck('Email outbox worker terminally fails permanent errors and ignores malformed rows', $workerSummary['failed'] === 1 && $workerSummary['invalid_rows'] === 1 && $fakeOutbox->failed[1]['terminal'] === true && $fakeOutbox->failed[1]['next'] === null);

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

$fakeFoundationRepository = new class implements AdminFoundationRepositoryInterface {
    public array $calls = [];
    public function createContentRevision(string $entityType, string $entityId, string $status, string $snapshotJson, int $createdByAdminId, ?string $scheduledAt = null): int { $this->calls['revision'] = func_get_args(); return 101; }
    public function saveView(int $adminUserId, string $moduleKey, string $name, string $filtersJson, ?string $sortJson, bool $shared): int { $this->calls['view'] = func_get_args(); return 102; }
    public function enqueueOperationJob(string $jobType, string $payloadJson, int $createdByAdminId): int { $this->calls['job'] = func_get_args(); return 103; }
    public function updateOperationJob(int $jobId, string $status, int $progressPercent, ?string $resultJson = null, ?string $errorSummary = null): bool { $this->calls['job_update'] = func_get_args(); return true; }
    public function recordIncident(string $incidentKey, string $severity, string $source, string $title, ?string $description, ?string $contextJson): int { $this->calls['incident'] = func_get_args(); return 104; }
    public function createApprovalRequest(string $actionKey, string $entityType, string $entityId, int $requesterAdminId, string $payloadJson, ?string $beforeSnapshotJson): int { $this->calls['approval'] = func_get_args(); return 105; }
    public function decideApprovalRequest(int $requestId, int $reviewerAdminId, string $decision, ?string $decisionNote): bool { $this->calls['decision'] = func_get_args(); return true; }
    public function createMediaAsset(string $storageKey, string $originalName, string $mimeType, int $fileSizeBytes, string $sha256, ?string $altText, int $uploadedByAdminId): int { $this->calls['media'] = func_get_args(); return 106; }
    public function createSupportTicket(string $ticketKey, ?int $customerId, string $priority, string $subject, string $description, ?int $assignedAdminId, ?int $createdByAdminId): int { $this->calls['ticket'] = func_get_args(); return 107; }
    public function recordAnalyticsEvent(string $eventKey, ?string $entityType, int|string|null $entityId, ?int $actorAdminId, ?float $numericValue, ?string $dimensionsJson): int { $this->calls['analytics'] = func_get_args(); return 108; }
};
$fakeActors = new AdminActorContext(static fn(): int => 7, static fn(): bool => true);
$foundationService = new AdminFoundationService(
    $fakeFoundationRepository,
    $fakeActors,
    new AdminSavedViewValidator(),
    new AdminContentPublishingPolicy(),
    new AdminBulkOperationPolicy(),
    null
);
$revisionId = $foundationService->createContentRevision(
    'product',
    'SKU-TEST',
    'draft',
    ['title' => 'Test product', 'password' => 'foundation-secret'],
    7
);
$revisionPayload = json_decode($fakeFoundationRepository->calls['revision'][3], true);
adminNonDbCheck('Foundation service uses injected persistence boundary and redacts revision snapshots', $revisionId === 101 && ($revisionPayload['password'] ?? null) === '[REDACTED]' && !str_contains($fakeFoundationRepository->calls['revision'][3], 'foundation-secret'));
$viewId = $foundationService->saveView(7, 'products', 'Test view', [['field' => 'status', 'operator' => 'eq', 'value' => 'active']], ['field' => 'created_at', 'direction' => 'desc'], false);
$viewFilters = json_decode($fakeFoundationRepository->calls['view'][3], true);
adminNonDbCheck('Foundation service validates saved views before persistence', $viewId === 102 && is_array($viewFilters) && $viewFilters[0]['field'] === 'status');
$approvalId = $foundationService->requestApproval('product.publish', 'product', 'SKU-TEST', 7, ['note' => 'review', 'api_token' => 'sensitive'], ['status' => 'draft']);
$approvalPayload = json_decode($fakeFoundationRepository->calls['approval'][4], true);
adminNonDbCheck('Foundation service redacts approval payloads and validates the actor', $approvalId === 105 && ($approvalPayload['api_token'] ?? null) === '[REDACTED]' && $fakeFoundationRepository->calls['approval'][3] === 7);
$mediaId = $foundationService->registerMediaAsset('media/test-image.webp', 'test-image.webp', 'image/webp', 512, hash('sha256', 'image-bytes'), 7, 'Test image');
adminNonDbCheck('Foundation service validates and forwards media metadata without filesystem or DB access', $mediaId === 106 && $fakeFoundationRepository->calls['media'][0] === 'media/test-image.webp');
$ticketId = $foundationService->createSupportTicket('Test ticket', 'A non-production contract test', 'normal', null, null, 7);
$analyticsId = $foundationService->recordAnalytics('admin.test.event', 'test', '42', 7, 1.5, ['cookie' => 'private']);
$analyticsPayload = json_decode($fakeFoundationRepository->calls['analytics'][5], true);
adminNonDbCheck('Foundation service forwards support and analytics commands through the injected boundary', $ticketId === 107 && $analyticsId === 108 && ($analyticsPayload['cookie'] ?? null) === '[REDACTED]');
$foundationService->updateOperation(5, 'completed', 12, ['token' => 'private-result']);
adminNonDbCheck('Foundation service normalizes job completion and redacts result payloads', $fakeFoundationRepository->calls['job_update'][2] === 100 && !str_contains($fakeFoundationRepository->calls['job_update'][3], 'private-result'));

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: non-database contract/unit checks; no SQL, network, email, or application data changes.\n";

exit($failed === 0 ? 0 : 1);
