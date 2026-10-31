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
        $recipient = array_key_exists('recipient_admin_user_id', $input)
            ? $input['recipient_admin_user_id']
            : $actorAdminId;
        if ($title === '' || mb_strlen($title) > 255 || $message === '' || mb_strlen($message) > 5000) {
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

    public function countIncidentsByStatus(string $status): int
    {
        if (!in_array($status, self::INCIDENT_STATUSES, true)) {
            throw new InvalidArgumentException('وضعیت رخداد نامعتبر است.');
        }
        return $this->repository->countIncidentsByStatus($status);
    }

    public function countTicketsByStatuses(array $statuses): int
    {
        foreach ($statuses as $status) {
            if (!is_string($status) || !in_array($status, self::TICKET_STATUSES, true)) {
                throw new InvalidArgumentException('وضعیت تیکت نامعتبر است.');
            }
        }
        return $this->repository->countTicketsByStatuses($statuses);
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
        $safeContext = SensitiveDataRedactor::redact($context);
        $contextJson = $safeContext !== []
            ? json_encode($safeContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            : null;
        $fingerprintMaterial = implode('|', [
            strtolower(trim($source)),
            strtolower(trim($title)),
            strtolower((string) ($safeContext['route_path'] ?? $safeContext['page'] ?? '')),
            strtolower((string) ($safeContext['exception_type'] ?? '')),
            strtolower((string) ($safeContext['file'] ?? '')),
            (string) ($safeContext['line'] ?? ''),
            strtolower((string) ($safeContext['event_type'] ?? '')),
        ]);
        $fingerprint = hash('sha256', $fingerprintMaterial);
        $incidentId = $this->repository->recordIncident([
            'fingerprint' => $fingerprint,
            'source' => mb_substr(trim($source), 0, 100),
            'severity' => $severity,
            'title' => mb_substr(trim($title), 0, 255),
            'description' => trim($description) !== '' ? mb_substr($description, 0, 5000) : null,
            'context_json' => $contextJson,
        ]);
        if ($severity === 'critical') {
            try {
                $this->repository->notifyIncident($incidentId, trim($title), trim($description));
            } catch (Throwable $notificationFailure) {
                Logger::error('Incident was recorded but Admin notification delivery failed.', [
                    'incident_id' => $incidentId,
                    'exception_type' => get_class($notificationFailure),
                ]);
            }
        }
        return $incidentId;
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

    public function customerTickets(int $userId): array
    {
        if ($userId < 1) throw new InvalidArgumentException('نشست مشتری معتبر نیست.');
        return $this->repository->customerTickets($userId);
    }

    public function customerTicket(int $ticketId, int $userId): ?array
    {
        if ($ticketId < 1 || $userId < 1) return null;
        return $this->repository->customerTicket($ticketId, $userId);
    }

    public function createCustomerTicket(int $userId, array $input): int
    {
        $subject = trim((string)($input['subject'] ?? ''));
        $message = trim((string)($input['message'] ?? ''));
        $category = trim((string)($input['category'] ?? 'general')) ?: 'general';
        $priority = (string)($input['priority'] ?? 'normal');
        if ($userId < 1 || $subject === '' || mb_strlen($subject) > 255 || $message === '' || mb_strlen($message) > 20000) {
            throw new InvalidArgumentException('موضوع و متن تیکت الزامی و باید در محدوده مجاز باشند.');
        }
        if (!preg_match('/^[a-z][a-z0-9_-]{1,79}$/', $category)) {
            throw new InvalidArgumentException('دسته‌بندی تیکت نامعتبر است.');
        }
        if (!in_array($priority, ['low', 'normal', 'high'], true)) {
            throw new InvalidArgumentException('اولویت تیکت نامعتبر است.');
        }
        $ticketId = $this->repository->createCustomerTicket($userId, [
            'category' => $category,
            'priority' => $priority,
            'subject' => $subject,
            'message' => $message,
        ]);
        try {
            $ticket = $this->repository->customerTicket($ticketId, $userId);
            $this->repository->notifyTicketCreated(
                $ticketId,
                (string)($ticket['ticket_number'] ?? $ticketId),
                $subject
            );
        } catch (Throwable $notificationFailure) {
            Logger::error('Customer support ticket was created but Admin notification failed.', [
                'ticket_id' => $ticketId,
                'exception_type' => get_class($notificationFailure),
            ]);
        }
        return $ticketId;
    }

    public function addCustomerTicketMessage(int $ticketId, int $userId, string $message): int
    {
        $message = trim($message);
        if ($ticketId < 1 || $userId < 1 || $message === '' || mb_strlen($message) > 20000) {
            throw new InvalidArgumentException('متن پیام تیکت نامعتبر است.');
        }
        return $this->repository->addCustomerTicketMessage($ticketId, $userId, $message);
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
        if ($subject === '' || mb_strlen($subject) > 255 || $message === '' || mb_strlen($message) > 20000) {
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
        if ($ticketId < 1 || $message === '' || mb_strlen($message) > 20000) {
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

    public function recordProductEvent(array $input): int
    {
        $event = trim((string)($input['event_name'] ?? ''));
        $allowedEvents = [
            'product_view', 'variant_select', 'add_to_cart', 'wishlist_add',
            'search_impression', 'search_click',
            'checkout_started', 'checkout_address_selected', 'checkout_shipping_selected', 'checkout_order_created',
            'related_product_impression', 'related_product_click',
            'discovery_impression', 'discovery_click', 'discovery_select', 'discovery_option_impression',
            'comparison_add', 'comparison_remove', 'comparison_view',
        ];
        if (!in_array($event, $allowedEvents, true)) {
            throw new InvalidArgumentException('نوع رویداد تحلیلی مجاز نیست.');
        }

        $productId = isset($input['product_id']) && (int)$input['product_id'] > 0
            ? (int)$input['product_id']
            : null;
        $variantId = isset($input['variant_id']) && (int)$input['variant_id'] > 0
            ? (int)$input['variant_id']
            : null;

        if (in_array($event, ['product_view', 'variant_select', 'add_to_cart', 'wishlist_add', 'related_product_impression', 'related_product_click', 'discovery_impression', 'discovery_click', 'comparison_add', 'comparison_remove'], true) && $productId === null) {
            throw new InvalidArgumentException('شناسه محصول برای این رویداد الزامی است.');
        }
        if ($productId !== null && !$this->repository->productExists($productId)) {
            throw new InvalidArgumentException('محصول فعال پیدا نشد.');
        }
        if ($variantId !== null && ($productId === null || !$this->repository->variantBelongsToProduct($variantId, $productId))) {
            throw new InvalidArgumentException('مدل انتخاب‌شده متعلق به این محصول نیست.');
        }

        $searchTerm = trim((string)($input['search_term'] ?? ''));
        if (in_array($event, ['search_impression', 'search_click'], true) && ($searchTerm === '' || mb_strlen($searchTerm) > 200)) {
            throw new InvalidArgumentException('عبارت جستجو نامعتبر است.');
        }
        $normalizedSearchTerm = str_replace(
            ['ي', 'ى', 'ك'],
            ['ی', 'ی', 'ک'],
            mb_strtolower($searchTerm, 'UTF-8')
        );
        $searchTermHash = $searchTerm !== ''
            ? hash('sha256', $normalizedSearchTerm)
            : null;

        $entityType = match (true) {
            str_starts_with($event, 'search_') => 'search',
            str_starts_with($event, 'checkout_') => 'checkout',
            str_starts_with($event, 'discovery_') => 'discovery',
            str_starts_with($event, 'comparison_') => 'comparison',
            str_starts_with($event, 'related_product_') => 'product',
            $productId !== null => 'product',
            default => null,
        };

        $entityId = $productId !== null
            ? (string)$productId
            : (isset($input['entity_id']) ? trim((string)$input['entity_id']) : null);
        if ($entityId === '') $entityId = null;
        if ($entityId !== null && mb_strlen($entityId) > 100) {
            throw new InvalidArgumentException('شناسه موجودیت تحلیلی بیش از حد مجاز است.');
        }
        if ($entityType === 'search') $entityId = $searchTermHash;

        Session::start();
        $sessionId = Session::id();
        $now = time();
        $rate = Session::get('_product_analytics_rate', []);
        if (!is_array($rate)) $rate = [];
        $rate = array_values(array_filter(
            $rate,
            static fn($timestamp): bool => is_int($timestamp) && $timestamp > ($now - 60)
        ));
        if (count($rate) >= 120) {
            throw new InvalidArgumentException('تعداد رویدادهای ثبت‌شده در این نشست بیش از حد مجاز است.');
        }
        $rate[] = $now;
        Session::set('_product_analytics_rate', $rate);

        $visitorId = trim((string)($input['visitor_id'] ?? ''));
        if ($visitorId !== '' && !preg_match('/^[A-Za-z0-9_-]{16,128}$/', $visitorId)) {
            throw new InvalidArgumentException('شناسه ناشناس بازدیدکننده معتبر نیست.');
        }
        $visitorHash = $visitorId !== ''
            ? hash('sha256', 'visitor|' . $visitorId)
            : hash('sha256', 'visitor-session|' . $sessionId . '|' . Request::userAgent());
        $sessionHash = hash('sha256', 'session|' . $sessionId);
        Session::set('_analytics_visitor_hash', $visitorHash);
        $customerId = Auth::id();
        $customerHash = $customerId !== null
            ? hash('sha256', 'customer|' . (string)$customerId)
            : null;

        $source = trim((string)($input['source'] ?? 'storefront'));
        if ($source === '' || !preg_match('/^[a-z0-9_-]{1,60}$/i', $source)) {
            $source = 'storefront';
        }
        $step = trim((string)($input['step'] ?? ''));
        $allowedSteps = ['start', 'address', 'shipping', 'review', 'order_created'];
        if ($step !== '' && !in_array($step, $allowedSteps, true)) $step = '';

        $resultCount = isset($input['result_count']) && is_numeric($input['result_count'])
            ? max(0, min(1000, (int)$input['result_count']))
            : null;

        $dimensions = [
            'visitor_hash' => $visitorHash,
            'session_hash' => $sessionHash,
            'customer_hash' => $customerHash,
            'search_term_hash' => $searchTermHash,
            'variant_id' => $variantId,
            'source' => $source,
            'step' => $step !== '' ? $step : null,
            'result_count' => $resultCount,
            'client_event_id' => isset($input['client_event_id'])
                && preg_match('/^[A-Za-z0-9_-]{12,80}$/', (string)$input['client_event_id'])
                    ? (string)$input['client_event_id']
                    : null,
        ];

        return $this->repository->recordProductEvent([
            'event_key' => $event,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'numeric_value' => $resultCount !== null ? (float)$resultCount : null,
            'dimensions' => $dimensions,
        ]);
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
            'product' => ['permission' => 'admin.products.index', 'href' => url('/admin/products')],
            'order' => ['permission' => 'admin.orders.index', 'href' => url('/admin/orders')],
            'customer' => ['permission' => 'admin.users.index', 'href' => url('/admin/users')],
            'ticket' => ['permission' => 'admin.capabilities.tickets.index', 'href' => url('/admin/capabilities/index.php#admin-tickets-title')],
        ];
        $adminId = (new AdminAuthService())->requireAdminId();
        $visible = [];
        foreach ($items as $item) {
            $target = $targets[$item['type']] ?? null;
            if ($target === null || !Authorization::can($adminId, $target['permission'])) {
                continue;
            }
            $item['href'] = $target['href'];
            $visible[] = $item;
        }
        return $visible;
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
        $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (strlen($snapshotJson) > 100000) {
            throw new InvalidArgumentException('حجم Snapshot بیش از حد مجاز است.');
        }
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
