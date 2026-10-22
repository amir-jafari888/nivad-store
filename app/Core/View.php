<?php

declare(strict_types=1);

final class View
{
    public static function path(string $view): string
    {
        $view = trim($view);

        if ($view === '') {
            throw new InvalidArgumentException(
                'View name cannot be empty.'
            );
        }

        if (
            str_contains($view, '..') ||
            str_contains($view, '\\') ||
            str_starts_with($view, '/')
        ) {
            throw new InvalidArgumentException(
                'Invalid view name.'
            );
        }

        $view = trim($view, '/');

        return dirname(__DIR__, 2) .
            '/pages/' .
            $view .
            '.php';
    }

    public static function exists(string $view): bool
    {
        return is_file(
            self::path($view)
        );
    }
}
