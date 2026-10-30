<?php

declare(strict_types=1);

/**
 * Bounded outbox processor. The DB adapter must atomically claim rows; this
 * service never connects to a database directly.
 */
final class AdminEmailOutboxWorker
{
    private const MAX_ATTEMPTS = 5;
    private const BASE_BACKOFF_SECONDS = 60;
    private const MAX_BACKOFF_SECONDS = 21600;

    private AdminEmailOutboxRepositoryInterface $repository;
    private \Closure $send;
    private \Closure $clock;

    public function __construct(
        AdminEmailOutboxRepositoryInterface $repository,
        ?callable $send = null,
        ?callable $clock = null
    ) {
        $this->repository = $repository;
        $transport = new GmailSmtpTransport();
        $this->send = $send !== null
            ? \Closure::fromCallable($send)
            : static fn(string $email, string $subject, string $body, ?string $name = null): array =>
                $transport->send($email, $subject, $body, $name);
        $this->clock = $clock !== null
            ? \Closure::fromCallable($clock)
            : static fn(): int => time();
    }

    /**
     * @return array{claimed:int,sent:int,retry_scheduled:int,failed:int,invalid_rows:int}
     */
    public function processBatch(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $rows = $this->repository->claimDue($limit);
        $summary = [
            'claimed' => count($rows),
            'sent' => 0,
            'retry_scheduled' => 0,
            'failed' => 0,
            'invalid_rows' => 0,
        ];

        foreach ($rows as $row) {
            if (!$this->isValidRow($row)) {
                $summary['invalid_rows']++;

                $candidateId = $row['id'] ?? null;
                if (
                    (is_int($candidateId) || (is_string($candidateId) && ctype_digit($candidateId))) &&
                    (int) $candidateId > 0
                ) {
                    $attempt = max(0, (int) ($row['attempts'] ?? 0)) + 1;
                    $this->repository->markFailed(
                        (int) $candidateId,
                        $attempt,
                        'invalid_message',
                        null,
                        true
                    );
                    $summary['failed']++;
                }

                continue;
            }

            $outboxId = (int) $row['id'];
            $attempt = max(0, (int) ($row['attempts'] ?? 0)) + 1;
            try {
                $result = ($this->send)(
                    (string) $row['recipient_email'],
                    (string) $row['subject'],
                    (string) $row['body_text'],
                    isset($row['recipient_name']) && is_string($row['recipient_name'])
                        ? $row['recipient_name']
                        : null
                );
                if (!is_array($result)) {
                    $result = ['success' => false, 'error_code' => 'smtp_delivery_failed'];
                }
            } catch (Throwable $exception) {
                // A failed provider adapter must not abort the rest of the batch
                // or leave this claimed row silently unprocessed.
                if (class_exists(AdminIncidentCaptureService::class)) {
                    AdminIncidentCaptureService::captureExternalFailure('admin_email_smtp', $exception, [
                        'outbox_id' => $outboxId,
                        'attempt' => $attempt,
                    ]);
                }
                $result = ['success' => false, 'error_code' => 'smtp_delivery_failed'];
            }

            if (($result['success'] ?? false) === true) {
                if ($this->repository->markSent($outboxId, $attempt, $result['message_id'] ?? null)) {
                    $summary['sent']++;
                } else {
                    $summary['failed']++;
                }
                continue;
            }

            $errorCode = is_string($result['error_code'] ?? null)
                ? $result['error_code']
                : 'smtp_delivery_failed';
            if (class_exists(AdminIncidentCaptureService::class)) {
                AdminIncidentCaptureService::capture(
                    'external_service.smtp',
                    'warning',
                    'SMTP delivery attempt failed.',
                    ['outbox_id' => $outboxId, 'attempt' => $attempt, 'error_code' => $errorCode],
                    'external_service.failure',
                    'An email delivery attempt failed and was handled by the outbox retry policy.'
                );
            }
            $terminal = $this->isPermanentFailure($errorCode) || $attempt >= self::MAX_ATTEMPTS;
            $nextAttempt = $terminal
                ? null
                : (new DateTimeImmutable('@' . (($this->clock)() + $this->backoffSeconds($attempt))))
                    ->setTimezone(new DateTimeZone('UTC'));

            $saved = $this->repository->markFailed(
                $outboxId,
                $attempt,
                $errorCode,
                $nextAttempt,
                $terminal
            );

            if (!$saved) {
                $summary['failed']++;
            } elseif ($terminal) {
                $summary['failed']++;
            } else {
                $summary['retry_scheduled']++;
            }
        }

        return $summary;
    }

    private function isValidRow(array $row): bool
    {
        return isset($row['id'], $row['recipient_email'], $row['subject'], $row['body_text'])
            && (is_int($row['id']) || (is_string($row['id']) && ctype_digit($row['id'])))
            && (int) $row['id'] > 0
            && is_string($row['recipient_email'])
            && is_string($row['subject'])
            && is_string($row['body_text']);
    }

    private function isPermanentFailure(string $errorCode): bool
    {
        return in_array($errorCode, [
            'invalid_recipient',
            'invalid_header',
            'invalid_message',
        ], true);
    }

    private function backoffSeconds(int $attempt): int
    {
        $exponent = max(0, min(20, $attempt - 1));
        return min(
            self::MAX_BACKOFF_SECONDS,
            self::BASE_BACKOFF_SECONDS * (2 ** $exponent)
        );
    }
}
