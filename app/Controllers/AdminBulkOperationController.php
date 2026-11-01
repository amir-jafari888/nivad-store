<?php

declare(strict_types=1);

final class AdminBulkOperationController extends Controller
{
    public function execute(): never
    {
        $adminId = AdminAuth::id();
        if ($adminId === null || $adminId < 1) $this->error('نشست مدیر معتبر نیست.', 401);

        if (!Authorization::can($adminId, 'admin.bulk_operations.execute')) {
            $this->error('مجوز اجرای عملیات گروهی را ندارید.', 403);
        }

        $data = Request::isJson() ? Request::json() : $_POST;
        $jobType = strtolower(trim((string)($data['job_type'] ?? '')));
        $definition = AdminBulkOperationRegistry::find($jobType);
        if ($definition === null) $this->error('نوع عملیات گروهی مجاز نیست.', 422);

        $featureAccess = new AdminFeatureAccessService();
        if (!$featureAccess->canAccess($adminId, (string)$definition['feature_id'])) {
            $this->error('مجوز ماژول مربوط به این عملیات را ندارید.', 403);
        }

        $permission = match ($jobType) {
            'catalog.products.status' => 'admin.products.manage',
            'catalog.categories.status' => 'admin.products.manage',
            'coupons.status' => 'admin.orders.manage',
            'inventory.adjust' => 'admin.inventory.manage',
            'orders.status' => 'admin.orders.manage',
            'content.publish' => 'admin.content.publish',
            'media.archive' => 'admin.media.manage',
            default => null,
        };
        if ($permission === null || !Authorization::can($adminId, $permission)) {
            $this->error('مجوز دقیق این عملیات در دسترس نیست.', 403);
        }

        try {
            $result = (new AdminBulkOperationService())->execute(
                $jobType,
                is_array($data['payload'] ?? null) ? $data['payload'] : $data,
                (bool)($data['confirmed'] ?? false),
                isset($data['approval_request_id']) ? (int)$data['approval_request_id'] : null
            );
            foreach ($result['updated_ids'] as $entityId) {
                AdminAuditTrail::record($adminId, 'bulk_status_update', 'bulk_operations', $jobType, (int)$entityId, 'Admin executed an allowlisted transactional bulk status update.', null, ['status' => $result['status'], 'job_type' => $jobType]);
            }
            $this->success($result, 'عملیات گروهی با موفقیت انجام شد.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        } catch (DomainException $exception) {
            $this->error($exception->getMessage(), 501);
        } catch (Throwable $exception) {
            Logger::error('Admin bulk operation failed.', ['admin_id' => $adminId, 'job_type' => $jobType, 'exception' => get_class($exception)]);
            $this->error('عملیات گروهی انجام نشد؛ تغییرات این دسته بازگردانی شدند.', 500);
        }
    }
}
