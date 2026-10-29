<?php

declare(strict_types=1);

/**
 * Structural validation for saved filters/sorts. Consumers must still map
 * field identifiers to an explicit per-module allowlist before building SQL.
 */
final class AdminSavedViewValidator
{
    private const OPERATORS = [
        'eq', 'neq', 'contains', 'starts_with', 'ends_with',
        'gt', 'gte', 'lt', 'lte', 'in', 'is_null', 'is_not_null',
    ];

    public function validateFilters(array $filters): array
    {
        if (count($filters) > 50) {
            throw new InvalidArgumentException('At most 50 saved filters are allowed.');
        }

        $normalized = [];
        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                throw new InvalidArgumentException('Each filter must be an object.');
            }

            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? null;

            if (!is_string($field) || !$this->isSafeIdentifier($field)) {
                throw new InvalidArgumentException('Saved filter field is invalid.');
            }

            if (!is_string($operator) || !in_array($operator, self::OPERATORS, true)) {
                throw new InvalidArgumentException('Saved filter operator is unsupported.');
            }

            $item = [
                'field' => $field,
                'operator' => $operator,
            ];

            if (in_array($operator, ['is_null', 'is_not_null'], true)) {
                if (array_key_exists('value', $filter)) {
                    throw new InvalidArgumentException('Null-check operators must not include a value.');
                }
            } else {
                if (!array_key_exists('value', $filter)) {
                    throw new InvalidArgumentException('Saved filter value is required.');
                }

                $item['value'] = $this->validateValue($filter['value'], $operator);
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    public function validateSort(?array $sort): ?array
    {
        if ($sort === null) {
            return null;
        }

        if (count($sort) > 5) {
            throw new InvalidArgumentException('At most 5 sort fields are allowed.');
        }

        $normalized = [];
        foreach ($sort as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Each sort item must be an object.');
            }

            $field = $item['field'] ?? null;
            $direction = strtolower(trim((string) ($item['direction'] ?? 'asc')));

            if (!is_string($field) || !$this->isSafeIdentifier($field)) {
                throw new InvalidArgumentException('Saved sort field is invalid.');
            }

            if (!in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Saved sort direction must be asc or desc.');
            }

            $normalized[] = [
                'field' => $field,
                'direction' => $direction,
            ];
        }

        return $normalized;
    }

    private function validateValue(mixed $value, string $operator): mixed
    {
        if ($operator === 'in') {
            if (!is_array($value) || $value === [] || count($value) > 50) {
                throw new InvalidArgumentException('The IN operator requires 1–50 values.');
            }

            foreach ($value as $item) {
                if (!is_scalar($item) && $item !== null) {
                    throw new InvalidArgumentException('Filter values must be scalar.');
                }
            }

            return array_values($value);
        }

        if (!is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException('Filter values must be scalar.');
        }

        if (is_string($value) && mb_strlen($value) > 500) {
            throw new InvalidArgumentException('Saved filter value is too long.');
        }

        return $value;
    }

    private function isSafeIdentifier(string $identifier): bool
    {
        return preg_match('/^[A-Za-z][A-Za-z0-9_.]{0,99}$/', $identifier) === 1
            && !str_contains($identifier, '..');
    }
}
