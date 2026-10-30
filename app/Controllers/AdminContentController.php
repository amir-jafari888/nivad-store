<?php

declare(strict_types=1);

final class AdminContentController extends Controller
{
    private AdminContentService $service;

    public function __construct(?AdminContentService $service = null)
    {
        $this->service = $service ?? new AdminContentService();
    }

    private function adminId(): int
    {
        $id = AdminAuth::id();
        if ($id === null || $id < 1) $this->error('نشست مدیر معتبر نیست.', 401);
        return $id;
    }

    private function payload(): array
    {
        return Request::isJson() ? Request::json() : $_POST;
    }

    private function invalid(InvalidArgumentException $e): never
    {
        $this->error($e->getMessage(), 422);
    }

    public function pages(): never
    {
        $this->success(['items' => $this->service->pages()], 'صفحات CMS دریافت شدند.');
    }

    public function sections(): never
    {
        try {
            $page = (string)Request::query('page', 'home');
            $this->success(['items' => $this->service->sections($page, (string)Request::query('deleted', '') === '1')], 'سکشن‌ها دریافت شدند.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function createSection(): never
    {
        try {
            $data = $this->payload();
            $id = $this->service->create((string)($data['page_key'] ?? ''), $data, $this->adminId());
            AdminAuditTrail::record($this->adminId(), 'create', 'admin_cms_sections', 'cms_section', $id, 'CMS section created.');
            $this->success(['id' => $id], 'سکشن ایجاد شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function updateSection(array $parameters = []): never
    {
        try {
            $id = (int)($parameters['id'] ?? 0);
            $updated = $this->service->update($id, $this->payload(), $this->adminId());
            if (!$updated) $this->error('سکشن پیدا نشد.', 404);
            AdminAuditTrail::record($this->adminId(), 'update', 'admin_cms_sections', 'cms_section', $id, 'CMS section updated.');
            $this->success(['updated' => true], 'تغییرات سکشن به‌صورت پیش‌نویس ذخیره شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function publish(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        if (!$this->service->publish($id, $this->adminId())) $this->error('سکشن پیدا نشد.', 404);
        AdminAuditTrail::record($this->adminId(), 'publish', 'admin_cms_sections', 'cms_section', $id, 'CMS section published.');
        $this->success(['published' => true], 'سکشن منتشر شد.');
    }

    public function schedule(array $parameters = []): never
    {
        try {
            $id = (int)($parameters['id'] ?? 0);
            $data = $this->payload();
            if (!$this->service->schedule($id, (string)($data['scheduled_at'] ?? ''), $this->adminId())) $this->error('سکشن پیدا نشد.', 404);
            $this->success(['scheduled' => true], 'زمان انتشار ثبت شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function deleteSection(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        if (!$this->service->delete($id, $this->adminId())) $this->error('سکشن پیدا نشد.', 404);
        $this->success(['deleted' => true], 'سکشن به آرشیو منتقل شد.');
    }

    public function restoreDeleted(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        if (!$this->service->restoreDeleted($id, $this->adminId())) $this->error('سکشن آرشیوشده پیدا نشد.', 404);
        $this->success(['restored' => true], 'سکشن بازیابی شد.');
    }

    public function reorder(): never
    {
        try {
            $data = $this->payload();
            $this->service->reorder((string)($data['page_key'] ?? ''), (array)($data['ids'] ?? []), $this->adminId());
            $this->success(['saved' => true], 'ترتیب سکشن‌ها ذخیره شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function revisions(array $parameters = []): never
    {
        try {
            $items = $this->service->revisions((int)($parameters['id'] ?? 0));
            $this->success(['items' => $items], 'تاریخچه سکشن دریافت شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function restoreRevision(array $parameters = []): never
    {
        try {
            $id = (int)($parameters['id'] ?? 0);
            $revisionId = (int)($parameters['revisionId'] ?? 0);
            if (!$this->service->restoreRevision($id, $revisionId, $this->adminId())) $this->error('نسخه انتخاب‌شده پیدا نشد.', 404);
            AdminAuditTrail::record($this->adminId(), 'restore_revision', 'admin_cms_sections', 'cms_section', $id, 'CMS section revision restored.');
            $this->success(['restored' => true], 'نسخه بازیابی شد و به‌صورت پیش‌نویس قرار گرفت.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function media(): never
    {
        $this->success(['items' => $this->service->media()], 'کتابخانه رسانه دریافت شد.');
    }

    public function uploadMedia(): never
    {
        try {
            $upload = $_FILES['file'] ?? [];
            $alt = isset($_POST['alt_text']) ? (string)$_POST['alt_text'] : null;
            $id = $this->service->uploadMedia($upload, $alt, $this->adminId());
            AdminAuditTrail::record($this->adminId(), 'upload', 'admin_media_assets', 'media_asset', $id, 'CMS media uploaded.');
            $this->success(['id' => $id, 'url' => url('/api/content-media/' . $id)], 'تصویر بارگذاری شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }

    public function updateMedia(array $parameters = []): never
    {
        $data = $this->payload();
        $id = (int)($parameters['id'] ?? 0);
        if (!$this->service->updateMedia($id, isset($data['alt_text']) ? (string)$data['alt_text'] : null, $this->adminId())) $this->error('تصویر پیدا نشد.', 404);
        $this->success(['updated' => true], 'اطلاعات تصویر به‌روزرسانی شد.');
    }

    public function deleteMedia(array $parameters = []): never
    {
        try {
            $id = (int)($parameters['id'] ?? 0);
            if (!$this->service->deleteMedia($id, $this->adminId())) $this->error('تصویر پیدا نشد.', 404);
            $this->success(['deleted' => true], 'تصویر حذف شد.');
        } catch (InvalidArgumentException $e) { $this->invalid($e); }
    }
}
