<?php

declare(strict_types=1);

/**
 * Pure publishing state/schedule policy. The persistence layer must store
 * normalized schedule values as UTC; applying/testing the DB session timezone
 * remains a database gate.
 */
final class AdminContentPublishingPolicy
{
    private const TRANSITIONS = [
        'draft' => ['draft', 'scheduled', 'published', 'archived'],
        'scheduled' => ['draft', 'scheduled', 'published', 'archived'],
        'published' => ['draft', 'archived'],
        'archived' => ['draft'],
    ];

    public function normalizeSchedule(
        string $status,
        ?string $scheduledAt,
        ?int $now = null
    ): ?string {
        $status = strtolower(trim($status));
        if (!isset(self::TRANSITIONS[$status])) {
            throw new InvalidArgumentException('Unsupported content revision status.');
        }

        if ($status !== 'scheduled') {
            if ($scheduledAt !== null && trim($scheduledAt) !== '') {
                throw new InvalidArgumentException('Only scheduled revisions may have a scheduled time.');
            }

            return null;
        }

        if ($scheduledAt === null || trim($scheduledAt) === '') {
            throw new InvalidArgumentException('Scheduled revisions require a publish time.');
        }

        $scheduledAt = trim($scheduledAt);
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $scheduledAt) !== 1) {
            throw new InvalidArgumentException('Scheduled time must be ISO 8601 with an explicit timezone.');
        }

        try {
            $date = new DateTimeImmutable($scheduledAt);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException('Scheduled time is invalid.', 0, $exception);
        }

        if ($date->getTimestamp() <= ($now ?? time())) {
            throw new InvalidArgumentException('Scheduled time must be in the future.');
        }

        return $date
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');
    }

    public function canTransition(string $from, string $to): bool
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        return isset(self::TRANSITIONS[$from])
            && in_array($to, self::TRANSITIONS[$from], true);
    }

    public function assertTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new InvalidArgumentException('Content publishing state transition is not allowed.');
        }
    }
}
