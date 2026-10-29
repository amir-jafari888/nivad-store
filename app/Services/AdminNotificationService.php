<?php

declare(strict_types=1);

final class AdminNotificationService
{
    private AdminNotificationRepository $repository;
    private AdminNotificationPolicy $policy;

    private const SEVERITIES = [
        'info',
        'success',
        'warning',
        'critical',
    ];

    public function __construct(
        ?AdminNotificationRepository $repository = null,
        ?AdminNotificationPolicy $policy = null
    ) {
        $this->repository =
            $repository ??
            new AdminNotificationRepository();
        $this->policy =
            $policy ??
            new AdminNotificationPolicy();
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

        if (
            $createdByAdminId !== null &&
            $createdByAdminId < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid notification creator ID.'
            );
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

    private function sanitizePayload(array $payload): array
    {
        return SensitiveDataRedactor::redact($payload);
    }
}
