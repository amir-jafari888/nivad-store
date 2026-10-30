<?php

declare(strict_types=1);

/**
 * PDO adapter for the admin email outbox.
 * Claims rows inside a transaction and changes them to processing before
 * releasing locks, preventing two workers from sending the same row.
 */
final class PdoAdminEmailOutboxRepository implements AdminEmailOutboxRepositoryInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function claimDue(int $limit): array
    {
        $limit = max(1, min(100, $limit));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                'SELECT id, recipient_email, subject, body_text, attempts
                 FROM admin_email_outbox
                 WHERE status IN ("pending", "retry")
                   AND (next_attempt_at IS NULL OR next_attempt_at <= CURRENT_TIMESTAMP)
                 ORDER BY id ASC
                 LIMIT ' . $limit . ' FOR UPDATE'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows !== []) {
                $ids = array_map(static fn(array $row): int => (int)$row['id'], $rows);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $update = $this->db->prepare(
                    'UPDATE admin_email_outbox SET status = "processing"
                     WHERE id IN (' . $placeholders . ') AND status IN ("pending", "retry")'
                );
                $update->execute($ids);
                if ($update->rowCount() !== count($ids)) {
                    throw new RuntimeException('Outbox claim count changed during transaction.');
                }
            }
            $this->db->commit();
            return $rows;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function markSent(int $outboxId, int $attemptCount, ?string $providerMessageId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_email_outbox
             SET status = "sent", attempts = :attempts, sent_at = CURRENT_TIMESTAMP,
                 next_attempt_at = NULL, last_error = NULL
             WHERE id = :id AND status = "processing"'
        );
        $stmt->execute(['id' => $outboxId, 'attempts' => $attemptCount]);
        return $stmt->rowCount() === 1;
    }

    public function markFailed(
        int $outboxId,
        int $attemptCount,
        string $errorCode,
        ?DateTimeImmutable $nextAttemptAt,
        bool $terminal
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE admin_email_outbox
             SET status = :status, attempts = :attempts, last_error = :error,
                 next_attempt_at = :next_attempt
             WHERE id = :id AND status = "processing"'
        );
        $stmt->execute([
            'id' => $outboxId,
            'status' => $terminal ? 'failed' : 'pending',
            'attempts' => $attemptCount,
            'error' => substr($errorCode, 0, 1000),
            'next_attempt' => $nextAttemptAt?->format('Y-m-d H:i:s')
        ]);
        return $stmt->rowCount() === 1;
    }
}
