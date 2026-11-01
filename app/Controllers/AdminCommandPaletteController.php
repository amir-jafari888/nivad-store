<?php

declare(strict_types=1);

final class AdminCommandPaletteController extends Controller
{
    private function adminId(): int
    {
        $id = AdminAuth::id();
        if ($id === null || $id < 1) $this->error('نشست مدیر معتبر نیست.', 401);
        if (!Authorization::can($id, 'admin.search.use')) $this->error('مجوز استفاده از Command Palette را ندارید.', 403);
        return $id;
    }

    public function index(): never
    {
        try {
            $items = (new AdminCommandPaletteService())->search(
                $this->adminId(),
                (string)Request::query('q', '')
            );
            $this->success(['items' => $items], 'فرمان‌های مجاز دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }

    public function execute(): never
    {
        $adminId = $this->adminId();
        $data = Request::isJson() ? Request::json() : $_POST;
        try {
            $result = (new AdminCommandPaletteService())->execute($adminId, (string)($data['command_id'] ?? ''));
            AdminAuditTrail::record($adminId, 'execute_command', 'admin_command_palette', (string)$result['id'], null, 'Admin executed an allowlisted navigation command.');
            $this->success($result, 'فرمان اجرا شد.');
        } catch (DomainException $exception) {
            $this->error($exception->getMessage(), 403);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
