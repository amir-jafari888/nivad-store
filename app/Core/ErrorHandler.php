<?php

declare(strict_types=1);

final class ErrorHandler
{
    private static bool $registered = false;
    private static bool $handlingShutdown = false;

    public static function register(): void
    {
        if (self::$registered) return;
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
        self::$registered = true;
    }

    public static function handleException(Throwable $exception): never
    {
        $statusCode = 500;
        $message = 'خطایی در سرور رخ داده است.';
        $errors = [];

        if ($exception instanceof AppException) {
            $statusCode = $exception->statusCode();
            if ($statusCode < 500) {
                $message = $exception->getMessage();
                $errors = $exception->errors();
            }
        }

        Logger::error('Unhandled exception.', [
            'type' => get_class($exception),
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine(),
            'request_id' => AdminRequestContext::id(),
            'http_status' => $statusCode,
        ]);
        AdminIncidentCaptureService::captureThrowable($exception, $statusCode);
        AdminRequestContext::publishResponseHeader();

        $path = parse_url(Request::uri(), PHP_URL_PATH);
        $isAdminBrowserRequest = is_string($path)
            && ($path === '/admin' || str_starts_with($path, '/admin/'))
            && !AdminHttpErrorResponder::expectsJson();

        if ($isAdminBrowserRequest) {
            AdminHttpErrorResponder::respond($statusCode, $message, $errors);
        }
        Response::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'request_id' => AdminRequestContext::id(),
        ], $statusCode);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) return false;
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        if (self::$handlingShutdown) return;
        $error = error_get_last();
        if (!is_array($error)) return;

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array((int) ($error['type'] ?? 0), $fatalTypes, true)) return;

        self::$handlingShutdown = true;
        $file = (string) ($error['file'] ?? '');
        $line = (int) ($error['line'] ?? 0);
        Logger::error('Fatal PHP shutdown error.', [
            'type' => (int) $error['type'],
            'file' => basename($file),
            'line' => $line,
            'request_id' => AdminRequestContext::id(),
        ]);
        AdminIncidentCaptureService::capture(
            'php.fatal',
            'critical',
            'Fatal PHP error @ ' . basename($file) . ':' . $line,
            ['php_error_type' => (int) $error['type'], 'file' => basename($file), 'line' => $line, 'http_status' => 500],
            'php.fatal',
            'A fatal PHP shutdown error was recorded.'
        );
    }
}
