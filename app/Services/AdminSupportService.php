<?php

declare(strict_types=1);

final class AdminSupportService
{
    private AdminSupportRepository $repository;
    private const STATUSES = ['open', 'pending_customer', 'pending_admin', 'resolved', 'closed'];
    private const PRIORITIES = ['urgent', 'high', 'normal', 'low'];

    public function __construct(?AdminSupportRepository $repository = null)
    {
        $this->repository = $repository ?? new AdminSupportRepository();
    }

    public function tickets(array $input): array
    {
        $filters = [];
        foreach (['status', 'priority'] as $key) {
            $value = trim((string)($input[$key] ?? ''));
            $allowed = $key === 'status' ? self::STATUSES : self::PRIORITIES;
            if ($value !== '' && !in_array($value, $allowed, true)) {
                throw new InvalidArgumentException('فیلتر وضعیت یا اولویت تیکت نامعتبر است.');
            }
            $filters[$key] = $value;
        }
        foreach (['assigned_admin_id', 'user_id'] as $key) {
            $value = trim((string)($input[$key] ?? ''));
            if ($value !== '' && (!ctype_digit($value) || (int)$value < 1)) {
                throw new InvalidArgumentException('شناسه فیلتر تیکت نامعتبر است.');
            }
            $filters[$key] = $value;
        }
        foreach (['date_from', 'date_to'] as $key) {
            $value = trim((string)($input[$key] ?? ''));
            if ($value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                throw new InvalidArgumentException('بازه زمانی فیلتر نامعتبر است.');
            }
            $filters[$key] = $value;
        }
        if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
            throw new InvalidArgumentException('ابتدای بازه نباید بعد از انتهای آن باشد.');
        }
        $filters['search'] = mb_substr(trim((string)($input['search'] ?? '')), 0, 120);
        $filters['limit'] = max(1, min(200, (int)($input['limit'] ?? 100)));
        return $this->repository->tickets($filters);
    }

    public function ticket(int $id): ?array
    {
        return $id > 0 ? $this->repository->ticket($id) : null;
    }

    public function slaPolicies(): array
    {
        return $this->repository->slaPolicies();
    }

    public function saveSlaPolicy(int $adminId, array $input): void
    {
        $priority = (string)($input['priority'] ?? '');
        $first = filter_var($input['first_response_minutes'] ?? null, FILTER_VALIDATE_INT);
        $resolution = filter_var($input['resolution_minutes'] ?? null, FILTER_VALIDATE_INT);
        if (!in_array($priority, self::PRIORITIES, true) || $first === false || $resolution === false
            || $first < 1 || $first > 525600 || $resolution < 1 || $resolution > 525600
            || $resolution < $first || $adminId < 1) {
            throw new InvalidArgumentException('مقادیر SLA معتبر نیستند.');
        }
        $this->repository->saveSlaPolicy($priority, $first, $resolution, $adminId);
    }

    public function metrics(string $from, string $to): array
    {
        $this->validateRange($from, $to);
        return $this->repository->metrics($from, $to);
    }

    public function reports(string $from, string $to): array
    {
        $this->validateRange($from, $to);
        return $this->repository->reports($from, $to);
    }

    private function validateRange(string $from, string $to): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)
            || $from > $to
            || (strtotime($to) - strtotime($from)) > 366 * 86400) {
            throw new InvalidArgumentException('بازه گزارش باید معتبر و حداکثر یک سال باشد.');
        }
    }
}
