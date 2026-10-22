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

        set_exception_handler(
            [self::class, 'handleException']
        );

        set_error_handler(
            [self::class, 'handleError']
        );

        self::$registered = true;
    }

    public static function handleException(
        Throwable $exception
    ): never {
        $statusCode = 500;
        $message = 'خطایی در سرور رخ داده است.';
        $errors = [];

        if ($exception instanceof AppException) {
            $statusCode = $exception->statusCode();
            $message = $exception->getMessage();
            $errors = $exception->errors();
        }

        Logger::error(
            'Unhandled exception.',
            [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        );

        Response::error(
            $message,
            $statusCode,
            $errors
        );
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
