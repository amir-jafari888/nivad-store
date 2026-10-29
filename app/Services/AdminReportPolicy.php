<?php

/** Non-DB report/filter contract; intentionally contains no SQL or persistence. */
final class AdminReportPolicy
{
    public static function normalizeRange(array $input): array
    {
        $from = trim((string)($input['from'] ?? ''));
        $to = trim((string)($input['to'] ?? ''));
        if ($from === '' || $to === '') {
            throw new InvalidArgumentException('Report date range is required.');
        }
        $fromTs = strtotime($from);
        $toTs = strtotime($to);
        if ($fromTs === false || $toTs === false || $fromTs > $toTs) {
            throw new InvalidArgumentException('Report date range is invalid.');
        }
        if (($toTs - $fromTs) > 366 * 86400) {
            throw new InvalidArgumentException('Report range cannot exceed 366 days.');
        }
        return ['from' => date('Y-m-d H:i:s', $fromTs), 'to' => date('Y-m-d H:i:s', $toTs)];
    }

    public static function normalizeGroup(string $group): string
    {
        $allowed = ['day', 'week', 'month'];
        if (!in_array($group, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported report grouping.');
        }
        return $group;
    }
}
