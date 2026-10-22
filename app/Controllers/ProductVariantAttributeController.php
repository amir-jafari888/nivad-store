<?php

declare(strict_types=1);

final class ProductVariantAttributeController extends Controller
{
    private ProductVariantAttributeService $service;

    private ProductVariantService $variantService;

    private ProductAttributeValueService $attributeValueService;

    private ProductAttributeService $attributeService;

    public function __construct(
        ?ProductVariantAttributeService $service = null,
        ?ProductVariantService $variantService = null,
        ?ProductAttributeValueService $attributeValueService = null,
        ?ProductAttributeService $attributeService = null
    ) {
        $this->service =
            $service
            ?? new ProductVariantAttributeService();

        $this->variantService =
            $variantService
            ?? new ProductVariantService();

        $this->attributeValueService =
            $attributeValueService
            ?? new ProductAttributeValueService();

        $this->attributeService =
            $attributeService
            ?? new ProductAttributeService();
    }

    /*
     * 09.81
     * 09.82
     * 09.83
     * 09.84
     * 09.85
     * 09.86
     * 09.87
     *
     * Domain-integrity validation
     */
    private function validateAssignment(
        int $variantId,
        int $attributeValueId
    ): void {
        /*
         * 09.82
         * Variant existence
         */
        $variant =
            $this->variantService
            ->findById($variantId);

        if ($variant === null) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404,
                [
                    'variantId' =>
                    'Variant موردنظر وجود ندارد.'
                ]
            );
        }

        /*
         * 09.83
         * Attribute Value existence
         */
        $attributeValue =
            $this->attributeValueService
            ->findById($attributeValueId);

        if ($attributeValue === null) {
            $this->error(
                'مقدار Attribute پیدا نشد.',
                404
            );
        }

        $attributeId =
            $attributeValue->attributeId;

        if (
            $this->service
            ->existsByVariantAndAttribute(
                $variantId,
                $attributeId
            )
        ) {
            $this->error(
                'این Variant قبلاً مقداری از این Attribute دارد.',
                409
            );
        }

        /*
         * 09.84
         * Attribute Value active state
         */
        $activeAttributeValue =
            $this->attributeValueService
            ->findActiveById($attributeValueId);

        if ($activeAttributeValue === null) {
            $this->error(
                'مقدار Attribute فعال نیست.',
                422,
                [
                    'attributeValueId' =>
                    'مقدار Attribute باید فعال باشد.'
                ]
            );
        }

        /*
         * 09.85
         * Variant active state
         */
        $activeVariant =
            $this->variantService
            ->findActiveById($variantId);

        if ($activeVariant === null) {
            $this->error(
                'Variant فعال نیست.',
                422,
                [
                    'variantId' =>
                    'Variant باید فعال باشد.'
                ]
            );
        }

        /*
         * 09.86
         * Duplicate assignment
         */
        if (
            $this->service->exists(
                $variantId,
                $attributeValueId
            )
        ) {
            $this->error(
                'این Attribute قبلاً به Variant اختصاص داده شده است.',
                409,
                [
                    'attributeValueId' =>
                    'این ارتباط قبلاً وجود دارد.'
                ]
            );
        }
    }

    public function index(
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

        $items =
            $this->service
            ->findAllByVariant($variantId);

        $this->success(
            $items,
            'ویژگی‌های Variant دریافت شد.'
        );
    }

    /*
     * 09.81–09.87
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductVariantAttributeValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات ارتباط Variant و Attribute نامعتبر است.',
                422,
                $errors
            );
        }

        $variantId =
            (int)$data['variantId'];

        $attributeValueId =
            (int)$data['attributeValueId'];

        $this->validateAssignment(
            $variantId,
            $attributeValueId
        );

        $item =
            $this->service->create(
                $variantId,
                $attributeValueId
            );

        if ($item === null) {
            $this->error(
                'ارتباط Variant و Attribute قابل ایجاد نیست.',
                409
            );
        }

        $this->success(
            $item,
            'ارتباط Variant و Attribute ایجاد شد.',
            201
        );
    }

    public function destroy(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new ProductVariantAttributeValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات ارتباط نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            !$this->service->delete(
                (int)$data['variantId'],
                (int)$data['attributeValueId']
            )
        ) {
            $this->error(
                'ارتباط موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'ارتباط Variant و Attribute حذف شد.'
        );
    }
}
