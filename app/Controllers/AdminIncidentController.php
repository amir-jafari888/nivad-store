<?php

declare(strict_types=1);

final class AdminIncidentController extends Controller
{
    private function repository(): AdminIncidentDetectionRepository
    {
        return new AdminIncidentDetectionRepository();
    }

    public function timeline(array $parameters = []): never
    {
        $id = (int) ($parameters['id'] ?? 0);
        if ($id < 1) $this->error('شناسه رخداد نامعتبر است.', 422);

        $items = $this->repository()->timeline($id, (int) Request::query('limit', 100));
        $this->success(['items' => $items], 'تاریخچه رخداد دریافت شد.');
    }

    public function analysis(): never
    {
        $hours = (int) Request::query('hours', 24);
        if ($hours < 1 || $hours > 720) $this->error('بازه تحلیل باید بین ۱ تا ۷۲۰ ساعت باشد.', 422);

        $this->success($this->repository()->analysis($hours), 'تحلیل رخدادها دریافت شد.');
    }

    public function jobs(): never
    {
        $this->success($this->repository()->jobHealth(), 'وضعیت صف‌های موجود دریافت شد.');
    }
}
