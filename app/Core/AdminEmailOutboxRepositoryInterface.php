<?php

declare(strict_types=1);

/**
 * Contract for an atomic, DB-backed outbox adapter.
 * claimDue() must claim rows atomically (e.g. transaction + row lock) before
 * returning them, so concurrent workers cannot send the same message.
 */
interface AdminEmailOutboxRepositoryInterface
{
    /** @return array<int,array<string,mixed>> */
    public function claimDue(int $limit): array;

    public function markSent(int $outboxId, ?string $providerMessageId): bool;

    public function markFailed(
        int $outboxId,
        int $attemptCount,
        string $errorCode,
        ?DateTimeImmutable $nextAttemptAt,
        bool $terminal
    ): bool;
}
