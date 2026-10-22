<?php

declare(strict_types=1);

final class ProductVariantController extends Controller
{
    private ProductVariantService $service;

    public function __construct(
        ?ProductVariantService $service = null
    ) {
        $this->service =
            $service
            ?? new ProductVariantService();
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        $variant =
            $this->service->findById($id);

        if ($variant === null) {
            $this->error(
                'Variant پیدا نشد.',
                404
            );
        }

        $this->success(
            $variant,
            'Variant دریافت شد.'
        );
    }

    public function byProduct(
        array $parameters = []
    ): never {
        $productId =
            (int)($parameters['productId'] ?? 0);

        if ($productId <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $variants =
            $this->service
            ->findAllByProduct($productId);

        $this->success(
            $variants,
            'لیست Variantهای محصول دریافت شد.'
        );
    }

    public function store(): never
    {
        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductVariantValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Variant نامعتبر است.',
                422,
                $errors
            );
        }

        $variant =
            $this->service->create(
                (int)$data['productId'],
                trim((string)$data['sku']),
                isset($data['barcode'])
                    ? trim((string)$data['barcode'])
                    : null,
                (string)($data['price'] ?? '0'),
                isset($data['compareAtPrice'])
                    ? (string)$data['compareAtPrice']
                    : null,
                strtoupper(
                    trim(
                        (string)($data['currency'] ?? 'IRR')
                    )
                ),
                trim((string)$data['status']),
                (bool)($data['isDefault'] ?? false),
                (int)($data['sortOrder'] ?? 0)
            );

        $this->success(
            $variant,
            'Variant با موفقیت ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductVariantValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Variant نامعتبر است.',
                422,
                $errors
            );
        }

        $variant =
            $this->service->update(
                $id,
                (int)$data['productId'],
                trim((string)$data['sku']),
                isset($data['barcode'])
                    ? trim((string)$data['barcode'])
                    : null,
                (string)($data['price'] ?? '0'),
                isset($data['compareAtPrice'])
                    ? (string)$data['compareAtPrice']
                    : null,
                strtoupper(
                    trim(
                        (string)($data['currency'] ?? 'IRR')
                    )
                ),
                trim((string)$data['status']),
                (bool)($data['isDefault'] ?? false),
                (int)($data['sortOrder'] ?? 0)
            );

        if ($variant === null) {
            $this->error(
                'Variant پیدا نشد.',
                404
            );
        }

        $this->success(
            $variant,
            'Variant بروزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        if (!$this->service->delete($id)) {
            $this->error(
                'Variant پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'Variant حذف شد.'
        );
    }

    public function activeByProduct(
        array $parameters = []
    ): never {
        $productId =
            (int)($parameters['productId'] ?? 0);

        if ($productId <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $variants =
            $this->service
            ->findActiveByProduct($productId);

        $this->success(
            $variants,
            'لیست Variantهای فعال دریافت شد.'
        );
    }

    public function defaultByProduct(
        array $parameters = []
    ): never {
        $productId =
            (int)($parameters['productId'] ?? 0);

        if ($productId <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $variant =
            $this->service
            ->findDefaultByProduct($productId);

        $this->success(
            $variant,
            'Variant پیش‌فرض دریافت شد.'
        );
    }

    public function setDefault(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        $variant =
            $this->service->setDefault($id);

        if ($variant === null) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $variant,
            'Variant به عنوان پیش‌فرض تعیین شد.'
        );
    }

    public function clearDefault(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        $variant =
            $this->service->clearDefault($id);

        if ($variant === null) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $variant,
            'Variant از حالت پیش‌فرض خارج شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        if (!$this->service->restore($id)) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'Variant با موفقیت بازیابی شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
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
                'وضعیت Variant الزامی است.',
                422,
                [
                    'status' =>
                    'وضعیت Variant الزامی است.'
                ]
            );
        }

        $variant =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($variant === null) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $variant,
            'وضعیت Variant بروزرسانی شد.'
        );
    }
}
