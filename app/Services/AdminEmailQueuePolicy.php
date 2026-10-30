<?php

/** Non-DB email outbox/retry contract. No network or persistence is performed here. */
final class AdminEmailQueuePolicy
{
    public static function normalize(array $input): array
    {
        $recipient = strtolower(trim((string)($input['recipient'] ?? '')));
        $subject = trim((string)($input['subject'] ?? ''));
        $body = (string)($input['body'] ?? '');
        $attempt = (int)($input['attempt'] ?? 0);
        $maxAttempts = (int)($input['max_attempts'] ?? 5);

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid notification recipient.');
        }
        if ($subject === '' || strlen($subject) > 250 || $body === '' || strlen($body) > 200000) {
            throw new InvalidArgumentException('Notification subject or body is invalid.');
        }
        if ($attempt < 0 || $attempt > 100 || $maxAttempts < 1 || $maxAttempts > 20) {
            throw new InvalidArgumentException('Notification retry configuration is invalid.');
        }

        return [
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'attempt' => $attempt,
            'max_attempts' => $maxAttempts,
            'status' => $attempt >= $maxAttempts ? 'failed' : 'pending',
        ];
    }

    public static function nextDelaySeconds(int $attempt): int
    {
        if ($attempt < 0) {
            throw new InvalidArgumentException('Retry attempt cannot be negative.');
        }
        return min(3600, 30 * (2 ** min($attempt, 7)));
    }
}
