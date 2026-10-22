<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Bootstrap.php';

Bootstrap::boot();

$router = new Router();

$routeFile = __DIR__ . '/../routes/api.php';

if (!is_file($routeFile)) {
    Response::error(
        'فایل Routeهای API پیدا نشد.',
        500
    );
}

$registerRoutes = require $routeFile;

if (!is_callable($registerRoutes)) {
    Response::error(
        'ساختار Routeهای API نامعتبر است.',
        500
    );
}

$registerRoutes($router);

$path = $_GET['path'] ?? '/';

if (!is_string($path) || trim($path) === '') {
    $path = '/';
}

$path = '/' . ltrim($path, '/');

$query = $_GET;

unset($query['path']);

$queryString = http_build_query($query);

$uri = $path;

if ($queryString !== '') {
    $uri .= '?' . $queryString;
}

$application = new Application($router);

$application->handle(
    new RequestContext(
        Request::method(),
        $uri
    )
);
