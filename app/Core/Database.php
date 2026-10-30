<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../../config/database.php';

        $dsn =
            $config['driver'] .
            ':host=' . $config['host'] .
            ';port=' . $config['port'] .
            ';dbname=' . $config['database'] .
            ';charset=' . $config['charset'];

        try {
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            Logger::error(
                'Database connection failed.',
                [
                    'driver' => $config['driver'],
                    'host' => $config['host'],
                    'database' => $config['database'],
                ]
            );

            throw new RuntimeException(
                'Database connection failed.'
            );
        }

        return self::$connection;
    }

    public static function reportFailure(PDOException $exception, array $context = []): void
    {
        AdminIncidentCaptureService::capture(
            'database.failure',
            'critical',
            'Database operation failed.',
            array_merge($context, [
                'exception_type' => get_class($exception),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine(),
            ]),
            'database.failure',
            'A database operation failed.'
        );
    }

    public static function disconnect(): void
    {
        self::$connection = null;
    }
}
