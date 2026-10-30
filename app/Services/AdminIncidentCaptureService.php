<?php

declare(strict_types=1);

/** Safe application boundary for incident capture. */
final class AdminIncidentCaptureService
{
    private static bool $capturing = false;

    public static function capture(
        string $source,
        string $severity,
        string $title,
        array $context = [],
        string $eventType = 'server.error',
        ?string $description = null
    ): void {
        if (self::$capturing) return;
        self::$capturing = true;

        try {
            $path = parse_url(Request::uri(), PHP_URL_PATH);
            $safe = SensitiveDataRedactor::redact($context);
            $safe['request_id'] = AdminRequestContext::id();
            $safe['request_method'] = Request::method();
            $fallbackPath = $safe['page'] ?? (is_string($path) ? $path : '/');
            $safe['route_path'] = mb_substr((string) ($safe['route_path'] ?? $fallbackPath), 0, 255);

            (new AdminCapabilityService())->recordIncident(
                mb_substr(trim($source), 0, 100),
                $severity,
                mb_substr(trim($title), 0, 255),
                $description ?? '',
                array_merge($safe, ['event_type' => $eventType])
            );
        } catch (Throwable $failure) {
            Logger::error('Incident capture failed.', [
                'failure_type' => get_class($failure),
                'source' => mb_substr($source, 0, 100),
                'event_type' => mb_substr($eventType, 0, 80),
            ]);
        } finally {
            self::$capturing = false;
        }
    }

    public static function captureThrowable(Throwable $exception, int $status = 500): void
    {
        if (!self::shouldCaptureHttpStatus($status)) return;
        $source = $exception instanceof PDOException ? 'database.failure' : 'php.exception';
        $severity = $status >= 500 ? 'critical' : ($status === 429 ? 'warning' : 'info');

        self::capture(
            $source,
            $severity,
            get_class($exception) . ' @ ' . basename($exception->getFile()) . ':' . $exception->getLine(),
            [
                'exception_type' => get_class($exception),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine(),
                'http_status' => $status,
            ],
            $source,
            'A server-side exception was recorded.'
        );
    }

    public static function captureHttp(int $status, string $source = 'http.response', array $context = []): void
    {
        if (!self::shouldCaptureHttpStatus($status)) return;
        $severity = $status >= 500 ? 'critical' : ($status === 429 ? 'warning' : 'info');

        self::capture(
            $source,
            $severity,
            'HTTP ' . $status . ' response',
            array_merge($context, ['http_status' => $status]),
            'http.status',
            'An HTTP error response was recorded.'
        );
    }

    public static function captureExternalFailure(string $service, Throwable $exception, array $context = []): void
    {
        $service = preg_match('/^[a-zA-Z0-9._-]{1,80}$/', $service) === 1 ? $service : 'unknown';
        self::capture(
            'external_service.failure',
            'warning',
            'External service failure: ' . $service,
            array_merge($context, [
                'service' => $service,
                'exception_type' => get_class($exception),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine(),
            ]),
            'external_service.failure',
            'An external service operation failed.'
        );
    }

    public static function shouldCaptureHttpStatus(int $status): bool
    {
        return $status === 404 || $status === 429 || $status >= 500;
    }
}
