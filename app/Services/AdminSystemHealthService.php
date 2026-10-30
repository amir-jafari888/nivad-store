<?php

declare(strict_types=1);

final class AdminSystemHealthService
{
    public function snapshot(): array
    {
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        $extensions = [];
        foreach ($requiredExtensions as $extension) {
            $extensions[$extension] = [
                'status' => extension_loaded($extension) ? 'ok' : 'error',
            ];
        }

        $timezone = date_default_timezone_get();
        $checks = [
            'php' => [
                'status' => 'ok',
                'version' => PHP_VERSION,
            ],
            'pdo' => $extensions['pdo'],
            'pdo_mysql' => $extensions['pdo_mysql'],
            'json' => $extensions['json'],
            'mbstring' => $extensions['mbstring'],
            'database' => [
                'status' => 'unknown',
                'duration_ms' => null,
            ],
            'timezone' => [
                'status' => $timezone !== '' ? 'ok' : 'error',
                'value' => $timezone,
            ],
        ];

        try {
            $startedAt = hrtime(true);
            $result = Database::connection()->query('SELECT 1')->fetchColumn();
            $checks['database']['status'] = (int)$result === 1 ? 'ok' : 'error';
            $checks['database']['duration_ms'] = round((hrtime(true) - $startedAt) / 1_000_000, 2);
        } catch (Throwable $exception) {
            $checks['database']['status'] = 'error';
        }

        $requiredChecksPassed = $checks['database']['status'] === 'ok'
            && $checks['timezone']['status'] === 'ok';
        foreach ($extensions as $extension) {
            $requiredChecksPassed = $requiredChecksPassed && $extension['status'] === 'ok';
        }

        return [
            'status' => $requiredChecksPassed ? 'healthy' : 'degraded',
            'checked_at' => date(DATE_ATOM),
            'checks' => $checks,
            'scope' => 'PHP runtime, required PHP extensions, database connectivity and configured timezone only.',
        ];
    }
}
