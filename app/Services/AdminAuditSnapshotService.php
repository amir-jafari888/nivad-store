<?php

declare(strict_types=1);

/**
 * Produces redacted, bounded before/after snapshots and a deterministic diff.
 * Pure PHP only: no database, filesystem, or network access.
 */
final class AdminAuditSnapshotService
{
    private const MAX_DIFF_ENTRIES = 500;

    /**
     * @return array{before:?array, after:?array, changes:array<int,array{path:string,change:string,before?:mixed,after?:mixed}>}
     */
    public function compare(?array $before, ?array $after): array
    {
        $safeBefore = $before === null ? null : SensitiveDataRedactor::redact($before);
        $safeAfter = $after === null ? null : SensitiveDataRedactor::redact($after);
        $changes = [];

        $this->walk(
            $safeBefore ?? [],
            $safeAfter ?? [],
            '',
            $changes,
            $before === null,
            $after === null
        );

        return [
            'before' => $safeBefore,
            'after' => $safeAfter,
            'changes' => array_slice($changes, 0, self::MAX_DIFF_ENTRIES),
        ];
    }

    private function walk(
        array $before,
        array $after,
        string $prefix,
        array &$changes,
        bool $beforeMissing = false,
        bool $afterMissing = false
    ): void {
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        sort($keys, SORT_STRING);

        foreach ($keys as $key) {
            if (count($changes) >= self::MAX_DIFF_ENTRIES) {
                return;
            }

            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            $hasBefore = !$beforeMissing && array_key_exists($key, $before);
            $hasAfter = !$afterMissing && array_key_exists($key, $after);

            if (!$hasBefore) {
                $changes[] = [
                    'path' => $path,
                    'change' => 'added',
                    'after' => $after[$key],
                ];
                continue;
            }

            if (!$hasAfter) {
                $changes[] = [
                    'path' => $path,
                    'change' => 'removed',
                    'before' => $before[$key],
                ];
                continue;
            }

            $oldValue = $before[$key];
            $newValue = $after[$key];

            if (is_array($oldValue) && is_array($newValue)) {
                $this->walk($oldValue, $newValue, $path, $changes);
                continue;
            }

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'path' => $path,
                    'change' => 'changed',
                    'before' => $oldValue,
                    'after' => $newValue,
                ];
            }
        }
    }
}
