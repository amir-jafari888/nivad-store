<?php

declare(strict_types=1);

/**
 * Pure validation for bulk job requests. It does not verify approval state;
 * that must be checked against the database immediately before execution.
 */
final class AdminBulkOperationPolicy
{
    public function validate(
        string $jobType,
        array $payload,
        bool $confirmed,
        ?int $approvalRequestId = null
    ): array {
        $jobType = strtolower(trim($jobType));
        $definition = AdminBulkOperationRegistry::find($jobType);

        if ($definition === null) {
            throw new InvalidArgumentException('Bulk operation type is not allowlisted.');
        }

        if (!$confirmed) {
            throw new InvalidArgumentException('Bulk operations require explicit confirmation.');
        }

        $ids = $payload['ids'] ?? null;
        if (!is_array($ids) || $ids === []) {
            throw new InvalidArgumentException('Bulk operations require a non-empty IDs list.');
        }

        $normalizedIds = [];
        foreach ($ids as $id) {
            if ((!is_int($id) && !(is_string($id) && ctype_digit($id))) || (int) $id < 1) {
                throw new InvalidArgumentException('Bulk operation IDs must be positive integers.');
            }
            $normalizedIds[] = (int) $id;
        }

        $normalizedIds = array_values(array_unique($normalizedIds));
        if (count($normalizedIds) > $definition['max_items']) {
            throw new InvalidArgumentException('Bulk operation exceeds the configured batch limit.');
        }

        if ($definition['requires_approval']) {
            if ($approvalRequestId === null || $approvalRequestId < 1) {
                throw new InvalidArgumentException('This bulk operation requires an approval request.');
            }
        } elseif ($approvalRequestId !== null && $approvalRequestId < 1) {
            throw new InvalidArgumentException('Invalid approval request ID.');
        }

        unset($payload['ids']);
        $safePayload = SensitiveDataRedactor::redact($payload);
        $safePayload['ids'] = $normalizedIds;
        $safePayload['confirmed'] = true;

        if ($approvalRequestId !== null) {
            $safePayload['approval_request_id'] = $approvalRequestId;
        }

        return [
            'job_type' => $jobType,
            'feature_id' => $definition['feature_id'],
            'requires_approval' => $definition['requires_approval'],
            'payload' => $safePayload,
            'approval_verification_required_at_execution' => $definition['requires_approval'],
        ];
    }
}
