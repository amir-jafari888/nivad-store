<?php

declare(strict_types=1);

/**
 * Validated application layer for the additive Admin Panel foundation tables.
 * Route authorization and CSRF checks must be enforced by callers.
 */
final class AdminFoundationService
{
    private AdminFoundationRepositoryInterface $repository;
    private AdminActorContext $actors;
    private AdminSavedViewValidator $savedViewValidator;
    private AdminContentPublishingPolicy $contentPublishingPolicy;
    private AdminBulkOperationPolicy $bulkOperationPolicy;
    private ?AdminFeatureAccessService $featureAccess;
    private AdminSupportTicketPolicy $supportTicketPolicy;

    private const SENSITIVE_KEY_PARTS = [
        'password', 'token', 'secret', 'authorization', 'cookie',
        'session_id', 'api_key', 'private_key', 'card_number', 'cvv',
    ];

    public function __construct(
        ?AdminFoundationRepositoryInterface $repository = null,
        ?AdminActorContext $actors = null,
        ?AdminSavedViewValidator $savedViewValidator = null,
        ?AdminContentPublishingPolicy $contentPublishingPolicy = null,
        ?AdminBulkOperationPolicy $bulkOperationPolicy = null,
        ?AdminFeatureAccessService $featureAccess = null,
        ?AdminSupportTicketPolicy $supportTicketPolicy = null
    ) {
        $this->repository =
            $repository ??
            new AdminFoundationRepository();
        $this->actors =
            $actors ??
            new AdminActorContext();
        $this->savedViewValidator =
            $savedViewValidator ??
            new AdminSavedViewValidator();
        $this->contentPublishingPolicy =
            $contentPublishingPolicy ??
            new AdminContentPublishingPolicy();
        $this->bulkOperationPolicy =
            $bulkOperationPolicy ??
            new AdminBulkOperationPolicy();
        $this->featureAccess = $featureAccess;
        $this->supportTicketPolicy =
            $supportTicketPolicy ??
            new AdminSupportTicketPolicy();
    }

    public function createContentRevision(
        string $entityType,
        string $entityId,
        string $status,
        array $snapshot,
        int $createdByAdminId,
        ?string $scheduledAt = null
    ): int {
        $createdByAdminId = $this->actors->requireCurrentAdmin($createdByAdminId);
        $status = strtolower(trim($status));
        $entityType = $this->requiredText($entityType, 100, 'Entity type');
        $entityId = $this->requiredText($entityId, 100, 'Entity ID');

        $scheduledAt = $this->contentPublishingPolicy->normalizeSchedule(
            $status,
            $scheduledAt
        );

        return $this->repository->createContentRevision(
            $entityType,
            $entityId,
            $status,
            $this->json($snapshot),
            $createdByAdminId,
            $scheduledAt
        );
    }

    public function saveView(
        int $adminUserId,
        string $moduleKey,
        string $name,
        array $filters,
        ?array $sort = null,
        bool $shared = false
    ): int {
        $adminUserId = $this->actors->requireCurrentAdmin($adminUserId);

        $filters = $this->savedViewValidator->validateFilters($filters);
        $sort = $this->savedViewValidator->validateSort($sort);

        return $this->repository->saveView(
            $adminUserId,
            $this->requiredText($moduleKey, 100, 'Module key'),
            $this->requiredText($name, 160, 'View name'),
            $this->json($filters),
            $sort === null ? null : $this->json($sort),
            $shared
        );
    }

    public function enqueueOperation(
        string $jobType,
        array $payload,
        int $createdByAdminId,
        bool $confirmed = false,
        ?int $approvalRequestId = null
    ): int {
        $createdByAdminId = $this->actors->requireCurrentAdmin($createdByAdminId);
        $operation = $this->bulkOperationPolicy->validate(
            $jobType,
            $payload,
            $confirmed,
            $approvalRequestId
        );

        $featureAccess = $this->featureAccess ?? new AdminFeatureAccessService();

        if (
            !$featureAccess->canAccess($createdByAdminId, 'ADM-031') ||
            !$featureAccess->canAccess($createdByAdminId, $operation['feature_id'])
        ) {
            throw new AuthorizationException('The admin lacks permission for this bulk operation.');
        }

        return $this->repository->enqueueOperationJob(
            $operation['job_type'],
            $this->json($operation['payload']),
            $createdByAdminId
        );
    }

    public function updateOperation(
        int $jobId,
        string $status,
        int $progressPercent,
        ?array $result = null,
        ?string $errorSummary = null
    ): bool {
        // This service method is an authenticated admin command. Background
        // workers should update the repository through a dedicated worker path.
        $this->actors->requireCurrentAdmin();

        if ($jobId < 1) {
            return false;
        }

        $allowed = ['queued', 'running', 'completed', 'failed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported operation status.');
        }

        if ($status === 'completed') {
            $progressPercent = 100;
        }

        return $this->repository->updateOperationJob(
            $jobId,
            $status,
            max(0, min(100, $progressPercent)),
            $result === null ? null : $this->json($result),
            $errorSummary === null ? null : mb_substr($errorSummary, 0, 1000)
        );
    }

    public function recordIncident(
        string $incidentKey,
        string $severity,
        string $source,
        string $title,
        ?string $description = null,
        ?array $context = null
    ): int {
        $severity = strtolower(trim($severity));
        if (!in_array($severity, ['info', 'warning', 'critical'], true)) {
            throw new InvalidArgumentException('Unsupported incident severity.');
        }

        return $this->repository->recordIncident(
            $this->requiredText($incidentKey, 160, 'Incident key'),
            $severity,
            $this->requiredText($source, 120, 'Incident source'),
            $this->requiredText($title, 255, 'Incident title'),
            $description === null ? null : mb_substr(trim($description), 0, 10000),
            $context === null ? null : $this->json($context)
        );
    }

    public function requestApproval(
        string $actionKey,
        string $entityType,
        string $entityId,
        int $requesterAdminId,
        array $payload,
        ?array $beforeSnapshot = null
    ): int {
        $requesterAdminId = $this->actors->requireCurrentAdmin($requesterAdminId);

        return $this->repository->createApprovalRequest(
            $this->requiredText($actionKey, 120, 'Action key'),
            $this->requiredText($entityType, 100, 'Entity type'),
            $this->requiredText($entityId, 100, 'Entity ID'),
            $requesterAdminId,
            $this->json($payload),
            $beforeSnapshot === null ? null : $this->json($beforeSnapshot)
        );
    }

    public function decideApproval(
        int $requestId,
        int $reviewerAdminId,
        string $decision,
        ?string $note = null
    ): bool {
        if ($requestId < 1) {
            return false;
        }
        $reviewerAdminId = $this->actors->requireCurrentAdmin($reviewerAdminId);

        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new InvalidArgumentException('Unsupported approval decision.');
        }

        return $this->repository->decideApprovalRequest(
            $requestId,
            $reviewerAdminId,
            $decision,
            $note === null ? null : mb_substr(trim($note), 0, 1000)
        );
    }

    public function registerMediaAsset(
        string $storageKey,
        string $originalName,
        string $mimeType,
        int $fileSizeBytes,
        string $sha256,
        int $uploadedByAdminId,
        ?string $altText = null
    ): int {
        $uploadedByAdminId = $this->actors->requireCurrentAdmin($uploadedByAdminId);
        if ($fileSizeBytes < 1) {
            throw new InvalidArgumentException('Invalid media metadata.');
        }

        if (!preg_match('/^[a-f0-9]{64}$/i', $sha256)) {
            throw new InvalidArgumentException('Media SHA-256 must be 64 hexadecimal characters.');
        }

        if (str_contains($storageKey, '..') || str_starts_with($storageKey, '/')) {
            throw new InvalidArgumentException('Storage key must be a relative opaque key.');
        }

        return $this->repository->createMediaAsset(
            $this->requiredText($storageKey, 500, 'Storage key'),
            $this->requiredText($originalName, 255, 'Original filename'),
            $this->requiredText($mimeType, 120, 'MIME type'),
            $fileSizeBytes,
            strtolower($sha256),
            $altText === null ? null : mb_substr(trim($altText), 0, 500),
            $uploadedByAdminId
        );
    }

    public function createSupportTicket(
        string $subject,
        string $description,
        string $priority = 'normal',
        ?int $customerId = null,
        ?int $assignedAdminId = null,
        ?int $createdByAdminId = null
    ): int {
        if ($createdByAdminId !== null) {
            $createdByAdminId = $this->actors->requireCurrentAdmin($createdByAdminId);
        }

        $ticket = $this->supportTicketPolicy->validateCreation(
            $subject,
            $description,
            $priority,
            $customerId,
            $assignedAdminId,
            $createdByAdminId
        );

        $ticketKey = 'NV-' . strtoupper(bin2hex(random_bytes(6)));

        return $this->repository->createSupportTicket(
            $ticketKey,
            $ticket['customer_id'],
            $ticket['priority'],
            $ticket['subject'],
            $ticket['description'],
            $ticket['assigned_admin_id'],
            $ticket['created_by_admin_id']
        );
    }

    public function recordAnalytics(
        string $eventKey,
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?int $actorAdminId = null,
        ?float $numericValue = null,
        ?array $dimensions = null
    ): int {
        if ($actorAdminId !== null) {
            $actorAdminId = $this->actors->requireCurrentAdmin($actorAdminId);
        }

        return $this->repository->recordAnalyticsEvent(
            $this->requiredText($eventKey, 120, 'Analytics event key'),
            $entityType === null ? null : mb_substr(trim($entityType), 0, 100),
            $entityId,
            $actorAdminId,
            $numericValue,
            $dimensions === null ? null : $this->json($dimensions)
        );
    }

    private function requiredText(
        string $value,
        int $maxLength,
        string $label
    ): string {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new InvalidArgumentException(
                $label . ' must contain 1–' . $maxLength . ' characters.'
            );
        }

        return $value;
    }

    private function json(array $data): string
    {
        try {
            return json_encode(
                $this->redact($data),
                JSON_THROW_ON_ERROR |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Admin foundation payload could not be encoded.',
                0,
                $exception
            );
        }
    }

    private function redact(array $data): array
    {
        return SensitiveDataRedactor::redact($data);
    }
}
