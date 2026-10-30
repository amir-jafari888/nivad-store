<?php

declare(strict_types=1);

final class AdminSystemHealthService
{
    public function snapshot(): array
    {
        $checks = [
            'php' => [
                'status' => 'ok',
                'version' => PHP_VERSION,
            ],
            'database' => [
                'status' => 'unknown',
            ],
            'timezone' => [
                'status' => 'ok',
                'value' => date_default_timezone_get(),
            ],
        ];

        try {
            $result = Database::connection()->query('SELECT 1')->fetchColumn();
            $checks['database']['status'] = (int)$result === 1 ? 'ok' : 'error';
        } catch (Throwable $exception) {
            $checks['database']['status'] = 'error';
        }

        return [
            'status' => $checks['database']['status'] === 'ok' ? 'healthy' : 'degraded',
            'checked_at' => date(DATE_ATOM),
            'checks' => $checks,
        ];
    }
}
