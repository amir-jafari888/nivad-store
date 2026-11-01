<?php

declare(strict_types=1);

final class AdminSecurityEventRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function record(string $eventKey, string $severity, string $subjectType, string $subjectId, string $summary, array $context = []): int
    {
        $fingerprint = hash('sha256', implode('|', [$eventKey, $subjectType, $subjectId, (string)($context['source_record_id'] ?? $context['scan_bucket'] ?? '')]));
        $safeContext = SensitiveDataRedactor::redact($context);
        $json = $safeContext !== [] ? json_encode($safeContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) : null;
        $stmt = $this->db->prepare(
            'INSERT INTO admin_security_events
                (fingerprint, event_key, severity, subject_type, subject_id, summary, context_json)
             VALUES
                (:fingerprint, :event_key, :severity, :subject_type, :subject_id, :summary, :context_json)
             ON DUPLICATE KEY UPDATE
                occurrence_count = occurrence_count + 1,
                last_seen_at = CURRENT_TIMESTAMP,
                severity = VALUES(severity),
                summary = VALUES(summary),
                context_json = VALUES(context_json),
                status = IF(status = "resolved", "open", status),
                updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'fingerprint' => $fingerprint,
            'event_key' => $eventKey,
            'severity' => $severity,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'summary' => mb_substr($summary, 0, 255),
            'context_json' => $json,
        ]);
        $find = $this->db->prepare('SELECT id FROM admin_security_events WHERE fingerprint = :fingerprint LIMIT 1');
        $find->execute(['fingerprint' => $fingerprint]);
        return (int)$find->fetchColumn();
    }

    public function recent(int $limit = 100, string $status = ''): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT e.*, COALESCE(NULLIF(TRIM(CONCAT_WS(" ", a.first_name, a.last_name)), ""), "—") AS reviewer_name
                FROM admin_security_events e
                LEFT JOIN admin_users a ON a.id = e.reviewed_by_admin_id';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE e.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY FIELD(e.severity, "critical", "warning", "info"), e.last_seen_at DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function review(int $id, int $adminId, string $note): bool
    {
        if ($id < 1 || $adminId < 1) return false;
        $stmt = $this->db->prepare(
            'UPDATE admin_security_events
             SET status = "reviewed", reviewed_by_admin_id = :admin_id,
                 reviewed_at = CURRENT_TIMESTAMP, review_note = :note
             WHERE id = :id AND status IN ("open", "reviewed")'
        );
        $stmt->execute(['admin_id' => $adminId, 'note' => mb_substr(trim($note), 0, 1000), 'id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
