<?php

declare(strict_types=1);

/**
 * Pure policy for admin notifications. Persistence-based deduplication and
 * rate-limit state must be read/written by the DB-backed caller.
 */
final class AdminNotificationPolicy
{
    private const SEVERITY_RANK = [
        'info' => 1,
        'success' => 2,
        'warning' => 3,
        'critical' => 4,
    ];

    private const LIMITS = [
        'info' => ['minimum_interval' => 300, 'maximum_per_window' => 3, 'dedupe_seconds' => 300],
        'success' => ['minimum_interval' => 120, 'maximum_per_window' => 10, 'dedupe_seconds' => 120],
        'warning' => ['minimum_interval' => 60, 'maximum_per_window' => 20, 'dedupe_seconds' => 60],
        'critical' => ['minimum_interval' => 15, 'maximum_per_window' => 60, 'dedupe_seconds' => 15],
    ];

    public function normalizeEventKey(string $eventKey): string
    {
        $eventKey = strtolower(trim($eventKey));

        if (preg_match('/^[a-z0-9][a-z0-9._:-]{0,119}$/', $eventKey) !== 1) {
            throw new InvalidArgumentException('Notification event key has an invalid format.');
        }

        return $eventKey;
    }

    public function normalizeSeverity(string $severity): string
    {
        $severity = strtolower(trim($severity));

        if (!isset(self::SEVERITY_RANK[$severity])) {
            throw new InvalidArgumentException('Unsupported notification severity.');
        }

        return $severity;
    }

    public function evaluate(
        string $eventKey,
        string $severity,
        int $recentEventCount = 0,
        ?int $lastSentAt = null,
        ?int $now = null
    ): array {
        $eventKey = $this->normalizeEventKey($eventKey);
        $severity = $this->normalizeSeverity($severity);
        $now ??= time();
        $limits = self::LIMITS[$severity];
        $errors = [];

        if ($recentEventCount < 0) {
            $errors[] = 'invalid_recent_event_count';
        } elseif ($recentEventCount >= $limits['maximum_per_window']) {
            $errors[] = 'rate_limit_exceeded';
        }

        if ($lastSentAt !== null && $lastSentAt > $now) {
            $errors[] = 'last_sent_timestamp_in_future';
        } elseif (
            $lastSentAt !== null &&
            ($now - $lastSentAt) < $limits['minimum_interval']
        ) {
            $errors[] = 'minimum_interval_not_elapsed';
        }

        $emailThreshold = strtolower(trim(
            (string) (getenv('NIVAD_ADMIN_EMAIL_MIN_SEVERITY') ?: 'warning')
        ));
        if (!isset(self::SEVERITY_RANK[$emailThreshold])) {
            $emailThreshold = 'warning';
        }

        return [
            'allowed' => $errors === [],
            'event_key' => $eventKey,
            'severity' => $severity,
            'priority' => self::SEVERITY_RANK[$severity],
            'should_email' => self::SEVERITY_RANK[$severity] >= self::SEVERITY_RANK[$emailThreshold],
            'dedupe_seconds' => $limits['dedupe_seconds'],
            'maximum_per_window' => $limits['maximum_per_window'],
            'window_seconds' => 900,
            'errors' => $errors,
        ];
    }

    public function deduplicationKey(
        string $eventKey,
        ?string $entityType,
        int|string|null $entityId,
        array $recipientAdminIds
    ): string {
        $eventKey = $this->normalizeEventKey($eventKey);
        $recipients = array_values(array_unique(array_filter(
            array_map('intval', $recipientAdminIds),
            static fn(int $id): bool => $id > 0
        )));
        sort($recipients, SORT_NUMERIC);

        $identity = implode('|', [
            $eventKey,
            strtolower(trim((string) $entityType)),
            $entityId === null ? '' : (string) $entityId,
            implode(',', $recipients),
        ]);

        return hash('sha256', $identity);
    }
}
