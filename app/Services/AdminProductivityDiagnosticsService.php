<?php

declare(strict_types=1);

/**
 * Evidence-only operational diagnostics. Missing telemetry is reported as
 * unknown/not_configured; this service never invents a healthy state.
 */
final class AdminProductivityDiagnosticsService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function snapshot(): array
    {
        return [
            'backup' => $this->backupAwareness(),
            'queues' => $this->queueMonitoring(),
            'webhooks' => $this->webhookMonitoring(),
            'payment_gateway' => $this->paymentGatewayTelemetry(),
            'inventory' => $this->inventoryAnomalies(),
            'customer_abuse' => $this->customerAbuseSignals(),
        ];
    }

    private function backupAwareness(): array
    {
        $tables = $this->matchingTables('%backup%');
        return [
            'status' => $tables === [] ? 'unknown' : 'evidence_available',
            'backup_tables' => $tables,
            'message' => $tables === []
                ? 'هیچ جدول ثبت وضعیت پشتیبان‌گیری در Schema فعال شناسایی نشد؛ وجود فایل Backup یا موفقیت Restore قابل اثبات نیست.'
                : 'جدول‌های مرتبط با Backup شناسایی شدند؛ وضعیت واقعی فایل‌ها و آزمون Restore نیازمند بررسی مستقل است.',
            'restore_tested' => false,
        ];
    }

    private function queueMonitoring(): array
    {
        try {
            return [
                'status' => 'observed',
                'items' => (new AdminIncidentDetectionRepository($this->db))->jobHealth(),
                'source' => ['admin_operation_jobs', 'admin_email_outbox'],
            ];
        } catch (Throwable $exception) {
            return ['status' => 'blocked', 'items' => [], 'message' => 'خواندن وضعیت صف‌ها به‌دلیل خطای وابستگی دیتابیس ممکن نشد.'];
        }
    }

    private function webhookMonitoring(): array
    {
        $tables = $this->matchingTables('%webhook%');
        return [
            'status' => $tables === [] ? 'unknown' : 'evidence_available',
            'tables' => $tables,
            'message' => $tables === []
                ? 'در Schema فعال جدول Webhook شناسایی نشد؛ وجود endpoint یا مصرف‌کننده Webhook هنوز اثبات نشده است.'
                : 'جدول‌های Webhook پیدا شدند؛ پایش تحویل، retry و امضای درخواست هنوز به ممیزی producer/consumer نیاز دارد.',
            'delivery_health' => 'unverified',
        ];
    }

    private function paymentGatewayTelemetry(): array
    {
        if (!$this->tableExists('payment_attempts')) {
            return ['status' => 'blocked', 'gateways' => [], 'message' => 'جدول payment_attempts در Schema فعال موجود نیست.'];
        }
        $rows = $this->db->query(
            'SELECT gateway, status, COUNT(*) AS total, MAX(created_at) AS last_attempt_at
             FROM payment_attempts
             WHERE created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)
             GROUP BY gateway, status
             ORDER BY gateway, status'
        )->fetchAll(PDO::FETCH_ASSOC);
        return [
            'status' => 'telemetry_only',
            'window_hours' => 24,
            'attempts' => $rows,
            'message' => 'این گزارش فقط تلاش‌های ثبت‌شده در دیتابیس را نشان می‌دهد؛ اتصال شبکه، دسترس‌پذیری و صحت پاسخ درگاه را تست نمی‌کند.',
            'connectivity_tested' => false,
        ];
    }

    private function inventoryAnomalies(): array
    {
        if (!$this->tableExists('inventory')) {
            return ['status' => 'blocked', 'items' => [], 'message' => 'جدول inventory در Schema فعال موجود نیست.'];
        }
        $items = $this->db->query(
            'SELECT id, variant_id, quantity, reserved_quantity, low_stock_threshold, updated_at,
                    CASE
                        WHEN quantity < 0 THEN "negative_quantity"
                        WHEN reserved_quantity < 0 THEN "negative_reserved"
                        WHEN reserved_quantity > quantity THEN "reserved_exceeds_quantity"
                        WHEN quantity <= low_stock_threshold THEN "low_stock"
                        ELSE "unknown"
                    END AS anomaly_type
             FROM inventory
             WHERE quantity < 0
                OR reserved_quantity < 0
                OR reserved_quantity > quantity
                OR quantity <= low_stock_threshold
             ORDER BY
                CASE WHEN quantity < 0 OR reserved_quantity < 0 OR reserved_quantity > quantity THEN 0 ELSE 1 END,
                updated_at DESC
             LIMIT 100'
        )->fetchAll(PDO::FETCH_ASSOC);
        return [
            'status' => 'observed',
            'items' => $items,
            'count' => count($items),
            'message' => 'سیگنال‌های موجودی بر اساس ستون‌های واقعی Inventory محاسبه شده‌اند؛ کمبود موجودی با ناسازگاری داده یکی نیست.',
        ];
    }

    private function customerAbuseSignals(): array
    {
        return [
            'status' => 'blocked',
            'items' => [],
            'message' => 'منبع داده و سیاست معتبر برای تشخیص سوءاستفاده مشتری در کد فعلی احراز نشد؛ هیچ مشتری به‌صورت خودکار برچسب‌گذاری نمی‌شود.',
        ];
    }

    private function matchingTables(string $pattern): array
    {
        $stmt = $this->db->prepare(
            'SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE :pattern
             ORDER BY TABLE_NAME'
        );
        $stmt->execute(['pattern' => $pattern]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name LIMIT 1'
        );
        $stmt->execute(['table_name' => $table]);
        return $stmt->fetchColumn() !== false;
    }
}
