<?php

declare(strict_types=1);

/**
 * Cross-module rules for operations that must never execute as casual CRUD.
 * This policy is intentionally pure (no database writes).
 */
final class AdminOperationPolicy
{
    private const SENSITIVE_ACTIONS = [
        'admin.disable',
        'admin.delete',
        'admin.role.assign',
        'admin.permission.assign',
        'admin.session.revoke_all',
        'catalog.bulk_delete',
        'inventory.stock_adjust',
        'order.cancel',
        'payment.refund',
        'refund.approve',
        'content.publish',
        'settings.security.update',
        'security.rotate_credentials',
        'bulk.execute',
        'backup.restore',
    ];

    public function requiresExplicitConfirmation(string $action): bool
    {
        return in_array($this->normalizeAction($action), self::SENSITIVE_ACTIONS, true);
    }

    public function requiresApproval(string $action): bool
    {
        $action = $this->normalizeAction($action);

        return in_array($action, [
            'admin.delete',
            'admin.permission.assign',
            'admin.session.revoke_all',
            'payment.refund',
            'refund.approve',
            'settings.security.update',
            'security.rotate_credentials',
            'bulk.execute',
            'backup.restore',
        ], true);
    }

    public function requiresSeparateReviewer(string $action): bool
    {
        return $this->requiresApproval($action);
    }

    public function validateExecutionContext(
        string $action,
        int $actorAdminId,
        bool $confirmed,
        ?int $approvalRequesterId = null,
        ?int $approvalReviewerId = null,
        ?int $approvalExpiresAt = null,
        ?int $now = null
    ): array {
        $action = $this->normalizeAction($action);
        $errors = [];

        if ($action === '') {
            $errors[] = 'action_required';
        }

        if ($actorAdminId < 1) {
            $errors[] = 'valid_actor_required';
        }

        if ($this->requiresExplicitConfirmation($action) && !$confirmed) {
            $errors[] = 'explicit_confirmation_required';
        }

        if ($this->requiresApproval($action)) {
            if ($approvalRequesterId === null || $approvalReviewerId === null) {
                $errors[] = 'approval_required';
            } elseif ($approvalRequesterId === $approvalReviewerId) {
                $errors[] = 'separate_reviewer_required';
            }

            $clock = $now ?? time();
            if ($approvalExpiresAt === null || $approvalExpiresAt <= $clock) {
                $errors[] = 'approval_expired_or_missing';
            }
        }

        return [
            'allowed' => $errors === [],
            'action' => $action,
            'errors' => array_values(array_unique($errors)),
            'requires_confirmation' => $this->requiresExplicitConfirmation($action),
            'requires_approval' => $this->requiresApproval($action),
            'requires_separate_reviewer' => $this->requiresSeparateReviewer($action),
        ];
    }

    public function sensitiveActions(): array
    {
        return self::SENSITIVE_ACTIONS;
    }

    private function normalizeAction(string $action): string
    {
        $action = strtolower(trim($action));

        return preg_match('/^[a-z0-9_.-]{1,120}$/', $action) === 1
            ? $action
            : '';
    }
}
