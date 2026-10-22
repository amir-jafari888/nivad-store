<?php

declare(strict_types=1);

final class InventoryMovementController extends Controller
{
    private InventoryMovementService $service;
    private ProductVariantService $variantService;
    private InventoryMovementValidator $validator;

    public function __construct(
        ?InventoryMovementService $service = null,
        ?ProductVariantService $variantService = null,
        ?InventoryMovementValidator $validator = null
    ) {
        $this->service =
            $service ??
            new InventoryMovementService();

        $this->variantService =
            $variantService ??
            new ProductVariantService();

        $this->validator =
            $validator ??
            new InventoryMovementValidator();
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Movement نامعتبر است.',
                422
            );
        }

        $item =
            $this->service->findById($id);

        if ($item === null) {
            $this->error(
                'Movement پیدا نشد.',
                404
            );
        }

        $this->success(
            $item,
            'اطلاعات Movement دریافت شد.'
        );
    }

    public function byVariant(
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
            'Movementهای Variant دریافت شد.'
        );
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['variantId'] =
            (int)($data['variantId'] ?? 0);

        $data['movementType'] =
            trim(
                (string)(
                    $data['movementType'] ?? ''
                )
            );

        $data['quantity'] =
            (int)($data['quantity'] ?? 0);

        $data['referenceType'] =
            array_key_exists(
                'referenceType',
                $data
            )
            ? (
                $data['referenceType'] === null
                ? null
                : trim(
                    (string)$data['referenceType']
                )
            )
            : null;

        $data['referenceId'] =
            array_key_exists(
                'referenceId',
                $data
            ) &&
            $data['referenceId'] !== null &&
            $data['referenceId'] !== ''
            ? (int)$data['referenceId']
            : null;

        $data['note'] =
            array_key_exists(
                'note',
                $data
            )
            ? (
                $data['note'] === null
                ? null
                : trim((string)$data['note'])
            )
            : null;

        $data['createdBy'] =
            array_key_exists(
                'createdBy',
                $data
            ) &&
            $data['createdBy'] !== null &&
            $data['createdBy'] !== ''
            ? (int)$data['createdBy']
            : null;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Movement نامعتبر است.',
                422,
                $errors
            );
        }

        if (
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
                $data['variantId'],
                $data['movementType'],
                $data['quantity'],
                $data['referenceType'],
                $data['referenceId'],
                $data['note'],
                $data['createdBy']
            );

        $this->success(
            $item,
            'Movement با موفقیت ثبت شد.',
            201
        );
    }
}
