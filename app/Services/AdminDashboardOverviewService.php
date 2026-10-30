<?php

declare(strict_types=1);

final class AdminDashboardOverviewService
{
    private AuditLogService $auditLogs;
    private AdminCapabilityService $capabilities;
    private ReportService $reports;
    private AdminSystemHealthService $health;

    public function __construct(
        ?AuditLogService $auditLogs = null,
        ?AdminCapabilityService $capabilities = null,
        ?ReportService $reports = null,
        ?AdminSystemHealthService $health = null
    ) {
        $this->auditLogs = $auditLogs ?? new AuditLogService();
        $this->capabilities = $capabilities ?? new AdminCapabilityService();
        $this->reports = $reports ?? new ReportService();
        $this->health = $health ?? new AdminSystemHealthService();
    }

    public function permissions(AdminAuthService $auth): array
    {
        return [
            'audit' => $auth->can('admin.audit-logs.index'),
            'inventory' => $auth->can('admin.inventory.show'),
            'incidents' => $auth->can('admin.capabilities.incidents.index'),
            'tickets' => $auth->can('admin.capabilities.tickets.index'),
            'products' => $auth->can('admin.products.store'),
            'orders' => $auth->can('admin.orders.index'),
            'admins' => $auth->can('admin.access.admins.index'),
        ];
    }

    public function overview(array $permissions): array
    {
        $activity = [
            'status' => !empty($permissions['audit']) ? 'empty' : 'restricted',
            'items' => [],
        ];

        if (!empty($permissions['audit'])) {
            try {
                $items = $this->auditLogs->recentAdminActivity(8);
                $activity = [
                    'status' => $items === [] ? 'empty' : 'ready',
                    'items' => $items,
                ];
            } catch (Throwable $exception) {
                $activity['status'] = 'error';
            }
        }

        $queueItems = [];
        $queueErrors = [];
        $queueSources = 0;

        if (!empty($permissions['inventory'])) {
            $queueSources++;
            try {
                $query = $this->reports->createQuery([]);
                $lowStock = $this->reports->inventoryLowStockSummary($query);
                $outOfStock = $this->reports->inventoryOutOfStockSummary($query);
                $queueItems[] = [
                    'key' => 'low_stock',
                    'priority' => 'low',
                    'priority_label' => 'عادی',
                    'title' => 'موجودی نزدیک به حد هشدار',
                    'count' => (int)($lowStock['low_stock_records'] ?? 0),
                    'href' => url('/admin/inventory/index.php'),
                    'detail' => 'بررسی Variantهایی که موجودی آن‌ها به آستانه تعریف‌شده رسیده است.',
                ];
                $queueItems[] = [
                    'key' => 'out_of_stock',
                    'priority' => 'critical',
                    'priority_label' => 'فوری',
                    'title' => 'Variantهای ناموجود',
                    'count' => (int)($outOfStock['out_of_stock_records'] ?? 0),
                    'href' => url('/admin/inventory/index.php'),
                    'detail' => 'بررسی موجودی صفر در رکوردهای انبار.',
                ];
            } catch (Throwable $exception) {
                $queueErrors[] = 'inventory';
            }
        }

        if (!empty($permissions['incidents'])) {
            $queueSources++;
            try {
                $queueItems[] = [
                    'key' => 'open_incidents',
                    'priority' => 'high',
                    'priority_label' => 'بالا',
                    'title' => 'رخدادهای باز سیستم',
                    'count' => $this->capabilities->countIncidentsByStatus('open'),
                    'href' => url('/admin/capabilities/index.php#admin-incidents-title'),
                    'detail' => 'رخدادهایی که هنوز به وضعیت دیگری منتقل نشده‌اند.',
                ];
            } catch (Throwable $exception) {
                $queueErrors[] = 'incidents';
            }
        }

        if (!empty($permissions['tickets'])) {
            $queueSources++;
            try {
                $queueItems[] = [
                    'key' => 'support_tickets',
                    'priority' => 'normal',
                    'priority_label' => 'متوسط',
                    'title' => 'تیکت‌های منتظر اقدام مدیر',
                    'count' => $this->capabilities->countTicketsByStatuses(['open', 'pending_admin']),
                    'href' => url('/admin/capabilities/index.php#admin-tickets-title'),
                    'detail' => 'تیکت‌های باز یا در انتظار پاسخ مدیر.',
                ];
            } catch (Throwable $exception) {
                $queueErrors[] = 'tickets';
            }
        }

        $priorityOrder = ['critical' => 0, 'high' => 1, 'normal' => 2, 'low' => 3];
        usort($queueItems, static function (array $left, array $right) use ($priorityOrder): int {
            $priority = ($priorityOrder[$left['priority'] ?? 'low'] ?? 3)
                <=> ($priorityOrder[$right['priority'] ?? 'low'] ?? 3);
            return $priority !== 0
                ? $priority
                : ((int)($right['count'] ?? 0) <=> (int)($left['count'] ?? 0));
        });

        $queueStatus = $queueSources === 0
            ? 'restricted'
            : ($queueErrors === [] ? 'ready' : ($queueItems === [] ? 'error' : 'partial'));

        return [
            'activity' => $activity,
            'action_queue' => [
                'status' => $queueStatus,
                'items' => $queueItems,
                'errors' => $queueErrors,
            ],
            'health' => $this->health->snapshot(),
        ];
    }
}
