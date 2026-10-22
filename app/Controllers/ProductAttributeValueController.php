<?php

declare(strict_types=1);

final class ProductAttributeValueController extends Controller
{
    private ProductAttributeValueService $service;

    public function __construct(
        ?ProductAttributeValueService $service = null
    ) {
        $this->service =
            $service
            ?? new ProductAttributeValueService();
    }

    public function index(
        array $parameters = []
    ): never {
        $attributeId =
            (int)($parameters['attributeId'] ?? 0);

        if ($attributeId <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        $values =
            $this->service
            ->findAllByAttribute($attributeId);

        $this->success(
            $values,
            'لیست مقادیر ویژگی دریافت شد.'
        );
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مقدار ویژگی نامعتبر است.',
                422
            );
        }

        $value =
            $this->service->findById($id);

        if ($value === null) {
            $this->error(
                'مقدار ویژگی پیدا نشد.',
                404
            );
        }

        $this->success(
            $value,
            'مقدار ویژگی دریافت شد.'
        );
    }

    public function store(): never
    {
        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductAttributeValueValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات مقدار ویژگی نامعتبر است.',
                422,
                $errors
            );
        }

        $value =
            $this->service->create(
                (int)$data['attributeId'],
                trim((string)$data['value']),
                trim((string)$data['slug']),
                trim((string)$data['status']),
                (int)($data['sortOrder'] ?? 0)
            );

        $this->success(
            $value,
            'مقدار ویژگی با موفقیت ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مقدار ویژگی نامعتبر است.',
                422
            );
        }

        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductAttributeValueValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات مقدار ویژگی نامعتبر است.',
                422,
                $errors
            );
        }

        $value =
            $this->service->update(
                $id,
                (int)$data['attributeId'],
                trim((string)$data['value']),
                trim((string)$data['slug']),
                trim((string)$data['status']),
                (int)($data['sortOrder'] ?? 0)
            );

        if ($value === null) {
            $this->error(
                'مقدار ویژگی پیدا نشد.',
                404
            );
        }

        $this->success(
            $value,
            'مقدار ویژگی بروزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مقدار ویژگی نامعتبر است.',
                422
            );
        }

        if (!$this->service->delete($id)) {
            $this->error(
                'مقدار ویژگی پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'مقدار ویژگی حذف شد.'
        );
    }

    public function active(
        array $parameters = []
    ): never {
        $attributeId =
            (int)($parameters['attributeId'] ?? 0);

        if ($attributeId <= 0) {
            $this->error(
                'شناسه ویژگی نامعتبر است.',
                422
            );
        }

        $values =
            $this->service
            ->findActiveByAttribute($attributeId);

        $this->success(
            $values,
            'لیست مقادیر فعال ویژگی دریافت شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مقدار ویژگی نامعتبر است.',
                422
            );
        }

        if (!$this->service->restore($id)) {
            $this->error(
                'مقدار ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'مقدار ویژگی با موفقیت بازیابی شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مقدار ویژگی نامعتبر است.',
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
                'وضعیت مقدار ویژگی الزامی است.',
                422,
                [
                    'status' =>
                    'وضعیت مقدار ویژگی الزامی است.'
                ]
            );
        }

        $value =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($value === null) {
            $this->error(
                'مقدار ویژگی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $value,
            'وضعیت مقدار ویژگی بروزرسانی شد.'
        );
    }
}
