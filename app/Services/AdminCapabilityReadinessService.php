<?php

declare(strict_types=1);

/**
 * Reports whether an Admin Panel command can be implemented without crossing
 * the database/external-service boundary. It performs no I/O and never opens
 * a DB connection.
 */
final class AdminCapabilityReadinessService
{
    /** @var array<string, string> */
    private const REQUIRED_CONTRACTS = [
        'ADM-005' => 'AdminAccountPolicy',
        'ADM-006' => 'AdminSessionService',
        'ADM-008' => 'AdminAuditSnapshotService',
        'ADM-016' => 'AdminNotificationPolicy',
        'ADM-019' => 'AdminFoundationService',
        'ADM-022' => 'AdminSupportTicketPolicy',
        'ADM-023' => 'AdminFoundationService',
        'ADM-030' => 'AdminGlobalSearchService',
        'ADM-031' => 'AdminBulkOperationPolicy',
        'ADM-032' => 'AdminFoundationService',
        'ADM-034' => 'AdminFoundationService',
        'ADM-039' => 'AdminOperationPolicy',
        'ADM-042' => 'AdminReportExportService',
    ];

    /** @return array<string, mixed> */
    public function validateContracts(): array
    {
        $errors = [];
        $domains = AdminCapabilityContractRegistry::all();

        foreach ($domains as $domain => $definition) {
            $featureId = $definition['feature_id'] ?? null;
            $feature = is_string($featureId) ? AdminFeatureRegistry::find($featureId) : null;

            if ($feature === null) {
                $errors[] = $domain . ': unknown feature ' . (string) $featureId;
                continue;
            }

            if (($feature['permission'] ?? null) !== ($definition['permission'] ?? null)) {
                $errors[] = $domain . ': permission mismatch';
            }

            foreach (($definition['operations'] ?? []) as $operation => $contract) {
                if (!is_array($contract)) {
                    $errors[] = $domain . '.' . $operation . ': invalid operation contract';
                    continue;
                }

                if (!in_array($contract['kind'] ?? null, ['read', 'write'], true)) {
                    $errors[] = $domain . '.' . $operation . ': invalid operation kind';
                }

                if (!is_bool($contract['database_required'] ?? null)) {
                    $errors[] = $domain . '.' . $operation . ': database_required must be boolean';
                }

                if (!is_bool($contract['audit_required'] ?? null)) {
                    $errors[] = $domain . '.' . $operation . ': audit_required must be boolean';
                }

                $permission = $contract['permission'] ?? null;
                if ($permission !== null && !is_string($permission)) {
                    $errors[] = $domain . '.' . $operation . ': permission must be a string or null';
                }
            }
        }

        foreach (self::REQUIRED_CONTRACTS as $featureId => $className) {
            if (AdminFeatureRegistry::find($featureId) === null) {
                $errors[] = 'required contract references unknown feature ' . $featureId;
            }

            if (!class_exists($className)) {
                $errors[] = 'required non-DB contract class missing: ' . $className;
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'domain_count' => count($domains),
            'operation_count' => array_sum(
                array_map(
                    static fn(array $definition): int => count($definition['operations'] ?? []),
                    $domains
                )
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function status(string $domain, string $operation): array
    {
        $contract = AdminCapabilityContractRegistry::getOperation($domain, $operation);
        if ($contract === null) {
            return [
                'status' => 'unknown',
                'domain' => $domain,
                'operation' => $operation,
                'database_required' => null,
            ];
        }

        return [
            'status' => $contract['database_required'] ? 'database_blocked' : 'non_database_ready',
            'domain' => $domain,
            'operation' => $operation,
            'database_required' => $contract['database_required'],
            'audit_required' => $contract['audit_required'],
            'permission' => $contract['permission'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function nonDatabaseOperations(): array
    {
        $ready = [];

        foreach (AdminCapabilityContractRegistry::all() as $domain => $definition) {
            foreach ($definition['operations'] as $operation => $contract) {
                if ($contract['database_required'] === false) {
                    $ready[] = [
                        'domain' => $domain,
                        'operation' => $operation,
                        'feature_id' => $definition['feature_id'],
                        'permission' => $contract['permission'] ?? $definition['permission'],
                    ];
                }
            }
        }

        return $ready;
    }
}
