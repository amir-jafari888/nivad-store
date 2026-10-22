<?php

declare(strict_types=1);

final class InventoryReservationController extends Controller
{
    private InventoryReservationService $service;
    private ProductVariantService $variantService;
    private InventoryReservationValidator $validator;

    public function __construct(
        ?InventoryReservationService $service = null,
        ?ProductVariantService $variantService = null,
        ?InventoryReservationValidator $validator = null
    ) {
        $this->service =
            $service ??
            new InventoryReservationService();

        $this->variantService =
            $variantService ??
            new ProductVariantService();

        $this->validator =
            $validator ??
            new InventoryReservationValidator();
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Reservation نامعتبر است.',
                422
            );
        }

        $item =
            $this->service->findById($id);

        if ($item === null) {
            $this->error(
                'Reservation پیدا نشد.',
                404
            );
        }

        $this->success(
            $item,
            'اطلاعات Reservation دریافت شد.'
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
            'Reservationهای Variant دریافت شد.'
        );
    }

    public function byCart(
        array $parameters = []
    ): never {
        $cartId =
            (int)($parameters['cartId'] ?? 0);

        if ($cartId <= 0) {
            $this->error(
                'شناسه Cart نامعتبر است.',
                422
            );
        }

        $items =
            $this->service->findAllByCart(
                $cartId
            );

        $this->success(
            $items,
            'Reservationهای Cart دریافت شد.'
        );
    }

    public function byOrder(
        array $parameters = []
    ): never {
        $orderId =
            (int)($parameters['orderId'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه Order نامعتبر است.',
                422
            );
        }

        $items =
            $this->service->findAllByOrder(
                $orderId
            );

        $this->success(
            $items,
            'Reservationهای Order دریافت شد.'
        );
    }

    public function activeByVariant(
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
            $this->service->findAllActiveByVariant(
                $variantId
            );

        $this->success(
            $items,
            'Reservationهای فعال Variant دریافت شد.'
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

        $data['cartId'] =
            array_key_exists('cartId', $data) &&
            $data['cartId'] !== null &&
            $data['cartId'] !== ''
            ? (int)$data['cartId']
            : null;

        $data['orderId'] =
            array_key_exists('orderId', $data) &&
            $data['orderId'] !== null &&
            $data['orderId'] !== ''
            ? (int)$data['orderId']
            : null;

        $data['quantity'] =
            (int)($data['quantity'] ?? 0);

        $data['status'] =
            trim(
                (string)(
                    $data['status'] ?? 'active'
                )
            );

        $data['expiresAt'] =
            array_key_exists(
                'expiresAt',
                $data
            )
            ? (
                $data['expiresAt'] === null
                ? null
                : trim((string)$data['expiresAt'])
            )
            : null;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Reservation نامعتبر است.',
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
                $data['cartId'],
                $data['orderId'],
                $data['quantity'],
                $data['status'],
                $data['expiresAt']
            );

        $this->success(
            $item,
            'Reservation با موفقیت ثبت شد.',
            201
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Reservation نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById($id) === null
        ) {
            $this->error(
                'Reservation پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)(
                    $data['status'] ?? ''
                )
            );

        $errors =
            $this->validator->validate([
                'variantId' => 1,
                'quantity' => 0,
                'status' => $status,
            ]);

        if (isset($errors['status'])) {
            $this->error(
                'Status Reservation نامعتبر است.',
                422,
                [
                    'status' =>
                    $errors['status']
                ]
            );
        }

        $item =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($item === null) {
            $this->error(
                'Status Reservation بروزرسانی نشد.',
                422
            );
        }

        $this->success(
            $item,
            'Status Reservation بروزرسانی شد.'
        );
    }
}
