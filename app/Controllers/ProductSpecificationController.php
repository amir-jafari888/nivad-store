<?php

declare(strict_types=1);

final class ProductSpecificationController extends Controller
{
    private ProductSpecificationService $service;
    private ProductService $productService;
    private SpecificationService $specificationService;
    private ProductSpecificationValidator $validator;

    public function __construct(
        ?ProductSpecificationService $service = null,
        ?ProductService $productService = null,
        ?SpecificationService $specificationService = null,
        ?ProductSpecificationValidator $validator = null
    ) {
        $this->service =
            $service ??
            new ProductSpecificationService();

        $this->productService =
            $productService ??
            new ProductService();

        $this->specificationService =
            $specificationService ??
            new SpecificationService();

        $this->validator =
            $validator ??
            new ProductSpecificationValidator();
    }

    /*
     * 10.18
     * Product specifications list
     */
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
            $this->productService->findById($productId)
            === null
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
            'مشخصات محصول دریافت شد.'
        );
    }

    /*
     * 10.18
     * Product specification create
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['productId'] =
            (int)($data['productId'] ?? 0);

        $data['specificationId'] =
            (int)($data['specificationId'] ?? 0);

        $data['valueText'] =
            array_key_exists('valueText', $data)
            ? (
                $data['valueText'] === null
                ? null
                : trim((string)$data['valueText'])
            )
            : null;

        $data['valueNumber'] =
            array_key_exists('valueNumber', $data)
            ? (
                $data['valueNumber'] === null
                ? null
                : trim((string)$data['valueNumber'])
            )
            : null;

        $data['valueBoolean'] =
            array_key_exists('valueBoolean', $data)
            ? $data['valueBoolean']
            : null;

        $data['valueDate'] =
            array_key_exists('valueDate', $data)
            ? (
                $data['valueDate'] === null
                ? null
                : trim((string)$data['valueDate'])
            )
            : null;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات مشخصات محصول نامعتبر است.',
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
            $this->specificationService->findById(
                $data['specificationId']
            ) === null
        ) {
            $this->error(
                'مشخصه موردنظر پیدا نشد.',
                404
            );
        }

        if (
            $this->service->exists(
                $data['productId'],
                $data['specificationId']
            )
        ) {
            $this->error(
                'این مشخصه قبلاً برای محصول ثبت شده است.',
                422,
                [
                    'specificationId' =>
                    'این مشخصه قبلاً برای محصول ثبت شده است.'
                ]
            );
        }

        $item =
            $this->service->create(
                $data['productId'],
                $data['specificationId'],
                $data['valueText'],
                $data['valueNumber'],
                $data['valueBoolean'],
                $data['valueDate']
            );

        if ($item === null) {
            $this->error(
                'ثبت مشخصات محصول انجام نشد.',
                422
            );
        }

        $this->success(
            $item,
            'مشخصات محصول با موفقیت ثبت شد.',
            201
        );
    }

    /*
     * 10.18
     * Product specification update
     */
    public function update(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['productId'] =
            (int)($data['productId'] ?? 0);

        $data['specificationId'] =
            (int)($data['specificationId'] ?? 0);

        $data['valueText'] =
            array_key_exists('valueText', $data)
            ? (
                $data['valueText'] === null
                ? null
                : trim((string)$data['valueText'])
            )
            : null;

        $data['valueNumber'] =
            array_key_exists('valueNumber', $data)
            ? (
                $data['valueNumber'] === null
                ? null
                : trim((string)$data['valueNumber'])
            )
            : null;

        $data['valueBoolean'] =
            array_key_exists('valueBoolean', $data)
            ? $data['valueBoolean']
            : null;

        $data['valueDate'] =
            array_key_exists('valueDate', $data)
            ? (
                $data['valueDate'] === null
                ? null
                : trim((string)$data['valueDate'])
            )
            : null;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات مشخصات محصول نامعتبر است.',
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
            $this->specificationService->findById(
                $data['specificationId']
            ) === null
        ) {
            $this->error(
                'مشخصه موردنظر پیدا نشد.',
                404
            );
        }

        if (
            !$this->service->exists(
                $data['productId'],
                $data['specificationId']
            )
        ) {
            $this->error(
                'رابطه مشخصات محصول پیدا نشد.',
                404
            );
        }

        $item =
            $this->service->update(
                $data['productId'],
                $data['specificationId'],
                $data['valueText'],
                $data['valueNumber'],
                $data['valueBoolean'],
                $data['valueDate']
            );

        if ($item === null) {
            $this->error(
                'بروزرسانی مشخصات محصول انجام نشد.',
                404
            );
        }

        $this->success(
            $item,
            'مشخصات محصول با موفقیت بروزرسانی شد.'
        );
    }

    /*
     * 10.18
     * Product specification delete
     */
    public function destroy(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $productId =
            (int)($data['productId'] ?? 0);

        $specificationId =
            (int)($data['specificationId'] ?? 0);

        if ($productId <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        if ($specificationId <= 0) {
            $this->error(
                'شناسه مشخصه نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete(
                $productId,
                $specificationId
            )
        ) {
            $this->error(
                'رابطه مشخصات محصول پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'مشخصات محصول با موفقیت حذف شد.'
        );
    }
}
