<?php

declare(strict_types=1);

final class AdminNotificationCenterService
{
    private AdminNotificationCenterRepository $repository;
    private AdminNotificationPolicy $policy;

    private const SEVERITY_RANK = ['info' => 1, 'success' => 2, 'warning' => 3, 'critical' => 4];

    public function __construct(?AdminNotificationCenterRepository $repository = null, ?AdminNotificationPolicy $policy = null)
    {
        $this->repository = $repository ?? new AdminNotificationCenterRepository();
        $this->policy = $policy ?? new AdminNotificationPolicy();
    }

    public function inbox(int $adminId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if ($adminId < 1) throw new InvalidArgumentException('شناسه مدیر نامعتبر است.');
        return [
            'items' => $this->repository->listForAdmin($adminId, $filters, $limit, $offset),
            'unread_count' => $this->repository->unreadCount($adminId),
        ];
    }

    public function markRead(int $notificationId, int $adminId): bool
    {
        if ($notificationId < 1 || $adminId < 1) throw new InvalidArgumentException('شناسه اعلان نامعتبر است.');
        return $this->repository->markRead($notificationId, $adminId);
    }

    public function markAllRead(int $adminId): int
    {
        if ($adminId < 1) throw new InvalidArgumentException('شناسه مدیر نامعتبر است.');
        return $this->repository->markAllRead($adminId);
    }

    public function acknowledge(int $notificationId, int $adminId): bool
    {
        if ($notificationId < 1 || $adminId < 1) throw new InvalidArgumentException('شناسه اعلان نامعتبر است.');
        return $this->repository->acknowledge($notificationId, $adminId);
    }

    public function preferences(int $adminId): array
    {
        if ($adminId < 1) throw new InvalidArgumentException('شناسه مدیر نامعتبر است.');
        return $this->repository->preferences($adminId);
    }

    public function savePreference(int $adminId, array $input): void
    {
        $eventKey = trim((string)($input['event_key'] ?? '*'));
        if ($eventKey !== '*') $eventKey = $this->policy->normalizeEventKey($eventKey);
        $severity = strtolower(trim((string)($input['minimum_severity'] ?? 'info')));
        if (!isset(self::SEVERITY_RANK[$severity])) throw new InvalidArgumentException('حداقل شدت اعلان نامعتبر است.');
        $quietStart = $input['quiet_hours_start'] ?? null;
        $quietEnd = $input['quiet_hours_end'] ?? null;
        foreach ([$quietStart, $quietEnd] as $time) {
            if ($time !== null && (!is_string($time) || preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) !== 1)) {
                throw new InvalidArgumentException('بازه سکوت اعلان باید ساعت معتبر HH:MM باشد.');
            }
        }
        if (($quietStart === null) !== ($quietEnd === null)) throw new InvalidArgumentException('شروع و پایان بازه سکوت باید با هم تنظیم شوند.');
        $timezone = trim((string)($input['timezone'] ?? 'UTC'));
        try { new DateTimeZone($timezone); } catch (Throwable) { throw new InvalidArgumentException('منطقه زمانی نامعتبر است.'); }
        $this->repository->savePreference($adminId, $eventKey, [
            'in_app_enabled' => (bool)($input['in_app_enabled'] ?? true),
            'email_enabled' => (bool)($input['email_enabled'] ?? false),
            'minimum_severity' => $severity,
            'quiet_hours_start' => $quietStart,
            'quiet_hours_end' => $quietEnd,
            'timezone' => $timezone
        ]);
    }

    public function publishEvent(string $eventKey, string $title, string $message, ?string $entityType = null, int|string|null $entityId = null, ?array $payload = null, ?int $actorAdminId = null): int
    {
        $eventKey = $this->policy->normalizeEventKey($eventKey);
        $configured = $this->repository->eventPolicy($eventKey);
        if ($configured === null || !(bool)$configured['enabled']) return 0;

        $severity = $this->policy->normalizeSeverity((string)$configured['severity']);
        $title = trim($title);
        $message = trim($message);
        if ($title === '' || mb_strlen($title) > 255 || $message === '' || mb_strlen($message) > 5000) {
            throw new InvalidArgumentException('عنوان یا متن اعلان معتبر نیست.');
        }
        if ($actorAdminId !== null && $actorAdminId < 1) throw new InvalidArgumentException('شناسه عامل اعلان نامعتبر است.');

        $recipients = [];
        $prefs = [];
        foreach ($this->repository->activeAdmins() as $admin) {
            $all = $this->repository->preferences((int)$admin['id']);
            $preference = $all[$eventKey] ?? $all['*'];
            if (!$preference['in_app_enabled']) continue;
            if (self::SEVERITY_RANK[$severity] < self::SEVERITY_RANK[$preference['minimum_severity']]) continue;
            if ($this->isQuietNow($preference)) continue;
            $admin['email_enabled'] = (bool)$preference['email_enabled'];
            $recipients[] = $admin;
        }
        if ($recipients === []) return 0;

        $payload = $payload === null ? null : SensitiveDataRedactor::redact($payload);
        return $this->repository->publish([
            'event_key' => $eventKey, 'severity' => $severity, 'title' => $title,
            'message' => $message, 'entity_type' => $entityType, 'entity_id' => $entityId,
            'payload' => $payload, 'actor_admin_id' => $actorAdminId
        ], $recipients, [
            'email_enabled' => (bool)$configured['email_enabled'],
            'dedupe_seconds' => (int)$configured['dedupe_seconds'],
            'maximum_per_window' => (int)$configured['maximum_per_window'],
            'window_seconds' => (int)$configured['window_seconds']
        ]);
    }

    private function isQuietNow(array $preference): bool
    {
        if (empty($preference['quiet_hours_start']) || empty($preference['quiet_hours_end'])) return false;
        try { $zone = new DateTimeZone((string)$preference['timezone']); } catch (Throwable) { $zone = new DateTimeZone('UTC'); }
        $now = new DateTimeImmutable('now', $zone);
        $current = $now->format('H:i');
        $start = (string)$preference['quiet_hours_start'];
        $end = (string)$preference['quiet_hours_end'];
        return $start <= $end
            ? ($current >= $start && $current < $end)
            : ($current >= $start || $current < $end);
    }
}
