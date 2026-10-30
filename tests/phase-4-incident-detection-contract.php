<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void {
    $checks[] = [$name, $ok];
    echo '<div style="color:' . ($ok ? 'green' : 'red') . '">' .
        ($ok ? 'PASS' : 'FAIL') . ' — ' .
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</div>' . PHP_EOL;
};
$read = static function (string $path) use ($root): string {
    $file = $root . '/' . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
};

$errorHandler = $read('app/Core/ErrorHandler.php');
$capture = $read('app/Services/AdminIncidentCaptureService.php');
$capRepo = $read('app/Repositories/AdminCapabilityRepository.php');
$detectRepo = $read('app/Repositories/AdminIncidentDetectionRepository.php');
$client = $read('app/Controllers/IncidentCaptureController.php');
$routes = $read('routes/api.php');
$permissions = $read('app/Core/AdminRoutePermissionMap.php');
$migration = $read('database/migrations/20261030_admin_incident_detection_phase4.sql');
$js = $read('assets/js/modules/client-error-monitor.js');
$main = $read('assets/js/main.js');

$check('PHP exception handler remains registered', str_contains($errorHandler, 'set_exception_handler'));
$check('PHP warning and notice conversion remains enabled', str_contains($errorHandler, 'set_error_handler'));
$check('Fatal shutdown handler is registered', str_contains($errorHandler, 'register_shutdown_function'));
$check('Fatal PHP errors are captured', str_contains($errorHandler, 'php.fatal'));
$check('Unhandled PHP exceptions are captured', str_contains($errorHandler, 'captureThrowable'));
$check('HTTP capture policy covers 404, 429 and 5xx', str_contains($capture, '$status === 404') && str_contains($capture, '$status === 429') && str_contains($capture, '$status >= 500'));
$check('PDO exceptions are classified as database failures', str_contains($capture, 'database.failure') && str_contains($capture, 'instanceof PDOException'));
$check('Capture recursion is guarded', str_contains($capture, 'private static bool $capturing'));
$check('Capture failures do not replace the original request', str_contains($capture, 'Incident capture failed.'));
$check('Context is redacted before persistence', str_contains($capture, 'SensitiveDataRedactor::redact'));
$check('Canonical foundation incident table is used', str_contains($capRepo, 'admin_system_incidents') && !str_contains($capRepo, 'FROM admin_incidents'));
$check('Occurrence count and last-seen are updated', str_contains($capRepo, 'occurrence_count = occurrence_count + 1') && str_contains($capRepo, 'last_seen_at = CURRENT_TIMESTAMP'));
$check('Incident grouping distinguishes route and exception context', str_contains($read('app/Services/AdminCapabilityService.php'), '$safeContext[\'route_path\']') && str_contains($read('app/Services/AdminCapabilityService.php'), '$safeContext[\'exception_type\']'));
$check('Incident timeline redacts legacy context before returning it', str_contains($detectRepo, 'SensitiveDataRedactor::redact'));
$check('Incident list does not expose raw context JSON', str_contains($capRepo, 'i.incident_key') && !str_contains(substr($capRepo, strpos($capRepo, 'public function incidents('), strpos($capRepo, 'public function countIncidentsByStatus(')), 'i.*'));
$check('SMTP transport failures are reported as external-service incidents', str_contains($read('app/Services/AdminEmailOutboxWorker.php'), 'captureExternalFailure'));
$check('Database failure reporting is available to caught service exceptions', str_contains($read('app/Core/Database.php'), 'public static function reportFailure') && str_contains($read('app/Controllers/PaymentController.php'), 'Database::reportFailure'));
$check('Payment failure events are captured without storing provider messages', str_contains($read('app/Controllers/PaymentController.php'), 'payment.failure'));
$check('Incident timeline events are persisted', str_contains($capRepo, 'admin_incident_events'));
$check('Timeline query is bounded and ordered', str_contains($detectRepo, 'ORDER BY occurred_at DESC, id DESC') && str_contains($detectRepo, 'min(200'));
$check('Frequency analysis is time-windowed and grouped', str_contains($detectRepo, 'min(720') && str_contains($detectRepo, 'GROUP BY source, severity'));
$check('Severity analysis aggregates event counts', str_contains($detectRepo, 'GROUP BY severity'));
$check('Client telemetry event types are allowlisted', str_contains($client, 'runtime_error') && str_contains($client, 'unhandled_rejection') && str_contains($client, 'resource_error'));
$check('Client telemetry has a session rate limit', str_contains($client, 'count($timestamps) >= 10'));
$check('Client telemetry does not persist raw stack traces', !str_contains($client, "'stack'"));
$check('Browser runtime and rejection listeners exist', str_contains($js, 'addEventListener("error"') && str_contains($js, 'addEventListener("unhandledrejection"'));
$check('Browser telemetry module is wired to storefront entry', str_contains($main, 'client-error-monitor.js'));
$check('Background queue audit is limited to known tables', str_contains($detectRepo, 'admin_operation_jobs') && str_contains($detectRepo, 'admin_email_outbox'));
$check('No generic queue is falsely reported as present', str_contains($detectRepo, "'generic_queue_found' => false"));
$check('Migration adds event and metadata tables only', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_incident_events') && str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_incident_metadata') && !str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_incidents ('));
$check('Admin timeline, analysis and job endpoints are registered', str_contains($routes, 'incidents/analysis') && str_contains($routes, 'incidents/{id}/timeline') && str_contains($routes, 'jobs/health'));
$check('New admin endpoints are permission-mapped', str_contains($permissions, 'admin.capabilities.incidents.analysis') && str_contains($permissions, 'admin.capabilities.incidents.timeline') && str_contains($permissions, 'admin.capabilities.jobs.health'));

$failed = count(array_filter($checks, static fn (array $row): bool => !$row[1]));
echo '<strong>' . count($checks) . ' checks; ' . (count($checks) - $failed) . ' PASS; ' . $failed . ' FAIL</strong>' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
