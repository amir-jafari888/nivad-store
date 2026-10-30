<?php

declare(strict_types=1);

/** Read models for incident timeline, frequency/severity analysis and known jobs. */
final class AdminIncidentDetectionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function timeline(int $incidentId, int $limit = 100): array
    {
        if ($incidentId < 1) return [];
        $limit = max(1, min(200, $limit));

        $exists = $this->db->prepare('SELECT 1 FROM admin_system_incidents WHERE id = :id LIMIT 1');
        $exists->execute(['id' => $incidentId]);
        if ($exists->fetchColumn() === false) return [];

        $stmt = $this->db->prepare(
            'SELECT id, incident_id, event_type, severity, source, request_id,
                    http_status, request_method, route_path, exception_type,
                    file_basename, line_number, context_json, occurred_at
             FROM admin_incident_events
             WHERE incident_id = :incident_id
             ORDER BY occurred_at DESC, id DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function analysis(int $hours = 24): array
    {
        $hours = max(1, min(720, $hours));
        $window = 'DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . $hours . ' HOUR)';

        $bySource = $this->db->query(
            'SELECT source, severity, COUNT(*) AS event_count,
                    COUNT(DISTINCT incident_id) AS incident_count,
                    MAX(occurred_at) AS latest_at
             FROM admin_incident_events
             WHERE occurred_at >= ' . $window . '
             GROUP BY source, severity
             ORDER BY event_count DESC, latest_at DESC LIMIT 100'
        )->fetchAll(PDO::FETCH_ASSOC);

        $bySeverity = $this->db->query(
            'SELECT severity, COUNT(*) AS event_count,
                    COUNT(DISTINCT incident_id) AS incident_count
             FROM admin_incident_events
             WHERE occurred_at >= ' . $window . '
             GROUP BY severity
             ORDER BY FIELD(severity, "critical", "warning", "info")'
        )->fetchAll(PDO::FETCH_ASSOC);

        $topIncidents = $this->db->query(
            'SELECT id, source, severity, status, title, occurrence_count,
                    first_seen_at, last_seen_at
             FROM admin_system_incidents
             WHERE last_seen_at >= ' . $window . '
             ORDER BY occurrence_count DESC, last_seen_at DESC LIMIT 20'
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'window_hours' => $hours,
            'by_source' => $bySource,
            'by_severity' => $bySeverity,
            'top_incidents' => $topIncidents,
        ];
    }

    public function jobHealth(): array
    {
        $queues = [
            'admin_operation_jobs' => ['label' => 'Admin operation jobs', 'active' => ['queued', 'running']],
            'admin_email_outbox' => ['label' => 'Admin email outbox', 'active' => ['pending', 'processing']],
        ];
        $results = [];

        foreach ($queues as $table => $definition) {
            if (!$this->tableExists($table)) {
                $results[] = [
                    'table' => $table, 'label' => $definition['label'],
                    'state' => 'missing', 'statuses' => [], 'stale_count' => null,
                ];
                continue;
            }

            $statuses = $this->db->query(
                'SELECT status, COUNT(*) AS total FROM ' . $table . ' GROUP BY status'
            )->fetchAll(PDO::FETCH_ASSOC);
            $active = '"' . implode('","', $definition['active']) . '"';
            $stale = $this->db->query(
                'SELECT COUNT(*) FROM ' . $table . '
                 WHERE status IN (' . $active . ')
                   AND created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)'
            )->fetchColumn();

            $results[] = [
                'table' => $table, 'label' => $definition['label'],
                'state' => 'available', 'statuses' => $statuses, 'stale_count' => (int) $stale,
            ];
        }

        return [
            'generic_queue_found' => false,
            'scope' => 'Only queues explicitly present in the Admin foundation schema are monitored.',
            'monitored_queues' => $results,
        ];
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table_name'
        );
        $stmt->execute(['table_name' => $table]);
        return (int) $stmt->fetchColumn() === 1;
    }
}
