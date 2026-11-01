<?php

declare(strict_types=1);

final class AdminSecurityOperationsController extends Controller
{
    private AdminSecurityOperationsService $service;

    public function __construct(?AdminSecurityOperationsService $service = null)
    {
        $this->service = $service ?? new AdminSecurityOperationsService();
    }

    private function actorId(): int
    {
        $id = AdminAuth::id();
        if (!is_int($id) || $id < 1) $this->error('نشست مدیر معتبر نیست.', 401);
        return $id;
    }

    public function index(): never
    {
        $this->actorId();
        $status = trim((string)Request::query('status', ''));
        if ($status !== '' && !in_array($status, ['open', 'reviewed', 'resolved'], true)) {
            $this->error('وضعیت رویداد امنیتی نامعتبر است.', 422);
        }
        $this->success(['items' => $this->service->recent(100, $status)], 'رویدادهای امنیتی دریافت شدند.');
    }

    public function scan(): never
    {
        $this->actorId();
        try {
            $this->success($this->service->scan(), 'اسکن سیگنال‌های امنیتی انجام شد.');
        } catch (PDOException $exception) {
            Logger::error('Security operations scan failed.', ['exception_type' => get_class($exception)]);
            $this->error('اسکن امنیتی به دلیل خطای وابستگی دیتابیس انجام نشد.', 503);
        }
    }

    public function review(array $parameters = []): never
    {
        $actorId = $this->actorId();
        $data = Request::isJson() ? Request::json() : $_POST;
        try {
            $updated = $this->service->review((int)($parameters['id'] ?? 0), $actorId, (string)($data['note'] ?? ''));
            if (!$updated) $this->error('رویداد امنیتی پیدا نشد یا قبلاً تغییر کرده است.', 404);
            AdminAuditTrail::record($actorId, 'review_security_event', 'admin_security_events', 'security_event', (int)$parameters['id'], 'Security event reviewed.', null, ['note' => trim((string)($data['note'] ?? ''))]);
            $this->success(['reviewed' => true], 'رویداد برای بررسی ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
