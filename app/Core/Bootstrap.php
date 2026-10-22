<?php

declare(strict_types=1);

final class Bootstrap
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::registerAutoloader();
        self::loadHelpers();
        self::registerErrorHandler();

        Security::enforceHttps();
        SecurityHeadersMiddleware::send();

        self::$booted = true;
    }

    private static function registerAutoloader(): void
    {
        spl_autoload_register(
            static function (string $class): void {
                $basePath = __DIR__ . '/..';

                $directories = [
                    $basePath . '/Core',
                    $basePath . '/Core/Exceptions',
                    $basePath . '/Controllers',
                    $basePath . '/Services',
                    $basePath . '/Repositories',
                    $basePath . '/Models',
                    $basePath . '/Validators',
                    $basePath . '/Middleware',
                ];

                foreach ($directories as $directory) {
                    $file = $directory . '/' . $class . '.php';

                    if (is_file($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        );
    }

    private static function loadHelpers(): void
    {
        $helpers = [
            __DIR__ . '/../Helpers/escape.php',
            __DIR__ . '/../Helpers/input.php',
            __DIR__ . '/../Helpers/security.php',
            __DIR__ . '/../Helpers/url.php',
        ];

        foreach ($helpers as $helper) {
            if (is_file($helper)) {
                require_once $helper;
            }
        }
    }

    private static function registerErrorHandler(): void
    {
        ErrorHandler::register();
    }
}
