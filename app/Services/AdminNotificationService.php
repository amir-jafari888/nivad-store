<?php

declare(strict_types=1);

final class AdminNotificationService
{
    private AdminNotificationRepository $repository;

    private const SEVERITIES = [
        'info',
        'success',
        'warning',
        'critical',
    ];

    public function __construct(
        ?AdminNotificationRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new AdminNotificationRepository();
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
        $eventKey = trim($eventKey);
        $severity = strtolower(trim($severity));
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
        $sensitiveParts = [
            'password', 'token', 'secret', 'authorization',
            'cookie', 'session_id', 'api_key', 'private_key',
            'card_number', 'cvv',
        ];
        $safe = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            foreach ($sensitiveParts as $part) {
                if (str_contains($normalizedKey, $part)) {
                    $safe[$key] = '[REDACTED]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $safe[$key] = $this->sanitizePayload($value);
            } elseif (is_string($value) && strlen($value) > 20000) {
                $safe[$key] = '[OMITTED: VALUE TOO LARGE]';
            } elseif (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
            } else {
                $safe[$key] = '[OMITTED: UNSUPPORTED VALUE]';
            }
        }

        return $safe;
    }

    public function enqueueEmail(
        ?int $notificationId,
        ?int $recipientAdminId,
        string $recipientEmail,
        string $subject,
        string $bodyText
    ): int {
        $recipientEmail = trim($recipientEmail);
        $subject = trim($subject);

        if (
            filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) === false ||
            mb_strlen($recipientEmail) > 254
        ) {
            throw new InvalidArgumentException(
                'A valid recipient email address is required.'
            );
        }

        if ($subject === '' || mb_strlen($subject) > 255) {
            throw new InvalidArgumentException(
                'Email subject must contain 1–255 characters.'
            );
        }

        if (trim($bodyText) === '') {
            throw new InvalidArgumentException(
                'Email body is required.'
            );
        }

        return $this->repository->enqueueEmail(
            $notificationId,
            $recipientAdminId,
            $recipientEmail,
            $subject,
            $bodyText
        );
    }
}
