<?php

declare(strict_types=1);

final class ViewRenderer
{
    public static function render(
        string $view,
        array $data = []
    ): never {
        $path = View::path($view);

        if (!is_file($path)) {
            throw new NotFoundException(
                'صفحه موردنظر پیدا نشد.'
            );
        }

        extract(
            $data,
            EXTR_SKIP
        );

        ob_start();

        try {
            require $path;

            $content = ob_get_clean();

            if ($content === false) {
                throw new RuntimeException(
                    'Failed to render view.'
                );
            }

            echo $content;

            exit;
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    public static function capture(
        string $view,
        array $data = []
    ): string {
        $path = View::path($view);

        if (!is_file($path)) {
            throw new NotFoundException(
                'صفحه موردنظر پیدا نشد.'
            );
        }

        extract(
            $data,
            EXTR_SKIP
        );

        ob_start();

        try {
            require $path;

            $content = ob_get_clean();

            if ($content === false) {
                throw new RuntimeException(
                    'Failed to capture view.'
                );
            }

            return $content;
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }
}
