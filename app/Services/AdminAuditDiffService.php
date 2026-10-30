<?php

/** Computes safe, field-level before/after audit diffs without persistence. */
final class AdminAuditDiffService
{
    public static function diff(array $before, array $after, array $redactKeys = []): array
    {
        $redact = array_fill_keys(array_map('strtolower', $redactKeys), true);
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];

        foreach ($keys as $key) {
            $name = (string)$key;
            if (isset($redact[strtolower($name)])) {
                if (array_key_exists($key, $before) || array_key_exists($key, $after)) {
                    $diff[$name] = ['changed' => true, 'before' => '[REDACTED]', 'after' => '[REDACTED]'];
                }
                continue;
            }

            $hasBefore = array_key_exists($key, $before);
            $hasAfter = array_key_exists($key, $after);
            $old = $hasBefore ? $before[$key] : null;
            $new = $hasAfter ? $after[$key] : null;
            if (!$hasBefore || !$hasAfter || $old !== $new) {
                $diff[$name] = [
                    'changed' => true,
                    'before' => self::safeValue($old),
                    'after' => self::safeValue($new),
                ];
            }
        }

        return $diff;
    }

    private static function safeValue($value)
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[(string)$key] = self::safeValue($item);
            }
            return $result;
        }
        return '[' . get_debug_type($value) . ']';
    }
}
