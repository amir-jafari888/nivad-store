<?php

/** Non-DB incident lifecycle contract. */
final class AdminIncidentPolicy
{
    public static function normalize(array $input): array
    {
        $title = trim((string)($input['title'] ?? ''));
        $severity = trim((string)($input['severity'] ?? 'medium'));
        $description = trim((string)($input['description'] ?? ''));
        if ($title === '' || mb_strlen($title) > 180 || $description === '' || mb_strlen($description) > 5000) {
            throw new InvalidArgumentException('Incident title or description is invalid.');
        }
        if (!in_array($severity, ['low', 'medium', 'high', 'critical'], true)) {
            throw new InvalidArgumentException('Unsupported incident severity.');
        }
        return ['title' => $title, 'severity' => $severity, 'description' => $description];
    }

    public static function assertTransition(string $from, string $to): void
    {
        $states = ['open', 'investigating', 'mitigated', 'resolved', 'closed'];
        if (!in_array($from, $states, true) || !in_array($to, $states, true)) {
            throw new InvalidArgumentException('Unsupported incident status.');
        }
        $allowed = [
            'open' => ['investigating', 'closed'],
            'investigating' => ['mitigated', 'resolved'],
            'mitigated' => ['investigating', 'resolved'],
            'resolved' => ['closed', 'investigating'],
            'closed' => [],
        ];
        if (!in_array($to, $allowed[$from], true)) {
            throw new InvalidArgumentException('Invalid incident status transition.');
        }
    }
}
