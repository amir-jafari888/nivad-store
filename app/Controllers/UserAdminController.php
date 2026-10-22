<?php

declare(strict_types=1);

final class UserAdminController extends Controller
{
    private UserAdminService $service;

    public function __construct(
        ?UserAdminService $service = null
    ) {
        $this->service =
            $service ??
            new UserAdminService();
    }

    public function index(): never
    {
        $this->success(
            [
                'items' =>
                $this->service->findAll(),
            ],
            'کاربران دریافت شدند.'
        );
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id < 1) {
            $this->error(
                'شناسه کاربر نامعتبر است.',
                422
            );
        }

        $user =
            $this->service->findById($id);

        if ($user === null) {
            $this->error(
                'کاربر پیدا نشد.',
                404
            );
        }

        unset($user['password_hash']);

        $this->success(
            ['user' => $user],
            'کاربر دریافت شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)($data['status'] ?? '')
            );

        $user =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($user === null) {
            $this->error(
                'وضعیت کاربر نامعتبر است.',
                422
            );
        }

        unset($user['password_hash']);

        $this->success(
            ['user' => $user],
            'وضعیت کاربر تغییر کرد.'
        );
    }
}
