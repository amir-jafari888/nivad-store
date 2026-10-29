<?php

declare(strict_types=1);

/**
 * Persistence boundary for Admin Panel foundation workflows.
 * Implementations may access storage; contract tests can inject an in-memory fake.
 */
interface AdminFoundationRepositoryInterface
{
    public function createContentRevision(string $entityType, string $entityId, string $status, string $snapshotJson, int $createdByAdminId, ?string $scheduledAt = null): int;

    public function saveView(int $adminUserId, string $moduleKey, string $name, string $filtersJson, ?string $sortJson, bool $shared): int;

    public function enqueueOperationJob(string $jobType, string $payloadJson, int $createdByAdminId): int;

    public function updateOperationJob(int $jobId, string $status, int $progressPercent, ?string $resultJson = null, ?string $errorSummary = null): bool;

    public function recordIncident(string $incidentKey, string $severity, string $source, string $title, ?string $description, ?string $contextJson): int;

    public function createApprovalRequest(string $actionKey, string $entityType, string $entityId, int $requesterAdminId, string $payloadJson, ?string $beforeSnapshotJson): int;

    public function decideApprovalRequest(int $requestId, int $reviewerAdminId, string $decision, ?string $decisionNote): bool;

    public function createMediaAsset(string $storageKey, string $originalName, string $mimeType, int $fileSizeBytes, string $sha256, ?string $altText, int $uploadedByAdminId): int;

    public function createSupportTicket(string $ticketKey, ?int $customerId, string $priority, string $subject, string $description, ?int $assignedAdminId, ?int $createdByAdminId): int;

    public function recordAnalyticsEvent(string $eventKey, ?string $entityType, int|string|null $entityId, ?int $actorAdminId, ?float $numericValue, ?string $dimensionsJson): int;
}
