<?php

declare(strict_types=1);

/**
 * Admin Phase 7 — Support Center static contract suite.
 * Read-only: no database writes, network requests, or runtime claims.
 * Run: php test-admin-phase-7-support-center.php
 */

$passed = 0;
$failed = 0;
$root = __DIR__;

function p7Read(string $relative): string
{
    global $root;
    $path = $root . '/' . $relative;
    return is_file($path) ? (string)file_get_contents($path) : '';
}

function p7Check(string $id, string $label, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
        return;
    }
    $failed++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

$roadmap = p7Read('ADMIN_PANEL_ROADMAP.md');
$migration = p7Read('database/migrations/20261031_admin_support_phase7.sql');
$verification = p7Read('database/verification/20261031_admin_support_phase7_verify.sql');
$repository = p7Read('app/Repositories/AdminSupportRepository.php');
$service = p7Read('app/Services/AdminSupportService.php');
$capRepository = p7Read('app/Repositories/AdminCapabilityRepository.php');
$capService = p7Read('app/Services/AdminCapabilityService.php');
$controller = p7Read('app/Controllers/AdminCapabilityController.php');
$routes = p7Read('routes/api.php');
$permissions = p7Read('app/Core/AdminRoutePermissionMap.php');
$page = p7Read('admin/capabilities/index.php');
$ui = p7Read('admin/assets/js/modules/capability-center.js');

$checks = [
    // Batch 01 — SLA
    ['07.01.01', 'Canonical roadmap declares Phase 7 Support Center', str_contains($roadmap, 'فاز ۷ — تکمیل Support Center')],
    ['07.01.02', 'Migration creates per-priority SLA policy storage', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_support_sla_policies')],
    ['07.01.03', 'SLA policies define first-response and resolution targets', str_contains($migration, 'first_response_minutes INT UNSIGNED NULL') && str_contains($migration, 'resolution_minutes INT UNSIGNED NULL')],
    ['07.01.04', 'Initial priorities are registered without invented SLA thresholds', str_contains($migration, "VALUES ('urgent', NULL, NULL, 0)")],
    ['07.01.05', 'Repository reads configured SLA policies', str_contains($repository, 'public function slaPolicies(): array')],
    ['07.01.06', 'Repository persists policy changes with an upsert', str_contains($repository, 'public function saveSlaPolicy') && str_contains($repository, 'ON DUPLICATE KEY UPDATE')],
    ['07.01.07', 'Service validates priority and positive bounded targets', str_contains($service, 'public function saveSlaPolicy') && str_contains($service, '$first < 1') && str_contains($service, '$resolution > 525600')],
    ['07.01.08', 'Resolution target cannot be shorter than first-response target', str_contains($service, '$resolution < $first')],
    ['07.01.09', 'SLA read/write endpoints have explicit support permissions', str_contains($permissions, "'admin.capabilities.support.sla.index' => 'admin.support.view'") && str_contains($permissions, "'admin.capabilities.support.sla.update' => 'admin.support.manage'")],
    ['07.01.10', 'Admin UI exposes configurable SLA values and warns when unset', str_contains($page, 'data-capability-form="support-sla"') && str_contains($ui, 'تعریف‌نشده')],

    // Batch 02 — Response Metrics
    ['07.02.01', 'Support repository exposes date-bounded metrics', str_contains($repository, 'public function metrics(string $from, string $to): array')],
    ['07.02.02', 'First response is derived from persisted support messages', str_contains($repository, 'MIN(created_at) AS first_response_at')],
    ['07.02.03', 'Internal notes are excluded from public response timing', str_contains($repository, 'sender_type = "admin" AND is_internal = 0')],
    ['07.02.04', 'Customer response timing excludes admin-created tickets', str_contains($repository, 't.user_id IS NOT NULL AND first_reply.first_response_at IS NOT NULL')],
    ['07.02.05', 'Average first-response time is calculated in elapsed minutes', str_contains($repository, 'AS avg_first_response_minutes') && str_contains($repository, 'TIMESTAMPDIFF(SECOND')],
    ['07.02.06', 'Unanswered active customer tickets are counted separately', str_contains($repository, 'AS awaiting_first_response')],
    ['07.02.07', 'Response SLA success and breach counts are calculated', str_contains($repository, 'AS response_within_sla') && str_contains($repository, 'AS response_breached')],
    ['07.02.08', 'Resolution SLA success and breach counts are calculated', str_contains($repository, 'AS resolution_within_sla') && str_contains($repository, 'AS resolution_breached')],
    ['07.02.09', 'Metrics endpoint validates the reporting date range', str_contains($controller, 'supportMetrics') && str_contains($service, 'private function validateRange')],
    ['07.02.10', 'Admin UI renders response and resolution SLA metrics', str_contains($ui, 'loadSupportMetrics') && str_contains($ui, 'resolution_within_sla')],

    // Batch 03 — Advanced Filters
    ['07.03.01', 'Ticket listing supports status filter', str_contains($service, "'status', 'priority'") && str_contains($repository, "'status' => 't.status'")],
    ['07.03.02', 'Ticket listing supports priority filter', str_contains($repository, "'priority' => 't.priority'")],
    ['07.03.03', 'Ticket listing supports assigned-admin filter', str_contains($repository, "'assigned_admin_id' => 't.assigned_admin_id'")],
    ['07.03.04', 'Ticket listing supports customer filter', str_contains($repository, "'user_id' => 't.user_id'")],
    ['07.03.05', 'Ticket listing supports a start date', str_contains($repository, "$" . "filters['date_from']") && str_contains($repository, 't.created_at >= :date_from')],
    ['07.03.06', 'Ticket listing supports an inclusive end date', str_contains($repository, 'DATE_ADD(:date_to, INTERVAL 1 DAY)')],
    ['07.03.07', 'Ticket search matches ticket number', str_contains($repository, 't.ticket_number LIKE :search_number')],
    ['07.03.08', 'Ticket search matches subject', str_contains($repository, 't.subject LIKE :search_subject')],
    ['07.03.09', 'Ticket search supports exact numeric ID', str_contains($repository, 'CAST(t.id AS CHAR) = :search_id')],
    ['07.03.10', 'Filter values are validated and result limits are bounded', str_contains($service, 'فیلتر وضعیت یا اولویت') && str_contains($repository, 'min(200, (int)($filters[\'limit\'] ?? 100))')],

    // Batch 04 — Status History
    ['07.04.01', 'Migration creates append-only support status history storage', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_support_status_history')],
    ['07.04.02', 'Status history stores previous and next status', str_contains($migration, 'from_status VARCHAR(20) NULL') && str_contains($migration, 'to_status VARCHAR(20) NOT NULL')],
    ['07.04.03', 'Repository exposes ordered status history', str_contains($repository, 'public function statusHistory(int $ticketId): array')],
    ['07.04.04', 'Ticket detail includes its status history', str_contains($repository, "\$ticket['status_history'] = \$this->statusHistory(\$id)")],
    ['07.04.05', 'Admin-created tickets record their initial status', str_contains($capRepository, 'Ticket created') && str_contains($capRepository, 'actor_type, actor_admin_id, note')],
    ['07.04.06', 'Customer-created tickets record their initial status', str_contains($capRepository, 'actor_type, actor_user_id, note)') && str_contains($capRepository, '"customer", :actor_user_id, "Ticket created"')],
    ['07.04.07', 'Admin status updates lock and read the prior state transactionally', str_contains($capRepository, 'SELECT status FROM admin_support_tickets WHERE id = :id LIMIT 1 FOR UPDATE')],
    ['07.04.08', 'Admin status transitions persist old and new status with actor', str_contains($capRepository, 'if ($previousStatus !== $status)') && str_contains($capRepository, "'actor_admin_id' => " . '$actorAdminId')],
    ['07.04.09', 'Customer replies record automatic pending-admin transitions', str_contains($capRepository, 'Customer replied') && str_contains($capRepository, '"pending_admin"')],
    ['07.04.10', 'Migration explicitly avoids fabricating historical status events', str_contains($migration, 'No historical status transitions are fabricated')],

    // Batch 05 — Support Reports
    ['07.05.01', 'Support service exposes bounded reports', str_contains($service, 'public function reports(string $from, string $to): array')],
    ['07.05.02', 'Reports group ticket volume by status and priority', str_contains($repository, 'GROUP BY status, priority')],
    ['07.05.03', 'Reports include an assignee performance breakdown', str_contains($repository, 'public function reports') && str_contains($repository, 'AS assigned_admin_name')],
    ['07.05.04', 'Assignee reports include total ticket volume', str_contains($repository, 'AS ticket_count')],
    ['07.05.05', 'Assignee reports include completed ticket volume', str_contains($repository, 'AS completed_count')],
    ['07.05.06', 'Assignee reports include average first-response time', str_contains($repository, 'AS avg_first_response_minutes')],
    ['07.05.07', 'Assignee reports include average resolution time', str_contains($repository, 'AS avg_resolution_minutes')],
    ['07.05.08', 'Report date ranges are ordered and capped at one year', str_contains($service, '$from > $to') && str_contains($service, '366 * 86400')],
    ['07.05.09', 'Controller returns status-priority and assignee report series', str_contains($controller, "'by_assignee' => \$report['by_assignee']")],
    ['07.05.10', 'Admin UI renders status, priority and assignee reports', str_contains($ui, 'support-assignees') && str_contains($ui, 'loadSupportReports')],
];

foreach ($checks as [$id, $label, $condition]) {
    p7Check($id, $label, $condition);
}

echo '<strong>Phase 7 static contracts: ' . $passed . ' PASS / ' . $failed . ' FAIL / ' . count($checks) . " TOTAL</strong><br>\n";
exit($failed === 0 ? 0 : 1);
