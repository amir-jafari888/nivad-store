<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(
        string $view,
        array $data = []
    ): never {
        ViewRenderer::render($view, $data);
    }

    protected function json(
        mixed $data = null,
        int $status = 200
    ): never {
        Response::json($data, $status);
    }

    protected function success(
        mixed $data = null,
        string $message = 'عملیات با موفقیت انجام شد.'
    ): never {
        Response::success($data, $message);
    }

    protected function error(
        string $message,
        int $status = 400,
        array $errors = []
    ): never {
        Response::error($message, $status, $errors);
    }
}
