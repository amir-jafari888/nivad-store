<?php

/** Non-DB content revision/publishing contract. */
final class AdminContentRevisionPolicy
{
    public static function normalize(array $input): array
    {
        $entityType = trim((string)($input['entity_type'] ?? ''));
        $entityId = (int)($input['entity_id'] ?? 0);
        $status = trim((string)($input['status'] ?? 'draft'));
        $payload = $input['payload'] ?? null;

        if ($entityType === '' || !preg_match('/^[a-z][a-z0-9._-]{1,79}$/', $entityType) || $entityId <= 0) {
            throw new InvalidArgumentException('Content revision target is invalid.');
        }
        if (!in_array($status, ['draft', 'scheduled', 'published', 'archived'], true)) {
            throw new InvalidArgumentException('Unsupported content revision status.');
        }
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Content revision payload must be an array.');
        }
        return ['entity_type' => $entityType, 'entity_id' => $entityId, 'status' => $status, 'payload' => $payload];
    }

    public static function assertTransition(string $from, string $to): void
    {
        $allowed = [
            'draft' => ['draft', 'scheduled', 'published', 'archived'],
            'scheduled' => ['scheduled', 'published', 'draft', 'archived'],
            'published' => ['published', 'archived'],
            'archived' => ['archived', 'draft'],
        ];
        if (!isset($allowed[$from]) || !in_array($to, $allowed[$from], true)) {
            throw new InvalidArgumentException('Invalid content publishing transition.');
        }
    }
}
