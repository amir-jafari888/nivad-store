<?php

declare(strict_types=1);

/**
 * Bounded synchronous bulk mutations. Only operations with verified existing
 * service contracts are executable; queued/approval-dependent types fail closed.
 */
final class AdminBulkOperationService
{
    private PDO $db;
    private AdminBulkOperationPolicy $policy;
    private ProductService $products;
    private CategoryService $categories;
    private CouponService $coupons;

    public function __construct(
        ?PDO $db = null,
        ?AdminBulkOperationPolicy $policy = null,
        ?ProductService $products = null,
        ?CategoryService $categories = null,
        ?CouponService $coupons = null
    ) {
        $this->db = $db ?? Database::connection();
        $this->policy = $policy ?? new AdminBulkOperationPolicy();
        $this->products = $products ?? new ProductService(new ProductRepository($this->db));
        $this->categories = $categories ?? new CategoryService(new CategoryRepository($this->db));
        $this->coupons = $coupons ?? new CouponService(new CouponRepository($this->db));
    }

    public function execute(string $jobType, array $payload, bool $confirmed, ?int $approvalRequestId = null): array
    {
        $validated = $this->policy->validate($jobType, $payload, $confirmed, $approvalRequestId);
        $jobType = $validated['job_type'];
        $supported = ['catalog.products.status', 'catalog.categories.status', 'coupons.status'];
        if (!in_array($jobType, $supported, true)) {
            throw new DomainException('این عملیات گروهی هنوز Worker و مسیر تأیید عملیاتی ندارد؛ اجرا مسدود شد.');
        }

        $status = strtolower(trim((string)($payload['status'] ?? '')));
        $allowedStatuses = match ($jobType) {
            'catalog.products.status', 'catalog.categories.status' => ['active', 'inactive'],
            'coupons.status' => ['active', 'inactive', 'expired'],
            default => [],
        };
        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('وضعیت درخواستی برای این عملیات گروهی معتبر نیست.');
        }

        $ids = $validated['payload']['ids'];
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) $this->db->beginTransaction();

        $updated = [];
        try {
            foreach ($ids as $id) {
                $result = match ($jobType) {
                    'catalog.products.status' => $this->products->setStatus($id, $status),
                    'catalog.categories.status' => $this->categories->setStatus($id, $status),
                    'coupons.status' => $this->coupons->updateStatus($id, $status),
                };
                if ($result === null) {
                    throw new RuntimeException('رکورد شماره ' . $id . ' پیدا نشد یا تغییر وضعیت آن ناموفق بود؛ کل عملیات لغو شد.');
                }
                $updated[] = $id;
            }
            if ($startedTransaction) $this->db->commit();
        } catch (Throwable $exception) {
            if ($startedTransaction && $this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }

        return [
            'job_type' => $jobType,
            'status' => $status,
            'requested_count' => count($ids),
            'updated_count' => count($updated),
            'updated_ids' => $updated,
            'execution_mode' => 'synchronous_transaction',
        ];
    }
}
