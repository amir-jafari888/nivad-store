<?php

declare(strict_types=1);

final class ProductAttributeController extends Controller
{
    private ProductAttributeService $service;

    public function __construct(
        ?ProductAttributeService $service = null
    ) {
        $this->service =
            $service
            ?? new ProductAttributeService();
    }

    public function index(): never
    {
        $attributes = $this->service->findAll();

        $this->success(
            $attributes,
            'لیست ویژگی‌ها دریافت شد.'
        );
    }

    public function show(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        $attribute =
            $this->service->findById($id);

        if ($attribute === null) {
            $this->error(
                'ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $attribute,
            'ویژگی دریافت شد.'
        );
    }

    public function store(): never
    {
        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductAttributeValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات ویژگی نامعتبر است.',
                422,
                $errors
            );
        }

        $attribute =
            $this->service->create(
                trim((string)$data['name']),
                trim((string)$data['slug']),
                trim((string)$data['type']),
                trim((string)$data['status']),
                (int)($data['sortOrder'] ?? 0)
            );

        $this->success(
            $attribute,
            'ویژگی با موفقیت ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductAttributeValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات ویژگی نامعتبر است.',
                422,
                $errors
            );
        }

        $attribute =
            $this->service->update(
                $id,
                trim((string)$data['name']),
                trim((string)$data['slug']),
                trim((string)$data['type']),
                trim((string)$data['status']),
                (int)($data['sortOrder'] ?? 0)
            );

        if ($attribute === null) {
            $this->error(
                'ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $attribute,
            'ویژگی با موفقیت بروزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        if (!$this->service->delete($id)) {
            $this->error(
                'ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'ویژگی با موفقیت حذف شد.'
        );
    }

    public function active(): never
    {
        $attributes =
            $this->service->findActive();

        $this->success(
            $attributes,
            'لیست ویژگی‌های فعال دریافت شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        if (!$this->service->restore($id)) {
            $this->error(
                'ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'ویژگی با موفقیت بازیابی شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim((string)($data['status'] ?? ''));

        if ($status === '') {
            $this->error(
                'وضعیت ویژگی الزامی است.',
                422,
                [
                    'status' =>
                    'وضعیت ویژگی الزامی است.'
                ]
            );
        }

        $attribute =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($attribute === null) {
            $this->error(
                'ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $attribute,
            'وضعیت ویژگی بروزرسانی شد.'
        );
    }
}
