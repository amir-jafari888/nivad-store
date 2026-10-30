<?php

declare(strict_types=1);

$admin = is_array($admin ?? null) ? $admin : [];
$metrics = is_array($metrics ?? null) ? $metrics : [];
$overview = is_array($overview ?? null) ? $overview : [];
$permissions = is_array($permissions ?? null) ? $permissions : [];

$orders = is_array($metrics['orders'] ?? null) ? $metrics['orders'] : [];
$payments = is_array($metrics['payments'] ?? null) ? $metrics['payments'] : [];
$orderItems = is_array($metrics['order_items'] ?? null) ? $metrics['order_items'] : [];
$inventory = is_array($metrics['inventory'] ?? null) ? $metrics['inventory'] : [];
$salesByPeriod = is_array($metrics['sales_by_period'] ?? null) ? $metrics['sales_by_period'] : [];
$salesByPeriodStatus = (string)($metrics['sales_by_period_status'] ?? 'dependency');
$periodComparison = is_array($metrics['period_comparison'] ?? null) ? $metrics['period_comparison'] : [];
$period = is_array($metrics['period'] ?? null) ? $metrics['period'] : [];

$activity = is_array($overview['activity'] ?? null) ? $overview['activity'] : [];
$activityItems = is_array($activity['items'] ?? null) ? $activity['items'] : [];
$activityStatus = (string)($activity['status'] ?? 'restricted');
$queue = is_array($overview['action_queue'] ?? null) ? $overview['action_queue'] : [];
$queueItems = is_array($queue['items'] ?? null) ? $queue['items'] : [];
$queueStatus = (string)($queue['status'] ?? 'restricted');
$health = is_array($overview['health'] ?? null) ? $overview['health'] : [];
$healthChecks = is_array($health['checks'] ?? null) ? $health['checks'] : [];

$formatNumber = static function (mixed $value): string {
    return is_numeric($value) ? number_format((float)$value, 0, '.', ',') : '—';
};
$formatDate = static function (mixed $value): string {
    if (!is_string($value) || $value === '') return '—';
    try { return (new DateTimeImmutable($value))->format('m/d'); }
    catch (Throwable $exception) { return '—'; }
};
$formatDateTime = static function (mixed $value): string {
    if (!is_string($value) || $value === '') return 'زمان نامشخص';
    try { return (new DateTimeImmutable($value))->format('Y/m/d H:i'); }
    catch (Throwable $exception) { return 'زمان نامشخص'; }
};
$formatComparison = static function (mixed $current, mixed $previous): string {
    if (!is_numeric($current) || !is_numeric($previous)) return 'مقایسه در دسترس نیست';
    $current = (float)$current;
    $previous = (float)$previous;
    if ($previous === 0.0) return $current === 0.0 ? 'بدون تغییر نسبت به مبنای صفر' : 'دوره قبل بدون داده';
    $change = (($current - $previous) / abs($previous)) * 100;
    $direction = $change > 0 ? 'افزایش' : ($change < 0 ? 'کاهش' : 'بدون تغییر');
    return $direction . ' ' . number_format(abs($change), 1) . '٪';
};
$statusText = static fn(string $status): string => match ($status) {
    'ok' => 'سالم',
    'error' => 'دارای خطا',
    'unknown' => 'نامشخص',
    default => 'نامشخص',
};

$maxNetSales = 0.0;
$maxOrders = 0.0;
foreach ($salesByPeriod as $periodRow) {
    $maxNetSales = max($maxNetSales, (float)($periodRow['net_sales'] ?? 0));
    $maxOrders = max($maxOrders, (float)($periodRow['total_orders'] ?? 0));
}

$quickActions = [];
if (!empty($permissions['products'])) {
    $quickActions[] = ['label' => 'افزودن محصول', 'detail' => 'ایجاد محصول جدید', 'href' => url('/admin/products/create.php')];
}
if (!empty($permissions['orders'])) {
    $quickActions[] = ['label' => 'مدیریت سفارش‌ها', 'detail' => 'مشاهده و پیگیری سفارش‌ها', 'href' => url('/admin/orders')];
}
if (!empty($permissions['inventory'])) {
    $quickActions[] = ['label' => 'مدیریت موجودی', 'detail' => 'بررسی وضعیت انبار', 'href' => url('/admin/inventory/index.php')];
}
if (!empty($permissions['admins'])) {
    $quickActions[] = ['label' => 'مدیریت مدیران', 'detail' => 'مدیریت حساب‌ها و دسترسی‌ها', 'href' => url('/admin/access/index.php')];
}
$quickActions[] = ['label' => 'تنظیمات پنل', 'detail' => 'شخصی‌سازی تجربه مدیریت', 'href' => url('/admin/settings')];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>داشبورد مدیریت | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-dashboard-page">
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <main id="main-content" tabindex="-1" class="admin-dashboard-main">
        <header class="admin-dashboard-page-heading">
            <div>
                <p class="admin-dashboard-eyebrow">مرکز کنترل فروشگاه</p>
                <h1>داشبورد مدیریت</h1>
                <p>نمای عملیاتی مبتنی بر داده‌های موجود فروشگاه و دسترسی واقعی حساب مدیر.</p>
            </div>
            <div class="admin-dashboard-date-chip">
                <span aria-hidden="true">◷</span>
                <span>روند ۱۴ روز اخیر</span>
            </div>
        </header>

        <section class="admin-dashboard-kpis" aria-label="شاخص‌های اصلی">
            <article class="admin-dashboard-kpi admin-dashboard-kpi-orders">
                <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                <h2>کل سفارش‌ها</h2>
                <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orders['total_orders'] ?? null)) ?></p>
                <p class="admin-dashboard-kpi-foot">مبلغ سفارش‌ها: <strong><?= e($formatNumber($orders['total_amount'] ?? null)) ?></strong></p>
            </article>
            <article class="admin-dashboard-kpi admin-dashboard-kpi-payments">
                <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                <h2>تعداد پرداخت‌ها</h2>
                <p class="admin-dashboard-kpi-value"><?= e($formatNumber($payments['total_payments'] ?? null)) ?></p>
                <p class="admin-dashboard-kpi-foot">مبلغ پرداخت‌ها: <strong><?= e($formatNumber($payments['total_amount'] ?? null)) ?></strong></p>
            </article>
            <article class="admin-dashboard-kpi admin-dashboard-kpi-items">
                <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                <h2>کالاهای سفارش‌داده‌شده</h2>
                <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orderItems['total_quantity'] ?? null)) ?></p>
                <p class="admin-dashboard-kpi-foot">خطوط سفارش: <strong><?= e($formatNumber($orderItems['total_lines'] ?? null)) ?></strong></p>
            </article>
            <article class="admin-dashboard-kpi admin-dashboard-kpi-inventory">
                <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">وضعیت فعلی</span></div>
                <h2>مجموع موجودی</h2>
                <p class="admin-dashboard-kpi-value"><?= e($formatNumber($inventory['total_quantity'] ?? null)) ?></p>
                <p class="admin-dashboard-kpi-foot">موجودی در دسترس: <strong><?= e($formatNumber($inventory['total_available_quantity'] ?? null)) ?></strong></p>
            </article>
        </section>

        <section class="admin-dashboard-period-comparison" aria-label="مقایسه عملکرد دوره‌ای">
            <div class="admin-dashboard-comparison-heading">
                <h2>مقایسه ۱۴ روز اخیر با ۱۴ روز قبل</h2>
                <p><?= e((string)($period['from'] ?? '—')) ?> تا <?= e((string)($period['to'] ?? '—')) ?> در برابر <?= e((string)($period['previous_from'] ?? '—')) ?> تا <?= e((string)($period['previous_to'] ?? '—')) ?></p>
            </div>
            <?php foreach ([
                'orders' => 'سفارش‌ها',
                'payments' => 'پرداخت‌ها',
                'items' => 'تعداد کالاهای سفارش‌شده',
                'sales' => 'مبلغ سفارش‌ها',
            ] as $key => $label) {
                $comparison = is_array($periodComparison[$key] ?? null) ? $periodComparison[$key] : [];
                $currentValue = $comparison['current'] ?? null;
                $previousValue = $comparison['previous'] ?? null;
            ?>
                <article class="admin-dashboard-comparison-card">
                    <h3><?= e($label) ?></h3>
                    <p class="admin-dashboard-comparison-values"><strong><?= e($formatNumber($currentValue)) ?></strong><span>دوره قبل: <?= e($formatNumber($previousValue)) ?></span></p>
                    <p class="admin-dashboard-comparison-delta"><?= e($formatComparison($currentValue, $previousValue)) ?></p>
                </article>
            <?php } ?>
        </section>

        <section class="admin-dashboard-chart-grid" aria-label="روند عملکرد">
            <article class="admin-dashboard-panel admin-dashboard-sales-panel">
                <div class="admin-dashboard-panel-heading">
                    <div><p class="admin-dashboard-eyebrow">عملکرد مالی</p><h2>روند فروش خالص</h2></div>
                    <span class="admin-dashboard-panel-meta">۱۴ روز اخیر</span>
                </div>
                <?php if ($salesByPeriodStatus === 'error') { ?>
                    <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>داده‌های نمودار در دسترس نیست.</strong><span>دریافت روند فروش با خطا مواجه شد؛ سایر شاخص‌ها جداگانه نمایش داده می‌شوند.</span></div>
                <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                    <div class="admin-dashboard-state" role="status"><strong><?= $salesByPeriodStatus === 'empty' ? 'در این بازه داده‌ای برای نمایش وجود ندارد.' : 'اتصال داده‌های دوره‌ای تأیید نشده است.' ?></strong><span>نمودار فقط با داده‌های واقعی گزارش‌گیری نمایش داده می‌شود.</span></div>
                <?php } else { ?>
                    <div class="admin-dashboard-chart" role="img" aria-label="نمودار میله‌ای فروش خالص روزانه در ۱۴ روز اخیر">
                        <div class="admin-dashboard-chart-bars">
                            <?php foreach ($salesByPeriod as $row) {
                                $value = max(0, (float)($row['net_sales'] ?? 0));
                                $height = $maxNetSales > 0 ? max(3, ($value / $maxNetSales) * 100) : 3;
                            ?>
                                <div class="admin-dashboard-chart-column" title="<?= e($formatDate($row['period'] ?? null) . ': ' . $formatNumber($value)) ?>">
                                    <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                    <span class="admin-dashboard-chart-bar" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                    <span class="admin-dashboard-chart-label"><?= e($formatDate($row['period'] ?? null)) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot" aria-hidden="true"></span><span>فروش خالص بر اساس مبلغ سفارش‌ها</span></div>
            </article>

            <article class="admin-dashboard-panel admin-dashboard-orders-panel">
                <div class="admin-dashboard-panel-heading">
                    <div><p class="admin-dashboard-eyebrow">حجم سفارش‌ها</p><h2>سفارش‌های روزانه</h2></div>
                    <span class="admin-dashboard-panel-meta">۱۴ روز اخیر</span>
                </div>
                <?php if ($salesByPeriodStatus === 'error') { ?>
                    <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>داده‌های نمودار در دسترس نیست.</strong><span>برای جلوگیری از نمایش اطلاعات نادرست، نمودار سفارش‌ها نمایش داده نمی‌شود.</span></div>
                <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                    <div class="admin-dashboard-state" role="status"><strong><?= $salesByPeriodStatus === 'empty' ? 'در این بازه سفارشی ثبت نشده است.' : 'اتصال داده‌های دوره‌ای تأیید نشده است.' ?></strong><span>این نمودار فقط از خروجی گزارش‌گیری واقعی استفاده می‌کند.</span></div>
                <?php } else { ?>
                    <div class="admin-dashboard-chart" role="img" aria-label="نمودار میله‌ای تعداد سفارش‌های روزانه در ۱۴ روز اخیر">
                        <div class="admin-dashboard-chart-bars">
                            <?php foreach ($salesByPeriod as $row) {
                                $value = max(0, (float)($row['total_orders'] ?? 0));
                                $height = $maxOrders > 0 ? max(3, ($value / $maxOrders) * 100) : 3;
                            ?>
                                <div class="admin-dashboard-chart-column" title="<?= e($formatDate($row['period'] ?? null) . ': ' . $formatNumber($value)) ?>">
                                    <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                    <span class="admin-dashboard-chart-bar admin-dashboard-chart-bar-secondary" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                    <span class="admin-dashboard-chart-label"><?= e($formatDate($row['period'] ?? null)) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot admin-dashboard-legend-dot-secondary" aria-hidden="true"></span><span>تعداد سفارش‌ها در هر روز</span></div>
            </article>
        </section>

        <section class="admin-dashboard-bottom-grid" aria-label="جزئیات گزارش و عملیات">
            <article class="admin-dashboard-panel admin-dashboard-period-panel">
                <div class="admin-dashboard-panel-heading">
                    <div><p class="admin-dashboard-eyebrow">جزئیات داده</p><h2>خلاصه ۱۴ روز اخیر</h2></div>
                </div>
                <?php if ($salesByPeriodStatus === 'error') { ?>
                    <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>گزارش دوره‌ای در دسترس نیست.</strong><span>بعداً دوباره تلاش کنید.</span></div>
                <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                    <div class="admin-dashboard-state" role="status"><strong>داده‌ای برای جدول دوره‌ای در دسترس نیست.</strong><span>با ثبت داده واقعی، ردیف‌های گزارش در این بخش نمایش داده می‌شوند.</span></div>
                <?php } else { ?>
                    <div class="admin-dashboard-table-wrap" tabindex="0" aria-label="جدول خلاصه دوره‌ای، برای مشاهده افقی قابل پیمایش است">
                        <table class="admin-dashboard-table">
                            <thead><tr><th scope="col">تاریخ</th><th scope="col">سفارش</th><th scope="col">فروش ناخالص</th><th scope="col">تخفیف</th><th scope="col">فروش خالص</th></tr></thead>
                            <tbody>
                                <?php foreach (array_reverse($salesByPeriod) as $row) { ?>
                                    <tr>
                                        <td><?= e((string)($row['period'] ?? '—')) ?></td>
                                        <td><?= e($formatNumber($row['total_orders'] ?? null)) ?></td>
                                        <td><?= e($formatNumber($row['gross_sales'] ?? null)) ?></td>
                                        <td><?= e($formatNumber($row['total_discount'] ?? null)) ?></td>
                                        <td><strong><?= e($formatNumber($row['net_sales'] ?? null)) ?></strong></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </article>

            <div class="admin-dashboard-operations-stack">
                <article class="admin-dashboard-panel admin-dashboard-activity-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">ردیابی عملیات</p><h2>فعالیت‌های اخیر مدیران</h2></div>
                        <?php if (!empty($permissions['audit'])) { ?><a href="<?= e(url('/admin/audit-logs')) ?>">مشاهده همه</a><?php } ?>
                    </div>
                    <?php if ($activityStatus === 'restricted') { ?>
                        <div class="admin-dashboard-state"><strong>دسترسی این گزارش برای حساب فعلی فعال نیست.</strong><span>فقط مدیران دارای مجوز گزارش رویدادها می‌توانند فعالیت‌ها را مشاهده کنند.</span></div>
                    <?php } elseif ($activityStatus === 'error') { ?>
                        <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>دریافت فعالیت‌ها ناموفق بود.</strong><span>منبع گزارش در دسترس نیست؛ بعداً دوباره تلاش کنید.</span></div>
                    <?php } elseif ($activityItems === []) { ?>
                        <div class="admin-dashboard-state"><strong>هنوز رویداد مدیریتی ثبت نشده است.</strong><span>پس از ثبت عملیات ممیزی‌شده، رویدادها در این بخش ظاهر می‌شوند.</span></div>
                    <?php } else { ?>
                        <ul class="admin-dashboard-activity-list">
                            <?php foreach ($activityItems as $item) { ?>
                                <li>
                                    <span class="admin-dashboard-activity-marker" aria-hidden="true"></span>
                                    <div class="admin-dashboard-activity-copy">
                                        <strong><?= e((string)($item['description'] ?? 'عملیات مدیریتی ثبت شد.')) ?></strong>
                                        <span><?= e((string)($item['admin_name'] ?? 'مدیر')) ?> · <?= e((string)($item['module'] ?? '')) ?> / <?= e((string)($item['action'] ?? '')) ?></span>
                                        <time datetime="<?= e((string)($item['created_at'] ?? '')) ?>"><?= e($formatDateTime($item['created_at'] ?? null)) ?></time>
                                        <small class="admin-dashboard-activity-reference">شناسه رویداد: #<?= e((string)($item['id'] ?? '—')) ?></small>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </article>

                <article class="admin-dashboard-panel admin-dashboard-queue-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">اقدامات قابل پیگیری</p><h2>صف اقدامات</h2></div>
                    </div>
                    <?php if ($queueStatus === 'restricted') { ?>
                        <div class="admin-dashboard-state"><strong>منبع قابل مشاهده‌ای برای صف اقدامات وجود ندارد.</strong><span>با توجه به مجوزهای حساب فعلی، داده عملیاتی قابل نمایش در این بخش در دسترس نیست.</span></div>
                    <?php } elseif ($queueStatus === 'error') { ?>
                        <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>دریافت صف اقدامات ناموفق بود.</strong><span>هیچ تعداد یا هشدار تخمینی نمایش داده نمی‌شود.</span></div>
                    <?php } else { ?>
                        <?php $actionableCount = array_sum(array_map(static fn(array $item): int => (int)($item['count'] ?? 0), $queueItems)); ?>
                        <?php if ($actionableCount === 0) { ?>
                            <p class="admin-dashboard-queue-empty">در منابع قابل مشاهده، موردی نیازمند اقدام ثبت نشده است.</p>
                        <?php } ?>
                        <ul class="admin-dashboard-queue-list">
                            <?php foreach ($queueItems as $item) { ?>
                                <li>
                                    <a href="<?= e((string)$item['href']) ?>">
                                        <span><strong><?= e((string)$item['title']) ?></strong><small><?= e((string)$item['detail']) ?></small><small class="admin-dashboard-queue-priority" data-priority="<?= e((string)($item['priority'] ?? 'low')) ?>">اولویت: <?= e((string)($item['priority_label'] ?? 'عادی')) ?></small></span>
                                        <b><?= e($formatNumber($item['count'] ?? 0)) ?></b>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        <?php if ($queueStatus === 'partial') { ?><p class="admin-dashboard-inline-warning" role="status">بعضی منابع صف اقدامات در دسترس نبودند؛ اعداد فقط برای منابع موفق نمایش داده شده‌اند.</p><?php } ?>
                    <?php } ?>
                </article>
            </div>
        </section>

        <section class="admin-dashboard-ops-grid" aria-label="سلامت سیستم و میانبرهای مدیریتی">
            <article class="admin-dashboard-panel admin-dashboard-health-panel">
                <div class="admin-dashboard-panel-heading">
                    <div><p class="admin-dashboard-eyebrow">بررسی‌های پایه</p><h2>سلامت سیستم</h2></div>
                    <span class="admin-dashboard-health-status" data-status="<?= e((string)($health['status'] ?? 'unknown')) ?>"><?= ($health['status'] ?? '') === 'healthy' ? 'سالم' : 'نیازمند بررسی' ?></span>
                </div>
                <ul class="admin-dashboard-health-list">
                    <?php foreach (['php' => 'نسخه PHP', 'pdo' => 'افزونه PDO', 'pdo_mysql' => 'درایور MySQL برای PDO', 'json' => 'افزونه JSON', 'mbstring' => 'افزونه MBString', 'database' => 'اتصال پایگاه داده', 'timezone' => 'منطقه زمانی'] as $key => $label) {
                        $check = is_array($healthChecks[$key] ?? null) ? $healthChecks[$key] : ['status' => 'unknown'];
                    ?>
                        <li>
                            <span><?= e($label) ?><?php if ($key === 'php' && isset($check['version'])) { ?><small><?= e((string)$check['version']) ?></small><?php } ?><?php if ($key === 'timezone' && isset($check['value'])) { ?><small><?= e((string)$check['value']) ?></small><?php } ?><?php if ($key === 'database' && isset($check['duration_ms'])) { ?><small><?= e((string)$check['duration_ms']) ?> ms</small><?php } ?></span>
                            <b data-status="<?= e((string)($check['status'] ?? 'unknown')) ?>"><?= e($statusText((string)($check['status'] ?? 'unknown'))) ?></b>
                        </li>
                    <?php } ?>
                </ul>
                <p class="admin-dashboard-health-note">این بررسی نسخه PHP، افزونه‌های موردنیاز، اتصال پایگاه داده و منطقه زمانی را پوشش می‌دهد. سلامت سرویس‌های خارجی، Queue و Cron تا زمان اتصال به probe واقعی در این گزارش ادعا نمی‌شود.</p>
            </article>

            <article class="admin-dashboard-panel admin-dashboard-quick-actions-panel">
                <div class="admin-dashboard-panel-heading">
                    <div><p class="admin-dashboard-eyebrow">دسترسی سریع</p><h2>میانبرهای مدیریتی</h2></div>
                </div>
                <?php if ($quickActions === []) { ?>
                    <div class="admin-dashboard-state"><strong>میانبر مجاز دیگری برای این حساب تعریف نشده است.</strong></div>
                <?php } else { ?>
                    <div class="admin-dashboard-quick-actions">
                        <?php foreach ($quickActions as $action) { ?>
                            <a href="<?= e($action['href']) ?>">
                                <strong><?= e($action['label']) ?></strong>
                                <span><?= e($action['detail']) ?></span>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>

        <footer class="admin-dashboard-footer">
            <span>نیواد · پنل مدیریت</span>
            <span>داده‌های عملیاتی فقط از منابع موجود و مجاز خوانده می‌شوند.</span>
        </footer>
    </main>
</body>
</html>
