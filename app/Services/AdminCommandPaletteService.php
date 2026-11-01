<?php

declare(strict_types=1);

/** Explicit command allowlist. Commands navigate or request safe read-only actions. */
final class AdminCommandPaletteService
{
    private const COMMANDS = [
        'dashboard.open' => ['label' => 'رفتن به داشبورد', 'detail' => 'داشبورد مدیریت', 'permission' => 'admin.dashboard.view', 'href' => '/nivad-panel'],
        'products.open' => ['label' => 'مدیریت محصولات', 'detail' => 'کاتالوگ', 'permission' => 'admin.products.index', 'href' => '/nivad-panel/products'],
        'orders.open' => ['label' => 'مدیریت سفارش‌ها', 'detail' => 'فروش', 'permission' => 'admin.orders.index', 'href' => '/nivad-panel/orders'],
        'customers.open' => ['label' => 'مدیریت مشتریان', 'detail' => 'مشتریان', 'permission' => 'admin.users.index', 'href' => '/nivad-panel/users'],
        'tickets.open' => ['label' => 'مرکز پشتیبانی', 'detail' => 'پشتیبانی', 'permission' => 'admin.capabilities.tickets.index', 'href' => '/nivad-panel/capabilities/index.php#admin-tickets-title'],
        'incidents.open' => ['label' => 'مدیریت رخدادها', 'detail' => 'سلامت و خطاها', 'permission' => 'admin.capabilities.incidents.index', 'href' => '/nivad-panel/capabilities/index.php#admin-incidents-title'],
        'notifications.open' => ['label' => 'اعلان‌های مدیریتی', 'detail' => 'ارتباطات', 'permission' => 'admin.capabilities.notifications.index', 'href' => '/nivad-panel/notifications'],
        'inventory.open' => ['label' => 'مدیریت موجودی', 'detail' => 'انبار', 'permission' => 'admin.inventory.manage', 'href' => '/nivad-panel/inventory'],
        'security.open' => ['label' => 'عملیات امنیتی', 'detail' => 'امنیت', 'permission' => 'admin.security_events.view', 'href' => '/nivad-panel/security-events/index.php'],
    ];

    public function search(int $adminId, string $query = ''): array
    {
        if ($adminId < 1) throw new InvalidArgumentException('شناسه مدیر معتبر نیست.');
        $query = trim($query);
        if (mb_strlen($query) > 100) throw new InvalidArgumentException('عبارت فرمان بیش از حد طولانی است.');
        $items = [];
        foreach (self::COMMANDS as $id => $command) {
            if (!Authorization::can($adminId, $command['permission'])) continue;
            $haystack = mb_strtolower($command['label'] . ' ' . $command['detail'] . ' ' . $id);
            if ($query !== '' && !str_contains($haystack, mb_strtolower($query))) continue;
            $items[] = ['id' => $id, 'label' => $command['label'], 'detail' => $command['detail']];
        }
        return $items;
    }

    public function execute(int $adminId, string $commandId): array
    {
        $command = self::COMMANDS[$commandId] ?? null;
        if ($adminId < 1 || $command === null) throw new InvalidArgumentException('فرمان ناشناخته است.');
        if (!Authorization::can($adminId, $command['permission'])) throw new DomainException('اجازه اجرای این فرمان را ندارید.');
        return ['id' => $commandId, 'action' => 'navigate', 'href' => url($command['href'])];
    }
}
