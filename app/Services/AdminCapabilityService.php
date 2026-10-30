<?php

declare(strict_types=1);

final class AdminCapabilityService
{
    private AdminCapabilityRepository $repository;

    private const NOTIFICATION_CATEGORIES = ['system', 'security', 'orders', 'inventory', 'customer_support', 'content', 'errors'];
    private const SEVERITIES = ['info', 'warning', 'critical'];
    private const INCIDENT_STATUSES = ['open', 'acknowledged', 'resolved', 'ignored'];
    private const TICKET_STATUSES = ['open', 'pending_customer', 'pending_admin', 'resolved', 'closed'];
    private const TICKET_PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function __construct(?AdminCapabilityRepository $repository = null)
    {
        $this->repository = $repository ?? new AdminCapabilityRepository();
    }

    public function notifications(int $adminId, int $limit = 50, bool $unreadOnly = false): array
    {
        return [
            'items' => $this->repository->notifications($adminId, $limit, $unreadOnly),
            'unread_count' => $this->repository->unreadNotificationCount($adminId),
        ];
    }

    public function markNotificationRead(int $id, int $adminId): bool
    {
        if ($id < 1 || $adminId < 1) {
            throw new InvalidArgumentException('شناسه اعلان نامعتبر است.');
        }
        return $this->repository->markNotificationRead($id, $adminId);
    }

    public function createNotification(int $actorAdminId, array $input): int
    {
        $title = trim((string)($input['title'] ?? ''));
        $message = trim((string)($input['message'] ?? ''));
        $category = (string)($input['category'] ?? 'system');
        $severity = (string)($input['severity'] ?? 'info');
        $recipient = $input['recipient_admin_user_id'] ?? $actorAdminId;
        if ($title === '' || mb_strlen($title) > 255 || $message === '') {
            throw new InvalidArgumentException('عنوان و متن اعلان الزامی است.');
        }
        if (!in_array($category, self::NOTIFICATION_CATEGORIES, true) || !in_array($severity, self::SEVERITIES, true)) {
            throw new InvalidArgumentException('دسته‌بندی یا شدت اعلان نامعتبر است.');
        }
        if ($recipient !== null && (!filter_var($recipient, FILTER_VALIDATE_INT) || (int)$recipient < 1)) {
            throw new InvalidArgumentException('گیرنده اعلان نامعتبر است.');
        }
        if ($recipient !== null && !$this->repository->adminExists((int)$recipient)) {
            throw new InvalidArgumentException('مدیر گیرنده فعال و معتبر نیست.');
        }
        $dedupe = trim((string)($input['dedupe_key'] ?? ''));
        return $this->repository->createNotification([
            'recipient_admin_user_id' => $recipient !== null ? (int)$recipient : null,
            'category' => $category,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'source_type' => trim((string)($input['source_type'] ?? '')) ?: null,
            'source_id' => isset($input['source_id']) && (int)$input['source_id'] > 0 ? (int)$input['source_id'] : null,
            'dedupe_key' => $dedupe !== '' ? hash('sha256', $dedupe) : null,
            'expires_at' => $this->nullableDate($input['expires_at'] ?? null),
        ]);
    }

    public function incidents(string $status = '', int $limit = 100): array
    {
        if ($status !== '' && !in_array($status, self::INCIDENT_STATUSES, true)) {
            throw new InvalidArgumentException('وضعیت رخداد نامعتبر است.');
        }
        return $this->repository->incidents($status, $limit);
    }

    public function recordIncident(string $source, string $severity, string $title, string $description = '', array $context = []): int
    {
        if (!in_array($severity, self::SEVERITIES, true) || trim($source) === '' || trim($title) === '') {
            throw new InvalidArgumentException('اطلاعات رخداد نامعتبر است.');
        }
        $contextJson = $context !== []
            ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            : null;
        $fingerprint = hash('sha256', strtolower(trim($source)) . '|' . strtolower(trim($title)));
        return $this->repository->recordIncident([
            'fingerprint' => $fingerprint,
            'source' => mb_substr(trim($source), 0, 100),
            'severity' => $severity,
            'title' => mb_substr(trim($title), 0, 255),
            'description' => trim($description) !== '' ? $description : null,
            'context_json' => $contextJson,
        ]);
    }

    public function updateIncident(int $id, string $status, ?int $assignedAdminId, int $actorAdminId): bool
    {
        if ($id < 1 || !in_array($status, self::INCIDENT_STATUSES, true)) {
            throw new InvalidArgumentException('اطلاعات تغییر رخداد نامعتبر است.');
        }
        if ($assignedAdminId !== null && !$this->repository->adminExists($assignedAdminId)) {
            throw new InvalidArgumentException('مدیر مسئول فعال و معتبر نیست.');
        }
        return $this->repository->updateIncident($id, $status, $assignedAdminId, $actorAdminId);
    }

    public function tickets(string $status = '', int $limit = 100): array
    {
        if ($status !== '' && !in_array($status, self::TICKET_STATUSES, true)) {
            throw new InvalidArgumentException('وضعیت تیکت نامعتبر است.');
        }
        return $this->repository->tickets($status, $limit);
    }

    public function ticket(int $id): ?array
    {
        return $id > 0 ? $this->repository->ticket($id) : null;
    }

    public function createTicket(int $actorAdminId, array $input): int
    {
        $subject = trim((string)($input['subject'] ?? ''));
        $message = trim((string)($input['message'] ?? ''));
        $priority = (string)($input['priority'] ?? 'normal');
        if ($subject === '' || mb_strlen($subject) > 255 || $message === '') {
            throw new InvalidArgumentException('موضوع و متن پیام تیکت الزامی است.');
        }
        if (!in_array($priority, self::TICKET_PRIORITIES, true)) {
            throw new InvalidArgumentException('اولویت تیکت نامعتبر است.');
        }
        $userId = isset($input['user_id']) && (int)$input['user_id'] > 0 ? (int)$input['user_id'] : null;
        $assigned = isset($input['assigned_admin_id']) && (int)$input['assigned_admin_id'] > 0 ? (int)$input['assigned_admin_id'] : $actorAdminId;
        if ($userId !== null && !$this->repository->customerExists($userId)) {
            throw new InvalidArgumentException('مشتری انتخاب‌شده پیدا نشد.');
        }
        if (!$this->repository->adminExists($assigned)) {
            throw new InvalidArgumentException('مدیر مسئول فعال و معتبر نیست.');
        }
        return $this->repository->createTicket([
            'user_id' => $userId,
            'category' => mb_substr(trim((string)($input['category'] ?? 'general')) ?: 'general', 0, 100),
            'priority' => $priority,
            'subject' => $subject,
            'assigned_admin_id' => $assigned,
            'actor_admin_id' => $actorAdminId,
            'message' => $message,
        ]);
    }

    public function addTicketMessage(int $ticketId, int $adminId, string $message, bool $internal): int
    {
        $message = trim($message);
        if ($ticketId < 1 || $message === '') {
            throw new InvalidArgumentException('متن پیام تیکت الزامی است.');
        }
        if ($this->repository->ticket($ticketId) === null) {
            throw new InvalidArgumentException('تیکت پیدا نشد.');
        }
        return $this->repository->addTicketMessage($ticketId, $adminId, $message, $internal);
    }

    public function updateTicket(int $id, string $status, string $priority, ?int $assignedAdminId): bool
    {
        if ($id < 1 || !in_array($status, self::TICKET_STATUSES, true) || !in_array($priority, self::TICKET_PRIORITIES, true)) {
            throw new InvalidArgumentException('وضعیت یا اولویت تیکت نامعتبر است.');
        }
        if ($assignedAdminId !== null && !$this->repository->adminExists($assignedAdminId)) {
            throw new InvalidArgumentException('مدیر مسئول فعال و معتبر نیست.');
        }
        return $this->repository->updateTicket($id, $status, $priority, $assignedAdminId);
    }

    public function analytics(string $from, string $to, int $limit = 50): array
    {
        $start = $this->date($from);
        $end = $this->date($to);
        if ($start >= $end || $end->diff($start)->days > 93) {
            throw new InvalidArgumentException('بازه گزارش باید معتبر و حداکثر ۹۳ روز باشد.');
        }
        return $this->repository->analytics($start->format('Y-m-d 00:00:00'), $end->modify('+1 day')->format('Y-m-d 00:00:00'), $limit);
    }

    public function preferences(int $adminId): array
    {
        return $this->repository->preferences($adminId);
    }

    public function savePreference(int $adminId, string $key, mixed $value): void
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]{1,99}$/', $key)) {
            throw new InvalidArgumentException('کلید تنظیمات نامعتبر است.');
        }
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (strlen($json) > 20000) {
            throw new InvalidArgumentException('حجم تنظیمات بیش از حد مجاز است.');
        }
        $this->repository->savePreference($adminId, $key, $json);
    }

    public function savedViews(int $adminId, string $module): array
    {
        $module = $this->module($module);
        return $this->repository->savedViews($adminId, $module);
    }

    public function saveView(int $adminId, array $input): int
    {
        $module = $this->module((string)($input['module'] ?? ''));
        $name = trim((string)($input['name'] ?? ''));
        $filters = $input['filters'] ?? null;
        $sort = $input['sort'] ?? null;
        if ($name === '' || mb_strlen($name) > 120 || !is_array($filters) || ($sort !== null && !is_array($sort))) {
            throw new InvalidArgumentException('نام و فیلترهای ذخیره‌شده معتبر نیستند.');
        }
        $filtersJson = json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $sortJson = $sort !== null ? json_encode($sort, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) : null;
        if (strlen($filtersJson) > 20000 || ($sortJson !== null && strlen($sortJson) > 5000)) {
            throw new InvalidArgumentException('حجم فیلترها بیش از حد مجاز است.');
        }
        return $this->repository->saveView($adminId, $module, $name, $filtersJson, $sortJson, (bool)($input['is_default'] ?? false));
    }

    public function deleteView(int $id, int $adminId): bool
    {
        return $id > 0 && $this->repository->deleteView($id, $adminId);
    }

    public function globalSearch(string $term): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2 || mb_strlen($term) > 100) {
            throw new InvalidArgumentException('عبارت جست‌وجو باید بین ۲ تا ۱۰۰ نویسه باشد.');
        }
        $items = $this->repository->globalSearch($term);
        $targets = [
            'product' => url('/admin/products'),
            'order' => url('/admin/orders'),
            'customer' => url('/admin/users'),
            'ticket' => url('/admin/capabilities/index.php#admin-tickets-title'),
        ];
        foreach ($items as &$item) {
            $item['href'] = $targets[$item['type']] ?? url('/admin');
        }
        unset($item);
        return $items;
    }

    public function revisions(string $entityType, int $entityId, int $limit = 50): array
    {
        $this->validateEntity($entityType, $entityId);
        return $this->repository->revisions($entityType, $entityId, $limit);
    }

    public function createRevision(int $adminId, array $input): int
    {
        $entityType = (string)($input['entity_type'] ?? '');
        $entityId = (int)($input['entity_id'] ?? 0);
        $snapshot = $input['snapshot'] ?? null;
        if (!is_array($snapshot)) {
            throw new InvalidArgumentException('Snapshot نسخه باید یک شیء باشد.');
        }
        $this->validateEntity($entityType, $entityId);
        $summary = trim((string)($input['change_summary'] ?? ''));
        return $this->repository->createRevision($entityType, $entityId, $adminId, $snapshot, mb_substr($summary, 0, 1000));
    }

    private function module(string $module): string
    {
        $module = trim($module);
        if (!preg_match('/^[a-z][a-z0-9_-]{1,79}$/', $module)) {
            throw new InvalidArgumentException('شناسه ماژول نامعتبر است.');
        }
        return $module;
    }

    private function validateEntity(string $type, int $id): void
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]{1,99}$/', $type) || $id < 1) {
            throw new InvalidArgumentException('شناسه موجودیت نسخه نامعتبر است.');
        }
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }
        return $this->date((string)$value)->format('Y-m-d H:i:s');
    }

    private function date(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('تاریخ باید با قالب YYYY-MM-DD ارسال شود.');
        }
        return $date;
    }
}
