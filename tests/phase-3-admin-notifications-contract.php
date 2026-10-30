<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void {
    $checks[] = [$name, $ok];
    echo '<div style="color:' . ($ok ? 'green' : 'red') . '">' . ($ok ? 'PASS' : 'FAIL') . ' — ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</div>' . PHP_EOL;
};
$read = static function (string $path) use ($root): string {
    $file = $root . '/' . $path;
    return is_file($file) ? (string)file_get_contents($file) : '';
};
$repo = $read('app/Repositories/AdminNotificationCenterRepository.php');
$service = $read('app/Services/AdminNotificationCenterService.php');
$controller = $read('app/Controllers/AdminCapabilityController.php');
$routes = $read('routes/api.php');
$permissions = $read('app/Core/AdminRoutePermissionMap.php');
$migration = $read('database/migrations/20261030_admin_notifications_phase3.sql');
$check('Canonical normalized notification tables are used', str_contains($repo, 'admin_notification_recipients') && str_contains($repo, 'admin_notifications n') && !str_contains($repo, 'recipient_admin_user_id'));
$check('Acknowledgement is persisted per recipient', str_contains($repo, 'acknowledged_at = COALESCE'));
$check('Notification history supports severity, event and date filters', str_contains($repo, "filters['severity']") && str_contains($repo, "filters['from']") && str_contains($repo, "filters['to']"));
$check('Preferences are per admin and event', str_contains($repo, 'admin_notification_preferences') && str_contains($migration, 'UNIQUE KEY uq_admin_notification_preference'));
$check('Quiet hours are timezone-aware', str_contains($service, 'isQuietNow') && str_contains($service, 'DateTimeZone'));
$check('Persistent dedupe and rate state are locked transactionally', str_contains($repo, 'FOR UPDATE') && str_contains($repo, 'admin_notification_rate_state') && str_contains($repo, 'admin_notification_dedupe_state'));
$check('Email delivery is queued only for opted-in recipients and enabled policy', str_contains($repo, 'admin_email_outbox') && str_contains($repo, 'email_enabled'));
$check('Read, acknowledge, preferences and history API routes are permission-mapped', str_contains($routes, 'notifications.acknowledge') && str_contains($routes, 'notification-preferences') && str_contains($permissions, 'admin.capabilities.notifications.acknowledge'));
$check('Event dispatcher exposes a single safe application boundary', is_file($root . '/app/Services/AdminNotificationEventDispatcher.php') && str_contains($read('app/Services/AdminNotificationEventDispatcher.php'), 'publishEvent'));
$centerRepo = $read('app/Repositories/AdminNotificationCenterRepository.php');
$centerService = $read('app/Services/AdminNotificationCenterService.php');
$outbox = $read('app/Repositories/PdoAdminEmailOutboxRepository.php');
$check('In-app and email preferences are independently honored', str_contains($centerService, '$inAppEnabled') && str_contains($centerRepo, 'in_app_enabled') && str_contains($centerRepo, 'email_enabled'));
$check('Persistent outbox adapter claims rows transactionally', str_contains($outbox, 'implements AdminEmailOutboxRepositoryInterface') && str_contains($outbox, 'FOR UPDATE') && str_contains($outbox, 'processing'));
$check('Notification policy read/update routes are permission-mapped', str_contains($routes, 'notification-policies') && str_contains($permissions, 'notification-policies.update'));
$check('Commerce and moderation events are connected to dispatcher', str_contains($read('app/Controllers/OrderController.php'), 'commerce.order.created') && str_contains($read('app/Controllers/PaymentController.php'), 'commerce.payment.failed') && str_contains($read('app/Controllers/InventoryController.php'), 'inventory.stock.low') && str_contains($read('app/Controllers/ReviewController.php'), 'content.review.pending') && str_contains($read('app/Controllers/ProductQuestionController.php'), 'content.question.pending'));
$failed = count(array_filter($checks, static fn(array $row): bool => !$row[1]));
echo '<strong>' . count($checks) . ' checks; ' . (count($checks) - $failed) . ' PASS; ' . $failed . ' FAIL</strong>' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
