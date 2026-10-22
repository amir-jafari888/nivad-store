<?php

declare(strict_types=1);

final class ProductMediaController extends Controller
{
    private ProductMediaService $service;
    private ProductService $productService;
    private ProductVariantService $variantService;
    private ProductMediaValidator $validator;

    public function __construct(
        ?ProductMediaService $service = null,
        ?ProductService $productService = null,
        ?ProductVariantService $variantService = null,
        ?ProductMediaValidator $validator = null
    ) {
        $this->service =
            $service ??
            new ProductMediaService();

        $this->productService =
            $productService ??
            new ProductService();

        $this->variantService =
            $variantService ??
            new ProductVariantService();

        $this->validator =
            $validator ??
            new ProductMediaValidator();
    }

    public function index(
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

        if (
            $this->productService->findById(
                $productId
            ) === null
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $items =
            $this->service->findAllByProduct(
                $productId
            );

        $this->success(
            $items,
            'Mediaهای محصول دریافت شد.'
        );
    }

    public function indexByVariant(
        array $parameters = []
    ): never {
        $variantId =
            (int)($parameters['variantId'] ?? 0);

        if ($variantId <= 0) {
            $this->error(
                'شناسه Variant نامعتبر است.',
                422
            );
        }

        if (
            $this->variantService->findById(
                $variantId
            ) === null
        ) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $items =
            $this->service->findAllByVariant(
                $variantId
            );

        $this->success(
            $items,
            'Mediaهای Variant دریافت شد.'
        );
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['productId'] =
            (int)($data['productId'] ?? 0);

        $data['variantId'] =
            array_key_exists(
                'variantId',
                $data
            ) &&
            $data['variantId'] !== null &&
            $data['variantId'] !== ''
            ? (int)$data['variantId']
            : null;

        $data['mediaType'] =
            trim(
                (string)($data['mediaType'] ?? '')
            );

        $data['filePath'] =
            trim(
                (string)($data['filePath'] ?? '')
            );

        $data['thumbnailPath'] =
            array_key_exists(
                'thumbnailPath',
                $data
            )
            ? (
                $data['thumbnailPath'] === null
                ? null
                : trim(
                    (string)$data['thumbnailPath']
                )
            )
            : null;

        $data['altText'] =
            array_key_exists(
                'altText',
                $data
            )
            ? (
                $data['altText'] === null
                ? null
                : trim(
                    (string)$data['altText']
                )
            )
            : null;

        $data['sortOrder'] =
            (int)($data['sortOrder'] ?? 0);

        $data['isPrimary'] =
            $data['isPrimary'] ?? false;

        $data['status'] =
            trim(
                (string)($data['status'] ?? '')
            );

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Media نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            $this->productService->findById(
                $data['productId']
            ) === null
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        if (
            $data['variantId'] !== null &&
            $this->variantService->findById(
                $data['variantId']
            ) === null
        ) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $item =
            $this->service->create(
                $data['productId'],
                $data['variantId'],
                $data['mediaType'],
                $data['filePath'],
                $data['thumbnailPath'],
                $data['altText'],
                $data['sortOrder'],
                (bool)$data['isPrimary'],
                $data['status']
            );

        if ($item === null) {
            $this->error(
                'ثبت Media انجام نشد.',
                422
            );
        }

        $this->success(
            $item,
            'Media با موفقیت ثبت شد.',
            201
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Media نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->exists($id)
        ) {
            $this->error(
                'Media موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['productId'] =
            (int)($data['productId'] ?? 0);

        $data['variantId'] =
            array_key_exists(
                'variantId',
                $data
            ) &&
            $data['variantId'] !== null &&
            $data['variantId'] !== ''
            ? (int)$data['variantId']
            : null;

        $data['mediaType'] =
            trim(
                (string)($data['mediaType'] ?? '')
            );

        $data['filePath'] =
            trim(
                (string)($data['filePath'] ?? '')
            );

        $data['thumbnailPath'] =
            array_key_exists(
                'thumbnailPath',
                $data
            )
            ? (
                $data['thumbnailPath'] === null
                ? null
                : trim(
                    (string)$data['thumbnailPath']
                )
            )
            : null;

        $data['altText'] =
            array_key_exists(
                'altText',
                $data
            )
            ? (
                $data['altText'] === null
                ? null
                : trim(
                    (string)$data['altText']
                )
            )
            : null;

        $data['sortOrder'] =
            (int)($data['sortOrder'] ?? 0);

        $data['isPrimary'] =
            $data['isPrimary'] ?? false;

        $data['status'] =
            trim(
                (string)($data['status'] ?? '')
            );

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Media نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            $this->productService->findById(
                $data['productId']
            ) === null
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        if (
            $data['variantId'] !== null &&
            $this->variantService->findById(
                $data['variantId']
            ) === null
        ) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $item =
            $this->service->update(
                $id,
                $data['productId'],
                $data['variantId'],
                $data['mediaType'],
                $data['filePath'],
                $data['thumbnailPath'],
                $data['altText'],
                $data['sortOrder'],
                (bool)$data['isPrimary'],
                $data['status']
            );

        if ($item === null) {
            $this->error(
                'بروزرسانی Media انجام نشد.',
                404
            );
        }

        $this->success(
            $item,
            'Media با موفقیت بروزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Media نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'Media موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'Media با موفقیت حذف شد.'
        );
    }
}
