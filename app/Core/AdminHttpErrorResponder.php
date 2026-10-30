<?php

declare(strict_types=1);

/**
 * Chooses a browser-safe HTML error page or a machine-readable API response.
 * Never includes stack traces, filesystem paths, or exception internals in the
 * response body. Correlation IDs help locate the server-side log entry.
 */
final class AdminHttpErrorResponder
{
    public static function respond(
        int $statusCode,
        string $message,
        array $errors = []
    ): never {
        $statusCode = $statusCode >= 400 && $statusCode <= 599
            ? $statusCode
            : 500;

        if (self::expectsJson()) {
            Response::json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
                'request_id' => AdminRequestContext::id(),
            ], $statusCode);
        }

        self::renderHtml($statusCode, $message);
    }

    public static function expectsJson(): bool
    {
        if (
            Request::acceptsJson() ||
            Request::isAjax() ||
            Request::isJson()
        ) {
            return true;
        }

        $path = parse_url(Request::uri(), PHP_URL_PATH);
        if (!is_string($path)) {
            return false;
        }

        return $path === '/api' || str_starts_with($path, '/api/');
    }

    private static function renderHtml(int $statusCode, string $message): never
    {
        $title = match ($statusCode) {
            400 => 'درخواست نامعتبر',
            401 => 'نیاز به ورود',
            403 => 'دسترسی غیرمجاز',
            404 => 'صفحه پیدا نشد',
            405 => 'روش درخواست مجاز نیست',
            409 => 'تعارض در درخواست',
            422 => 'اطلاعات نامعتبر',
            429 => 'تعداد درخواست بیش از حد',
            default => 'خطای سرور',
        };

        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $requestId = htmlspecialchars(AdminRequestContext::id(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $backLink = $statusCode === 401
            ? '/admin/auth/login.php'
            : '/admin';

        Response::status($statusCode);
        Response::header('Content-Type', 'text/html; charset=utf-8');
        Response::header('Cache-Control', 'no-store, max-age=0');
        Response::header('X-Content-Type-Options', 'nosniff');

        echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<meta name="robots" content="noindex,nofollow">';
        echo '<title>' . $safeTitle . '</title></head><body>';
        echo '<main id="main-content" role="main" aria-labelledby="error-title">';
        echo '<h1 id="error-title">' . $safeTitle . '</h1>';
        echo '<p>' . $safeMessage . '</p>';
        echo '<p>کد پیگیری: <code>' . $requestId . '</code></p>';
        echo '<p><a href="' . $backLink . '">بازگشت به پنل مدیریت</a></p>';
        echo '</main></body></html>';
        exit;
    }
}
