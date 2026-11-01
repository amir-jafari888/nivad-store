<?php

declare(strict_types=1);

final class AdminProductivityController extends Controller
{
    public function diagnostics(): never
    {
        $adminId = AdminAuth::id();
        if ($adminId === null || $adminId < 1) $this->error('نشست مدیر معتبر نیست.', 401);
        if (!Authorization::can($adminId, 'admin.system_health.view')) $this->error('اجازه مشاهده سلامت عملیاتی را ندارید.', 403);
        $this->success((new AdminProductivityDiagnosticsService())->snapshot(), 'وضعیت قابلیت‌های عملیاتی دریافت شد.');
    }
}
