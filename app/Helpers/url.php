<?php

declare(strict_types=1);

function app_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $config = require __DIR__ . '/../../config/app.php';

    $configuredBasePath = trim(
        (string) ($config['base_path'] ?? '')
    );

    if ($configuredBasePath !== '') {
        $basePath = '/' . trim(
            str_replace('\\', '/', $configuredBasePath),
            '/'
        );

        return $basePath === '/' ? '' : $basePath;
    }

    $projectRoot = realpath(__DIR__ . '/../..');

    $documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
        ? realpath((string) $_SERVER['DOCUMENT_ROOT'])
        : false;

    if (
        $projectRoot === false ||
        $documentRoot === false
    ) {
        return '';
    }

    $projectRoot = rtrim(
        str_replace('\\', '/', $projectRoot),
        '/'
    );

    $documentRoot = rtrim(
        str_replace('\\', '/', $documentRoot),
        '/'
    );

    if ($projectRoot === $documentRoot) {
        return '';
    }

    $documentRootPrefix = $documentRoot . '/';

    if (
        !str_starts_with(
            $projectRoot . '/',
            $documentRootPrefix
        )
    ) {
        return '';
    }

    $relativePath = substr(
        $projectRoot,
        strlen($documentRoot)
    );

    $basePath = '/' . trim(
        $relativePath,
        '/'
    );

    return $basePath === '/'
        ? ''
        : $basePath;
}

function app_origin(): string
{
    static $origin = null;

    if ($origin !== null) {
        return $origin;
    }

    $config = require __DIR__ . '/../../config/app.php';

    $configuredUrl = trim(
        (string) ($config['url'] ?? '')
    );

    if ($configuredUrl !== '') {
        $configuredUrlData = parse_url($configuredUrl);

        if (
            is_array($configuredUrlData) &&
            !empty($configuredUrlData['scheme']) &&
            !empty($configuredUrlData['host'])
        ) {
            $origin =
                $configuredUrlData['scheme'] .
                '://' .
                $configuredUrlData['host'] .
                (
                    isset($configuredUrlData['port'])
                    ? ':' . (int) $configuredUrlData['port']
                    : ''
                );

            return rtrim($origin, '/');
        }
    }

    $scheme = 'http';

    if (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== '' &&
        strtolower((string) $_SERVER['HTTPS']) !== 'off'
    ) {
        $scheme = 'https';
    }

    $host = trim(
        (string) ($_SERVER['HTTP_HOST'] ?? '')
    );

    if (
        $host === '' ||
        !preg_match(
            '/^(?:[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?|\[[0-9a-f:]+\])(?::\d{1,5})?$/i',
            $host
        )
    ) {
        $host = 'localhost';
    }

    $origin = $scheme . '://' . $host;

    return $origin;
}

function url(string $path = ''): string
{
    $path = trim($path);

    if (
        $path !== '' &&
        filter_var($path, FILTER_VALIDATE_URL)
    ) {
        return $path;
    }

    $baseUrl = rtrim(
        app_origin() . app_base_path(),
        '/'
    );

    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim(
        $path,
        '/'
    );
}

function asset(string $path): string
{
    return url(
        'assets/' . ltrim($path, '/')
    );
}

function route_url(
    string $path,
    array $parameters = []
): string {
    foreach ($parameters as $key => $value) {
        $path = str_replace(
            '{' . $key . '}',
            rawurlencode((string) $value),
            $path
        );
    }

    return url($path);
}
