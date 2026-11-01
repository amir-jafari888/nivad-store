<?php

declare(strict_types=1);

$root = __DIR__;
$checks = 0;
$failures = 0;
function phase9Check(string $name, bool $condition): void
{
    global $checks, $failures;
    $checks++;
    echo ($condition ? '<span style="color:green">PASS</span>' : '<span style="color:red">FAIL</span>') . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>';
    if (!$condition) $failures++;
}
$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    return is_string($content) ? $content : '';
};
$migration = $read('database/migrations/20261101_admin_security_operations_phase9.sql');
$repository = $read('app/Repositories/AdminSecurityEventRepository.php');
$service = $read('app/Services/AdminSecurityOperationsService.php');
$controller = $read('app/Controllers/AdminSecurityOperationsController.php');
$routes = $read('routes/api.php');
$map = $read('app/Core/AdminRoutePermissionMap.php');
$return = $read('app/Controllers/ReturnController.php');
$page = $read('admin/security-events/index.php');
$policy = $read('app/Services/AdminSecurityEventPolicy.php');

phase9Check('Security event storage is additive and has unique fingerprint', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_security_events') && str_contains($migration, 'UNIQUE KEY uq_admin_security_event_fingerprint'));
phase9Check('Security event snapshots are redacted before persistence', str_contains($repository, 'SensitiveDataRedactor::redact'));
phase9Check('Repeated detections are deduplicated and counted', str_contains($repository, 'ON DUPLICATE KEY UPDATE') && str_contains($repository, 'occurrence_count = occurrence_count + 1'));
phase9Check('Suspicious login scan uses existing login_attempts and throttling limits', str_contains($service, 'FROM login_attempts') && str_contains($service, 'AdminLoginAttemptService())->limits()'));
phase9Check('Admin behavior detection uses actual audit_logs', str_contains($service, 'FROM audit_logs') && str_contains($service, 'ADMIN_ACTION_THRESHOLD'));
phase9Check('Bulk-change detection is scoped to explicit bulk operation audit events', str_contains($service, 'module = "bulk_operations"') && str_contains($service, 'BULK_ACTION_THRESHOLD'));
phase9Check('Refund anomaly detection compares committed refund totals to payment amount', str_contains($service, 'INNER JOIN refunds r ON r.payment_id = p.id') && str_contains($service, 'HAVING refund_total > CAST(p.amount'));
phase9Check('Privilege changes are linked to source audit records', str_contains($service, 'security.privilege_change_review') && str_contains($service, 'source_record_id'));
phase9Check('Security signals are explicitly review prompts, not fraud verdicts', str_contains($service, 'automated fraud verdicts'));
phase9Check('Security events can be reviewed with actor and note', str_contains($repository, 'reviewed_by_admin_id') && str_contains($repository, 'review_note'));
phase9Check('Security API routes are registered behind Admin middleware', str_contains($routes, 'AdminSecurityOperationsController::class') && str_contains($routes, '$adminCapabilityRead') && str_contains($routes, '$adminCapabilityWrite'));
phase9Check('Security routes map to dedicated view/manage permissions', str_contains($map, "'admin.security-events.index' => 'admin.security_events.view'") && str_contains($map, "'admin.security-events.scan' => 'admin.security_events.manage'"));
phase9Check('Refund creation requires current-password re-authentication', str_contains($return, "'refund.create'") && str_contains($return, 'AdminReauthenticationService'));
phase9Check('Refund status changes require re-authentication and are audited', str_contains($return, "'refund.status_change'") && str_contains($return, 'refund_status_change'));
phase9Check('Security UI enforces view permission and CSRF for scan/review actions', str_contains($page, "admin.security_events.view") && str_contains($page, 'csrf-token') && str_contains($page, 'X-CSRF-TOKEN'));
phase9Check('Canonical security signal policies include all implemented signal types', str_contains($policy, 'security.suspicious_login') && str_contains($policy, 'security.refund_total_exceeds_payment'));

echo '<hr>Checks: ' . $checks . ' | Failures: ' . $failures . '<br>';
exit($failures === 0 ? 0 : 1);
