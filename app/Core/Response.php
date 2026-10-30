<?php

declare(strict_types=1);

final class Response
{
    public static function status(
        int $status
    ): void {
        if (
            $status < 100 ||
            $status > 599
        ) {
            throw new InvalidArgumentException(
                'Invalid HTTP status code.'
            );
        }

        http_response_code($status);
    }

    public static function header(
        string $name,
        string $value,
        bool $replace = true
    ): void {
        if (headers_sent()) {
            return;
        }

        $name = trim($name);

        if (
            $name === '' ||
            preg_match(
                '/[\r\n]/',
                $name
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid response header name.'
            );
        }

        if (
            preg_match(
                '/[\r\n]/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid response header value.'
            );
        }

        header(
            $name . ': ' . $value,
            $replace
        );
    }

    public static function json(
        mixed $data,
        int $status = 200
    ): never {
        self::status($status);

        if (!headers_sent()) {
            self::header(
                'Content-Type',
                'application/json; charset=utf-8'
            );
        }

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES |
                JSON_THROW_ON_ERROR
        );

        exit;
    }

    public static function text(
        string $content,
        int $status = 200
    ): never {
        self::status($status);

        if (!headers_sent()) {
            self::header(
                'Content-Type',
                'text/plain; charset=utf-8'
            );
        }

        echo $content;

        exit;
    }

    public static function redirect(
        string $url,
        int $status = 302
    ): never {
        if (
            $status < 300 ||
            $status > 399
        ) {
            throw new InvalidArgumentException(
                'Redirect status must be 3xx.'
            );
        }

        $url = trim($url);

        if ($url === '') {
            throw new InvalidArgumentException(
                'Redirect URL cannot be empty.'
            );
        }

        if (
            preg_match(
                '/[\r\n]/',
                $url
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid redirect URL.'
            );
        }

        if (str_starts_with($url, '/')) {
            self::status($status);

            self::header(
                'Location',
                $url
            );

            exit;
        }

        $target = parse_url($url);

        if (
            $target === false ||
            !is_array($target)
        ) {
            throw new InvalidArgumentException(
                'Invalid redirect URL.'
            );
        }

        $config = require __DIR__ .
            '/../../config/app.php';

        $base = parse_url(
            $config['url'] ?? ''
        );

        if (
            !is_array($base) ||
            empty($base['host']) ||
            empty($target['host'])
        ) {
            throw new InvalidArgumentException(
                'Invalid redirect URL.'
            );
        }

        $baseHost = strtolower(
            (string)$base['host']
        );

        $targetHost = strtolower(
            (string)$target['host']
        );

        if ($baseHost !== $targetHost) {
            throw new InvalidArgumentException(
                'External redirects are not allowed.'
            );
        }

        if (
            isset($base['port']) &&
            isset($target['port']) &&
            (int)$base['port'] !==
            (int)$target['port']
        ) {
            throw new InvalidArgumentException(
                'External redirects are not allowed.'
            );
        }

        self::status($status);

        self::header(
            'Location',
            $url
        );

        exit;
    }

    public static function error(
        string $message,
        int $status = 400,
        array $errors = []
    ): never {
        AdminIncidentCaptureService::captureHttp($status, 'http.response');
        self::json(
            ['success' => false, 'message' => $message, 'errors' => $errors],
            $status
        );
    }


    public static function validationError(
        array $errors = [],
        string $message =
        'اطلاعات واردشده معتبر نیست.'
    ): never {
        self::error(
            $message,
            422,
            $errors
        );
    }

    public static function success(
        mixed $data = null,
        string $message =
        'عملیات با موفقیت انجام شد.'
    ): never {
        self::json(
            [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]
        );
    }
}
