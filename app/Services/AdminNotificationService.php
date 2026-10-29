<?php

declare(strict_types=1);

final class AdminNotificationService
{
    private AdminNotificationRepository $repository;
    private AdminNotificationPolicy $policy;
    private AdminActorContext $actors;

    private const SEVERITIES = [
        'info',
        'success',
        'warning',
        'critical',
    ];

    public function __construct(
        ?AdminNotificationRepository $repository = null,
        ?AdminNotificationPolicy $policy = null,
        ?AdminActorContext $actors = null
    ) {
        $this->repository =
            $repository ??
            new AdminNotificationRepository();
        $this->policy =
            $policy ??
            new AdminNotificationPolicy();
        $this->actors =
            $actors ??
            new AdminActorContext();
    }

    public function createForAdmins(
        string $eventKey,
        string $severity,
        string $title,
        string $message,
        array $recipientAdminIds,
        ?int $createdByAdminId = null,
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?array $payload = null
    ): int {
        $eventKey = $this->policy->normalizeEventKey($eventKey);
        $severity = $this->policy->normalizeSeverity($severity);
        $title = trim($title);
        $message = trim($message);

        if ($eventKey === '' || mb_strlen($eventKey) > 120) {
            throw new InvalidArgumentException(
                'Notification event key must contain 1–120 characters.'
            );
        }

        if (!in_array($severity, self::SEVERITIES, true)) {
            throw new InvalidArgumentException(
                'Unsupported admin notification severity.'
            );
        }

        if ($title === '' || mb_strlen($title) > 255) {
            throw new InvalidArgumentException(
                'Notification title must contain 1–255 characters.'
            );
        }

        if ($message === '') {
            throw new InvalidArgumentException(
                'Notification message is required.'
            );
        }

        if ($createdByAdminId !== null) {
            $createdByAdminId = $this->actors->requireCurrentAdmin($createdByAdminId);
        }

        if ($entityType !== null) {
            $entityType = trim($entityType);
            if ($entityType === '' || mb_strlen($entityType) > 100) {
                throw new InvalidArgumentException(
                    'Entity type must contain 1–100 characters.'
                );
            }
        }

        return $this->repository->create(
            $eventKey,
            $severity,
            $title,
            $message,
            $recipientAdminIds,
            $createdByAdminId,
            $entityType,
            $entityId,
            $payload === null ? null : $this->sanitizePayload($payload)
        );
    }

    public function listForAdmin(
        int $adminUserId,
        bool $unreadOnly = false,
        int $limit = 50
    ): array {
        return $this->repository->listForAdmin(
            $adminUserId,
            $unreadOnly,
            $limit
        );
    }

    public function markRead(
        int $notificationId,
        int $adminUserId
    ): bool {
        return $this->repository->markReadForAdmin(
            $notificationId,
            $adminUserId
        );
    }

    /**
     * Enqueue only; the database-backed worker is responsible for delivery,
     * retry/backoff and durable status tracking.
     */
    public function enqueueEmail(
        ?int $notificationId,
        ?int $recipientAdminId,
        string $recipientEmail,
        string $subject,
        string $bodyText
    ): int {
        $recipientEmail = trim($recipientEmail);
        $subject = trim($subject);

        if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email recipient.');
        }

        if (
            ($notificationId !== null && $notificationId < 1) ||
            ($recipientAdminId !== null && $recipientAdminId < 1)
        ) {
            throw new InvalidArgumentException('Invalid email outbox reference.');
        }

        if (
            $subject === '' ||
            mb_strlen($subject) > 255 ||
            preg_match('/[\\r\\n\\x00]/', $subject) === 1
        ) {
            throw new InvalidArgumentException('Invalid email subject.');
        }

        if (trim($bodyText) === '' || mb_strlen($bodyText) > 50000) {
            throw new InvalidArgumentException('Email body must contain 1–50000 characters.');
        }

        return $this->repository->enqueueEmail(
            $notificationId,
            $recipientAdminId,
            $recipientEmail,
            $subject,
            $bodyText
        );
    }

    private function sanitizePayload(array $payload): array
    {
        return SensitiveDataRedactor::redact($payload);
    }
}
