<?php

declare(strict_types=1);

final class InventoryController extends Controller
{
    private InventoryService $service;
    private ProductVariantService $variantService;
    private InventoryValidator $validator;

    public function __construct(
        ?InventoryService $service = null,
        ?ProductVariantService $variantService = null,
        ?InventoryValidator $validator = null
    ) {
        $this->service =
            $service ??
            new InventoryService();

        $this->variantService =
            $variantService ??
            new ProductVariantService();

        $this->validator =
            $validator ??
            new InventoryValidator();
    }

    public function index(): never
    {
        $this->error(
            'Inventory index requires a variant identifier.',
            422
        );
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Inventory نامعتبر است.',
                422
            );
        }

        $inventory =
            $this->service->findById($id);

        if ($inventory === null) {
            $this->error(
                'Inventory پیدا نشد.',
                404
            );
        }

        $this->success(
            $inventory,
            'اطلاعات Inventory دریافت شد.'
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

        $inventory =
            $this->service->findByVariant(
                $variantId
            );

        $this->success(
            $inventory,
            'اطلاعات موجودی Variant دریافت شد.'
        );
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Inventory نامعتبر است.',
                422,
                $errors
            );
        }

        $variantId =
            (int)$data['variantId'];

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

        if (
            $this->service->existsByVariant(
                $variantId
            )
        ) {
            $this->error(
                'برای این Variant قبلاً Inventory ثبت شده است.',
                422,
                [
                    'variantId' =>
                    'Inventory این Variant قبلاً وجود دارد.'
                ]
            );
        }

        $inventory =
            $this->service->create(
                $variantId,
                (int)$data['quantity'],
                (int)$data['reservedQuantity'],
                (int)$data['lowStockThreshold']
            );

        $quantity = (int)$data['quantity'];
        $threshold = (int)$data['lowStockThreshold'];
        if ($quantity <= 0) {
            (new AdminNotificationEventDispatcher())->dispatch('inventory.stock.out', 'موجودی محصول تمام شد', 'موجودی یک محصول به صفر رسیده است.', 'variant', $variantId, ['quantity' => $quantity]);
        } elseif ($quantity <= $threshold) {
            (new AdminNotificationEventDispatcher())->dispatch('inventory.stock.low', 'موجودی محصول رو به اتمام است', 'موجودی یک محصول به آستانه هشدار رسیده است.', 'variant', $variantId, ['quantity' => $quantity, 'threshold' => $threshold]);
        }
        $this->success(
            $inventory,
            'Inventory با موفقیت ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه Inventory نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $errors =
            $this->validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Inventory نامعتبر است.',
                422,
                $errors
            );
        }

        $existing =
            $this->service->findById($id);

        if ($existing === null) {
            $this->error(
                'Inventory پیدا نشد.',
                404
            );
        }

        $variantId =
            (int)$data['variantId'];

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

        $updated =
            $this->service->update(
                $id,
                (int)$data['quantity'],
                (int)$data['reservedQuantity'],
                (int)$data['lowStockThreshold']
            );

        if ($updated === null) {
            $this->error(
                'Inventory بروزرسانی نشد.',
                404
            );
        }

        $quantity = (int)$data['quantity'];
        $threshold = (int)$data['lowStockThreshold'];
        if ($quantity <= 0) {
            (new AdminNotificationEventDispatcher())->dispatch('inventory.stock.out', 'موجودی محصول تمام شد', 'موجودی یک محصول به صفر رسیده است.', 'inventory', $id, ['quantity' => $quantity]);
        } elseif ($quantity <= $threshold) {
            (new AdminNotificationEventDispatcher())->dispatch('inventory.stock.low', 'موجودی محصول رو به اتمام است', 'موجودی یک محصول به آستانه هشدار رسیده است.', 'inventory', $id, ['quantity' => $quantity, 'threshold' => $threshold]);
        }
        $this->success(
            $updated,
            'Inventory بروزرسانی شد.'
        );
    }
}
