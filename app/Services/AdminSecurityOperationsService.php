<?php

declare(strict_types=1);

/**
 * Evidence-based security signal collection. Signals are review prompts, not
 * automated fraud verdicts or account-blocking decisions.
 */
final class AdminSecurityOperationsService
{
    private PDO $db;
    private AdminSecurityEventRepository $events;

    // Baselines reuse existing authentication throttling policy where possible.
    private const ADMIN_ACTION_WINDOW_MINUTES = 10;
    private const ADMIN_ACTION_THRESHOLD = 30;
    private const BULK_ACTION_WINDOW_MINUTES = 10;
    private const BULK_ACTION_THRESHOLD = 10;

    public function __construct(?PDO $db = null, ?AdminSecurityEventRepository $events = null)
    {
        $this->db = $db ?? Database::connection();
        $this->events = $events ?? new AdminSecurityEventRepository($this->db);
    }

    public function scan(): array
    {
        $created = [
            'suspicious_login' => $this->scanSuspiciousLogins(),
            'admin_behavior' => $this->scanAdminBehavior(),
            'bulk_changes' => $this->scanBulkChanges(),
            'refund_anomalies' => $this->scanRefundAnomalies(),
            'privilege_changes' => $this->scanPrivilegeChanges(),
        ];
        return ['signals' => $created, 'total' => array_sum($created)];
    }

    private function scanSuspiciousLogins(): int
    {
        $limits = (new AdminLoginAttemptService())->limits();
        $stmt = $this->db->prepare(
            'SELECT identifier, ip_address, COUNT(*) AS attempts,
                    MAX(created_at) AS last_seen
             FROM login_attempts
             WHERE status IN ("failed", "blocked", "rate_limited")
               AND created_at >= DATE_SUB(NOW(), INTERVAL :window_seconds SECOND)
             GROUP BY identifier, ip_address
             HAVING COUNT(*) >= :threshold'
        );
        $stmt->bindValue(':window_seconds', (int)$limits['identifier_window'], PDO::PARAM_INT);
        $stmt->bindValue(':threshold', (int)$limits['identifier'], PDO::PARAM_INT);
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $identifierHash = hash('sha256', (string)$row['identifier']);
            $ipHash = hash('sha256', (string)$row['ip_address']);
            $this->events->record(
                'security.suspicious_login',
                'warning',
                'login_identity',
                $identifierHash,
                'تلاش‌های ناموفق ورود از یک IP از حد Rate Limit عبور کرده‌اند.',
                ['attempt_count' => (int)$row['attempts'], 'window_seconds' => (int)$limits['identifier_window'], 'ip_hash' => $ipHash, 'last_seen' => $row['last_seen'], 'scan_bucket' => (string)(int)(time() / 600)]
            );
            $count++;
        }
        return $count;
    }

    private function scanAdminBehavior(): int
    {
        $stmt = $this->db->prepare(
            'SELECT admin_user_id, COUNT(*) AS action_count, MAX(created_at) AS last_seen
             FROM audit_logs
             WHERE admin_user_id IS NOT NULL
               AND created_at >= DATE_SUB(NOW(), INTERVAL ' . self::ADMIN_ACTION_WINDOW_MINUTES . ' MINUTE)
             GROUP BY admin_user_id
             HAVING COUNT(*) >= ' . self::ADMIN_ACTION_THRESHOLD
        );
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)$row['admin_user_id'];
            $this->events->record(
                'security.unusual_admin_activity',
                'warning',
                'admin_user',
                (string)$id,
                'حجم فعالیت ثبت‌شده مدیر در بازه کوتاه از آستانه بررسی عبور کرده است.',
                ['action_count' => (int)$row['action_count'], 'window_minutes' => self::ADMIN_ACTION_WINDOW_MINUTES, 'threshold' => self::ADMIN_ACTION_THRESHOLD, 'last_seen' => $row['last_seen'], 'scan_bucket' => (string)(int)(time() / 600)]
            );
            $count++;
        }
        return $count;
    }

    private function scanBulkChanges(): int
    {
        $stmt = $this->db->prepare(
            'SELECT admin_user_id, COUNT(*) AS action_count, MAX(created_at) AS last_seen
             FROM audit_logs
             WHERE admin_user_id IS NOT NULL
               AND module = "bulk_operations"
               AND action IN ("execute", "bulk_update", "bulk_delete")
               AND created_at >= DATE_SUB(NOW(), INTERVAL ' . self::BULK_ACTION_WINDOW_MINUTES . ' MINUTE)
             GROUP BY admin_user_id
             HAVING COUNT(*) >= ' . self::BULK_ACTION_THRESHOLD
        );
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)$row['admin_user_id'];
            $this->events->record(
                'security.unusual_bulk_changes',
                'warning',
                'admin_user',
                (string)$id,
                'تعداد عملیات گروهی ثبت‌شده مدیر از آستانه بررسی عبور کرده است.',
                ['operation_count' => (int)$row['action_count'], 'window_minutes' => self::BULK_ACTION_WINDOW_MINUTES, 'threshold' => self::BULK_ACTION_THRESHOLD, 'last_seen' => $row['last_seen'], 'scan_bucket' => (string)(int)(time() / 600)]
            );
            $count++;
        }
        return $count;
    }

    private function scanRefundAnomalies(): int
    {
        $stmt = $this->db->prepare(
            'SELECT p.id AS payment_id, p.order_id, p.amount AS payment_amount,
                    SUM(CASE WHEN r.status IN ("pending", "processing", "successful") THEN CAST(r.amount AS DECIMAL(20,4)) ELSE 0 END) AS refund_total,
                    COUNT(r.id) AS refund_count
             FROM payments p
             INNER JOIN refunds r ON r.payment_id = p.id
             GROUP BY p.id, p.order_id, p.amount
             HAVING refund_total > CAST(p.amount AS DECIMAL(20,4))'
        );
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $paymentId = (int)$row['payment_id'];
            $this->events->record(
                'security.refund_total_exceeds_payment',
                'critical',
                'payment',
                (string)$paymentId,
                'مجموع بازپرداخت‌های باز یا موفق از مبلغ پرداخت اصلی بیشتر است.',
                ['order_id' => (int)$row['order_id'], 'payment_amount' => (string)$row['payment_amount'], 'refund_total' => (string)$row['refund_total'], 'refund_count' => (int)$row['refund_count']]
            );
            $count++;
        }
        return $count;
    }

    private function scanPrivilegeChanges(): int
    {
        $stmt = $this->db->prepare(
            'SELECT id, admin_user_id, action, entity_type, entity_id, created_at
             FROM audit_logs
             WHERE action IN ("assign_roles", "permission_changed", "permissions_updated", "role_permissions_updated")
               AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
             ORDER BY id DESC
             LIMIT 200'
        );
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $actorId = (int)($row['admin_user_id'] ?? 0);
            $this->events->record(
                'security.privilege_change_review',
                'warning',
                'audit_log',
                (string)$row['id'],
                'تغییر سطح دسترسی برای بازبینی امنیتی علامت‌گذاری شد.',
                ['source_record_id' => (int)$row['id'], 'actor_admin_id' => $actorId > 0 ? $actorId : null, 'entity_type' => (string)$row['entity_type'], 'entity_id' => (string)$row['entity_id'], 'action' => (string)$row['action'], 'created_at' => $row['created_at']]
            );
            $count++;
        }
        return $count;
    }

    public function recent(int $limit = 100, string $status = ''): array
    {
        return $this->events->recent($limit, $status);
    }

    public function review(int $eventId, int $adminId, string $note): bool
    {
        if (mb_strlen(trim($note)) > 1000) throw new InvalidArgumentException('یادداشت بازبینی بیش از حد مجاز است.');
        return $this->events->review($eventId, $adminId, $note);
    }
}
