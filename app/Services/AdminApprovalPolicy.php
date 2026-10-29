<?php

/** Non-DB approval workflow decision contract. */
final class AdminApprovalPolicy
{
    public static function validateRequest(array $input): array
    {
        $operation = trim((string)($input['operation'] ?? ''));
        $reason = trim((string)($input['reason'] ?? ''));
        $targetId = (int)($input['target_id'] ?? 0);
        if ($operation === '' || mb_strlen($operation) > 120 || $reason === '' || mb_strlen($reason) > 1000 || $targetId <= 0) {
            throw new InvalidArgumentException('Approval request fields are invalid.');
        }
        return ['operation' => $operation, 'reason' => $reason, 'target_id' => $targetId];
    }

    public static function validateDecision(int $requestId, int $reviewerId, string $decision, string $reason = ''): array
    {
        if ($requestId <= 0 || $reviewerId <= 0) {
            throw new InvalidArgumentException('Approval identifiers must be positive integers.');
        }
        if (!in_array($decision, ['approve', 'reject'], true)) {
            throw new InvalidArgumentException('Unsupported approval decision.');
        }
        $reason = trim($reason);
        if ($decision === 'reject' && $reason === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }
        return ['request_id' => $requestId, 'reviewer_id' => $reviewerId, 'decision' => $decision, 'reason' => $reason];
    }
}
