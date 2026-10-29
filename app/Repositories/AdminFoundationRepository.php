<?php

declare(strict_types=1);

/**
 * Persistence primitives for the additive Admin Panel foundation tables.
 * These methods are intentionally not exposed as routes by themselves.
 */
final class AdminFoundationRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function createContentRevision(
        string $entityType,
        string $entityId,
        string $status,
        string $snapshotJson,
        int $createdByAdminId,
        ?string $scheduledAt = null
    ): int {
        $this->db->beginTransaction();

        try {
            $versionStmt = $this->db->prepare(
                'SELECT COALESCE(MAX(version_number), 0) + 1
                 FROM admin_content_revisions
                 WHERE entity_type = :entity_type
                   AND entity_id = :entity_id
                 FOR UPDATE'
            );
            $versionStmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
            $version = (int) $versionStmt->fetchColumn();

            $stmt = $this->db->prepare(<<<'SQL'
                INSERT INTO admin_content_revisions (
                    entity_type, entity_id, version_number, status,
                    snapshot_json, scheduled_at, published_at, created_by_admin_id
                ) VALUES (
                    :entity_type, :entity_id, :version_number, :status,
                    :snapshot_json, :scheduled_at,
                    CASE WHEN :published_status = 'published' THEN CURRENT_TIMESTAMP ELSE NULL END,
                    :created_by_admin_id
                )
                SQL
            );
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'version_number' => max(1, $version),
                'status' => $status,
                'snapshot_json' => $snapshotJson,
                'scheduled_at' => $scheduledAt,
                'published_status' => $status,
                'created_by_admin_id' => $createdByAdminId,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function saveView(
        int $adminUserId,
        string $moduleKey,
        string $name,
        string $filtersJson,
        ?string $sortJson,
        bool $shared
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_saved_views (
                admin_user_id, module_key, name, filters_json, sort_json, is_shared
            ) VALUES (
                :admin_user_id, :module_key, :name, :filters_json, :sort_json, :is_shared
            )'
        );
        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'module_key' => $moduleKey,
            'name' => $name,
            'filters_json' => $filtersJson,
            'sort_json' => $sortJson,
            'is_shared' => $shared ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function enqueueOperationJob(
        string $jobType,
        string $payloadJson,
        int $createdByAdminId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_operation_jobs (
                job_type, status, payload_json, created_by_admin_id
            ) VALUES (
                :job_type, :status, :payload_json, :created_by_admin_id
            )'
        );
        $stmt->execute([
            'job_type' => $jobType,
            'status' => 'queued',
            'payload_json' => $payloadJson,
            'created_by_admin_id' => $createdByAdminId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateOperationJob(
        int $jobId,
        string $status,
        int $progressPercent,
        ?string $resultJson = null,
        ?string $errorSummary = null
    ): bool {
        $stmt = $this->db->prepare(<<<'SQL'
            UPDATE admin_operation_jobs
             SET status = :status,
                 progress_percent = :progress_percent,
                 result_json = :result_json,
                 error_summary = :error_summary,
                 started_at = CASE
                    WHEN :started_status = 'running'
                    THEN COALESCE(started_at, CURRENT_TIMESTAMP)
                    ELSE started_at
                 END,
                 completed_at = CASE
                    WHEN :completed_status IN ('completed', 'failed', 'cancelled')
                    THEN CURRENT_TIMESTAMP
                    ELSE completed_at
                 END
             WHERE id = :id
            SQL
        );
        $stmt->execute([
            'status' => $status,
            'progress_percent' => max(0, min(100, $progressPercent)),
            'result_json' => $resultJson,
            'error_summary' => $errorSummary,
            'started_status' => $status,
            'completed_status' => $status,
            'id' => $jobId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function recordIncident(
        string $incidentKey,
        string $severity,
        string $source,
        string $title,
        ?string $description,
        ?string $contextJson
    ): int {
        $stmt = $this->db->prepare(<<<'SQL'
            INSERT INTO admin_system_incidents (
                incident_key, severity, status, source, title,
                description, context_json, occurrence_count,
                first_seen_at, last_seen_at
            ) VALUES (
                :incident_key, :severity, :status, :source, :title,
                :description, :context_json, 1,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
            ON DUPLICATE KEY UPDATE
                severity = VALUES(severity),
                status = IF(status = 'resolved', 'open', status),
                title = VALUES(title),
                description = VALUES(description),
                context_json = VALUES(context_json),
                occurrence_count = occurrence_count + 1,
                last_seen_at = CURRENT_TIMESTAMP
            SQL
        );
        $stmt->execute([
            'incident_key' => $incidentKey,
            'severity' => $severity,
            'status' => 'open',
            'source' => $source,
            'title' => $title,
            'description' => $description,
            'context_json' => $contextJson,
        ]);

        $lookup = $this->db->prepare(
            'SELECT id FROM admin_system_incidents
             WHERE incident_key = :incident_key LIMIT 1'
        );
        $lookup->execute(['incident_key' => $incidentKey]);

        return (int) $lookup->fetchColumn();
    }

    public function createApprovalRequest(
        string $actionKey,
        string $entityType,
        string $entityId,
        int $requesterAdminId,
        string $payloadJson,
        ?string $beforeSnapshotJson
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_approval_requests (
                action_key, entity_type, entity_id, status,
                requested_by_admin_id, request_payload_json, before_snapshot_json
            ) VALUES (
                :action_key, :entity_type, :entity_id, :status,
                :requested_by_admin_id, :request_payload_json, :before_snapshot_json
            )'
        );
        $stmt->execute([
            'action_key' => $actionKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => 'pending',
            'requested_by_admin_id' => $requesterAdminId,
            'request_payload_json' => $payloadJson,
            'before_snapshot_json' => $beforeSnapshotJson,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function decideApprovalRequest(
        int $requestId,
        int $reviewerAdminId,
        string $decision,
        ?string $decisionNote
    ): bool {
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            return false;
        }

        $stmt = $this->db->prepare(<<<'SQL'
            UPDATE admin_approval_requests
             SET status = :status,
                 reviewed_by_admin_id = :reviewed_by_admin_id,
                 decision_note = :decision_note,
                 reviewed_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND status = 'pending'
               AND requested_by_admin_id <> :requester_guard
            SQL
        );
        $stmt->execute([
            'status' => $decision,
            'reviewed_by_admin_id' => $reviewerAdminId,
            'decision_note' => $decisionNote,
            'id' => $requestId,
            'requester_guard' => $reviewerAdminId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function createMediaAsset(
        string $storageKey,
        string $originalName,
        string $mimeType,
        int $fileSizeBytes,
        string $sha256,
        ?string $altText,
        int $uploadedByAdminId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_media_assets (
                storage_key, original_name, mime_type, file_size_bytes,
                sha256, alt_text, uploaded_by_admin_id
            ) VALUES (
                :storage_key, :original_name, :mime_type, :file_size_bytes,
                :sha256, :alt_text, :uploaded_by_admin_id
            )'
        );
        $stmt->execute([
            'storage_key' => $storageKey,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size_bytes' => $fileSizeBytes,
            'sha256' => $sha256,
            'alt_text' => $altText,
            'uploaded_by_admin_id' => $uploadedByAdminId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createSupportTicket(
        string $ticketKey,
        ?int $customerId,
        string $priority,
        string $subject,
        string $description,
        ?int $assignedAdminId,
        ?int $createdByAdminId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_support_tickets (
                ticket_key, customer_id, status, priority, subject,
                description, assigned_admin_id, created_by_admin_id
            ) VALUES (
                :ticket_key, :customer_id, :status, :priority, :subject,
                :description, :assigned_admin_id, :created_by_admin_id
            )'
        );
        $stmt->execute([
            'ticket_key' => $ticketKey,
            'customer_id' => $customerId,
            'status' => 'open',
            'priority' => $priority,
            'subject' => $subject,
            'description' => $description,
            'assigned_admin_id' => $assignedAdminId,
            'created_by_admin_id' => $createdByAdminId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function recordAnalyticsEvent(
        string $eventKey,
        ?string $entityType,
        int|string|null $entityId,
        ?int $actorAdminId,
        ?float $numericValue,
        ?string $dimensionsJson
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_analytics_events (
                event_key, entity_type, entity_id, actor_admin_id,
                value_numeric, dimensions_json
            ) VALUES (
                :event_key, :entity_type, :entity_id, :actor_admin_id,
                :value_numeric, :dimensions_json
            )'
        );
        $stmt->execute([
            'event_key' => $eventKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'actor_admin_id' => $actorAdminId,
            'value_numeric' => $numericValue,
            'dimensions_json' => $dimensionsJson,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
