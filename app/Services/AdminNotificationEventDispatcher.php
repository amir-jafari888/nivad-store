<?php

declare(strict_types=1);

/**
 * Small application boundary for commerce/content events.
 * Call dispatch() only after the originating transaction has committed.
 */
final class AdminNotificationEventDispatcher
{
    private AdminNotificationCenterService $notifications;

    public function __construct(?AdminNotificationCenterService $notifications = null)
    {
        $this->notifications = $notifications ?? new AdminNotificationCenterService();
    }

    public function dispatch(string $eventKey, string $title, string $message, ?string $entityType = null, int|string|null $entityId = null, ?array $payload = null, ?int $actorAdminId = null): int
    {
        try {
            return $this->notifications->publishEvent($eventKey, $title, $message, $entityType, $entityId, $payload, $actorAdminId);
        } catch (Throwable $exception) {
            error_log('Admin notification dispatch failed: ' . get_class($exception));
            return 0;
        }
    }
}
