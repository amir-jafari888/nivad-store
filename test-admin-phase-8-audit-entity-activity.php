<?php

declare(strict_types=1);

$root = __DIR__;
$checks = 0;
$failures = 0;
function phase8Check(string $name, bool $condition): void
{
    global $checks, $failures;
    $checks++;
    echo ($condition ? '<span style="color:green">PASS</span>' : '<span style="color:red">FAIL</span>') . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>';
    if (!$condition) $failures++;
}
$repo = file_get_contents($root . '/app/Repositories/AuditLogRepository.php');
$service = file_get_contents($root . '/app/Services/AuditLogService.php');
$controller = file_get_contents($root . '/app/Controllers/AdminAuditController.php');
$routes = file_get_contents($root . '/routes/api.php');
$map = file_get_contents($root . '/app/Core/AdminRoutePermissionMap.php');
$page = file_get_contents($root . '/admin/audit-logs/index.php');
phase8Check('Entity timeline queries the existing audit_logs table', str_contains($repo, 'FROM audit_logs l') && str_contains($repo, 'l.entity_type = :entity_type') && str_contains($repo, 'l.entity_id = :entity_id'));
phase8Check('Entity timeline joins the actual Admin actor', str_contains($repo, 'LEFT JOIN admin_users a'));
phase8Check('Timeline pagination is bounded', str_contains($repo, 'min(100, $limit)') && str_contains($repo, 'before_id'));
phase8Check('Timeline omits IP and User-Agent fields', !str_contains(substr($repo, strpos($repo, 'public function entityTimeline'), strpos($repo, 'public function count()') - strpos($repo, 'public function entityTimeline')), 'ip_address') && !str_contains(substr($repo, strpos($repo, 'public function entityTimeline'), strpos($repo, 'public function count()') - strpos($repo, 'public function entityTimeline')), 'user_agent'));
phase8Check('Entity identifiers are validated', str_contains($service, 'Entity type is invalid') && str_contains($service, 'Entity ID is invalid'));
phase8Check('Before/after snapshots are redacted before API output', str_contains($service, 'SensitiveDataRedactor::redact'));
phase8Check('Controller returns timeline and pagination cursor', str_contains($controller, 'entityTimeline') && str_contains($controller, 'next_before_id'));
phase8Check('API route has authentication and permission middleware', str_contains($routes, 'AdminAuditController::class') && str_contains($routes, '$adminCapabilityRead'));
phase8Check('Route maps to existing admin.audit.view permission', str_contains($map, "'admin.audit.entities.timeline' => 'admin.audit.view'"));
phase8Check('Audit page enforces audit permission and exposes timeline UI', str_contains($page, "$adminAuth->can('admin.audit.view')") && str_contains($page, 'entity-timeline-form'));
echo '<hr>Checks: ' . $checks . ' | Failures: ' . $failures . '<br>';
exit($failures === 0 ? 0 : 1);
