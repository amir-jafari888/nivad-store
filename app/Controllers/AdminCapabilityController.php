<?php

declare(strict_types=1);

final class AdminCapabilityController extends Controller
{
    private AdminCapabilityService $service;

    public function __construct(?AdminCapabilityService $service = null)
    {
        $this->service = $service ?? new AdminCapabilityService();
    }

    private function adminId(): int
    {
        $id = AdminAuth::id();
        if ($id === null || $id < 1) {
            $this->error('نشست مدیر معتبر نیست.', 401);
        }
        return $id;
    }

    private function payload(): array
    {
        return Request::isJson() ? Request::json() : $_POST;
    }

    private function invalid(InvalidArgumentException $exception): never
    {
        $this->error($exception->getMessage(), 422);
    }

    public function notifications(): never
    {
        try {
            $data = $this->service->notifications(
                $this->adminId(),
                (int)Request::query('limit', 50),
                (string)Request::query('unread', '') === '1'
            );
            $this->success($data, 'اعلان‌های مدیریتی دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function createNotification(): never
    {
        try {
            $id = $this->service->createNotification($this->adminId(), $this->payload());
            $this->success(['id' => $id], 'اعلان مدیریتی ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function markNotificationRead(array $parameters = []): never
    {
        try {
            $updated = $this->service->markNotificationRead((int)($parameters['id'] ?? 0), $this->adminId());
            if (!$updated) {
                $this->error('اعلان پیدا نشد یا قبلاً خوانده شده است.', 404);
            }
            $this->success(['updated' => true], 'اعلان خوانده‌شده ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function incidents(): never
    {
        try {
            $items = $this->service->incidents(
                (string)Request::query('status', ''),
                (int)Request::query('limit', 100)
            );
            $this->success(['items' => $items], 'رخدادها دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function updateIncident(array $parameters = []): never
    {
        try {
            $data = $this->payload();
            $assigned = isset($data['assigned_admin_id']) && (int)$data['assigned_admin_id'] > 0
                ? (int)$data['assigned_admin_id']
                : null;
            $updated = $this->service->updateIncident(
                (int)($parameters['id'] ?? 0),
                (string)($data['status'] ?? ''),
                $assigned,
                $this->adminId()
            );
            if (!$updated) {
                $this->error('رخداد پیدا نشد یا تغییری اعمال نشد.', 404);
            }
            $this->success(['updated' => true], 'وضعیت رخداد به‌روزرسانی شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function tickets(): never
    {
        try {
            $items = $this->service->tickets(
                (string)Request::query('status', ''),
                (int)Request::query('limit', 100)
            );
            $this->success(['items' => $items], 'تیکت‌ها دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function showTicket(array $parameters = []): never
    {
        $ticket = $this->service->ticket((int)($parameters['id'] ?? 0));
        if ($ticket === null) {
            $this->error('تیکت پیدا نشد.', 404);
        }
        $this->success(['ticket' => $ticket], 'جزئیات تیکت دریافت شد.');
    }

    public function createTicket(): never
    {
        try {
            $id = $this->service->createTicket($this->adminId(), $this->payload());
            $this->success(['id' => $id], 'تیکت ایجاد شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function addTicketMessage(array $parameters = []): never
    {
        try {
            $data = $this->payload();
            $id = $this->service->addTicketMessage(
                (int)($parameters['id'] ?? 0),
                $this->adminId(),
                (string)($data['message'] ?? ''),
                (bool)($data['is_internal'] ?? false)
            );
            $this->success(['id' => $id], 'پیام تیکت ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function updateTicket(array $parameters = []): never
    {
        try {
            $data = $this->payload();
            $assigned = isset($data['assigned_admin_id']) && (int)$data['assigned_admin_id'] > 0
                ? (int)$data['assigned_admin_id']
                : null;
            $updated = $this->service->updateTicket(
                (int)($parameters['id'] ?? 0),
                (string)($data['status'] ?? ''),
                (string)($data['priority'] ?? ''),
                $assigned
            );
            if (!$updated) {
                $this->error('تیکت پیدا نشد یا تغییری اعمال نشد.', 404);
            }
            $this->success(['updated' => true], 'تیکت به‌روزرسانی شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function analytics(): never
    {
        try {
            $from = (string)Request::query('from', date('Y-m-d', strtotime('-29 days')));
            $to = (string)Request::query('to', date('Y-m-d'));
            $items = $this->service->analytics($from, $to, (int)Request::query('limit', 50));
            $this->success(['items' => $items, 'from' => $from, 'to' => $to], 'گزارش رفتار محصولات دریافت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function preferences(): never
    {
        $this->success(['items' => $this->service->preferences($this->adminId())], 'تنظیمات مدیر دریافت شدند.');
    }

    public function savePreference(): never
    {
        try {
            $data = $this->payload();
            $this->service->savePreference(
                $this->adminId(),
                (string)($data['key'] ?? ''),
                $data['value'] ?? null
            );
            $this->success(['saved' => true], 'تنظیمات مدیر ذخیره شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function savedViews(): never
    {
        try {
            $items = $this->service->savedViews(
                $this->adminId(),
                (string)Request::query('module', '')
            );
            $this->success(['items' => $items], 'نماهای ذخیره‌شده دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function saveView(): never
    {
        try {
            $id = $this->service->saveView($this->adminId(), $this->payload());
            $this->success(['id' => $id], 'نمای ذخیره‌شده ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function deleteView(array $parameters = []): never
    {
        $deleted = $this->service->deleteView((int)($parameters['id'] ?? 0), $this->adminId());
        if (!$deleted) {
            $this->error('نمای ذخیره‌شده پیدا نشد.', 404);
        }
        $this->success(['deleted' => true], 'نمای ذخیره‌شده حذف شد.');
    }

    public function globalSearch(): never
    {
        try {
            $items = $this->service->globalSearch((string)Request::query('q', ''));
            $this->success(['items' => $items], 'نتایج جست‌وجوی سراسری دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function health(): never
    {
        $checks = [
            'php' => [
                'status' => 'ok',
                'version' => PHP_VERSION,
            ],
            'database' => [
                'status' => 'unknown',
            ],
            'timezone' => [
                'status' => 'ok',
                'value' => date_default_timezone_get(),
            ],
        ];

        try {
            $db = Database::connection();
            $db->query('SELECT 1')->fetchColumn();
            $checks['database']['status'] = 'ok';
        } catch (Throwable $exception) {
            $checks['database']['status'] = 'error';
        }

        $overall = $checks['database']['status'] === 'ok'
            ? 'healthy'
            : 'degraded';

        $this->success([
            'status' => $overall,
            'checked_at' => date(DATE_ATOM),
            'checks' => $checks,
        ], 'وضعیت پایه سرویس دریافت شد.');
    }

    public function revisions(): never
    {
        try {
            $items = $this->service->revisions(
                (string)Request::query('entity_type', ''),
                (int)Request::query('entity_id', 0),
                (int)Request::query('limit', 50)
            );
            $this->success(['items' => $items], 'تاریخچه نسخه‌ها دریافت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function createRevision(): never
    {
        try {
            $id = $this->service->createRevision($this->adminId(), $this->payload());
            $this->success(['id' => $id], 'نسخه محتوا ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }
}
