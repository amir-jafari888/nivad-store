<?php

declare(strict_types=1);

// This entry point is CLI-only. Schedule it with a trusted server cron/task.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';
Bootstrap::boot();

try {
    $worker = new AdminEmailOutboxWorker(new PdoAdminEmailOutboxRepository());
    $summary = $worker->processBatch(25);
    echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (Throwable $exception) {
    error_log('Admin email outbox worker failed: ' . get_class($exception));
    fwrite(STDERR, "Admin email outbox worker failed." . PHP_EOL);
    exit(1);
}
