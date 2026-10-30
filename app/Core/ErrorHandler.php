<?php

declare(strict_types=1);

final class ErrorHandler
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
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

        // Do not log the raw exception message: database/provider exceptions
        // can contain credentials, SQL fragments, or personal data.
        Logger::error('Unhandled exception.', [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_id' => AdminRequestContext::id(),
        ]);

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

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }
}
