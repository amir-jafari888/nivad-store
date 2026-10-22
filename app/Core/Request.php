<?php

declare(strict_types=1);

final class Request
{
    private static ?string $rawBody = null;

    public static function method(): string
    {
        return strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }

    public static function isMethod(
        string $method
    ): bool {
        return self::method() === strtoupper($method);
    }

    public static function isGet(): bool
    {
        return self::isMethod('GET');
    }

    public static function isPost(): bool
    {
        return self::isMethod('POST');
    }

    public static function isPut(): bool
    {
        return self::isMethod('PUT');
    }

    public static function isPatch(): bool
    {
        return self::isMethod('PATCH');
    }

    public static function isDelete(): bool
    {
        return self::isMethod('DELETE');
    }

    public static function isHead(): bool
    {
        return self::isMethod('HEAD');
    }

    public static function isOptions(): bool
    {
        return self::isMethod('OPTIONS');
    }

    public static function input(
        string $key,
        mixed $default = null
    ): mixed {
        if (self::isGet()) {
            return $_GET[$key] ?? $default;
        }

        return $_POST[$key] ?? $default;
    }

    public static function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $_GET[$key] ?? $default;
    }

    public static function post(
        string $key,
        mixed $default = null
    ): mixed {
        return $_POST[$key] ?? $default;
    }

    public static function hasInput(
        string $key
    ): bool {
        if (self::isGet()) {
            return array_key_exists(
                $key,
                $_GET
            );
        }

        return array_key_exists(
            $key,
            $_POST
        );
    }

    public static function body(): string
    {
        if (self::$rawBody === null) {
            $body = file_get_contents(
                'php://input'
            );

            self::$rawBody = is_string($body)
                ? $body
                : '';
        }

        return self::$rawBody;
    }

    public static function json(): array
    {
        $body = trim(self::body());

        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new ValidationException([
                'body' => 'JSON نامعتبر است.',
            ]);
        }

        if (!is_array($data)) {
            throw new ValidationException([
                'body' => 'بدنه JSON باید یک شیء باشد.',
            ]);
        }

        return $data;
    }

    public static function header(
        string $name,
        mixed $default = null
    ): mixed {
        $name = strtoupper(
            str_replace(
                '-',
                '_',
                trim($name)
            )
        );

        if ($name === '') {
            return $default;
        }

        $serverKey = 'HTTP_' . $name;

        if ($name === 'CONTENT_TYPE') {
            $serverKey = 'CONTENT_TYPE';
        }

        if ($name === 'CONTENT_LENGTH') {
            $serverKey = 'CONTENT_LENGTH';
        }

        return $_SERVER[$serverKey] ?? $default;
    }

    public static function contentType(): ?string
    {
        $contentType = self::header(
            'Content-Type'
        );

        if (!is_string($contentType)) {
            return null;
        }

        $contentType = trim($contentType);

        if ($contentType === '') {
            return null;
        }

        return strtolower(
            trim(
                explode(
                    ';',
                    $contentType,
                    2
                )[0]
            )
        );
    }

    public static function acceptsJson(): bool
    {
        $accept = self::header(
            'Accept',
            ''
        );

        if (!is_string($accept)) {
            return false;
        }

        return str_contains(
            strtolower($accept),
            'application/json'
        );
    }

    public static function isJson(): bool
    {
        return self::contentType() ===
            'application/json';
    }

    public static function ip(): string
    {
        return Security::clientIp();
    }

    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function isAjax(): bool
    {
        return strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'xmlhttprequest';
    }
}
