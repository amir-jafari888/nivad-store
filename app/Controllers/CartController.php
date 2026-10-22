<?php

declare(strict_types=1);

final class CartController extends Controller
{
    private ProductService $products;

    private ProductVariantService $variants;

    public function __construct(
        ?ProductService $products = null,
        ?ProductVariantService $variants = null
    ) {
        $this->products = $products ?? new ProductService();
        $this->variants = $variants ?? new ProductVariantService();
    }

    private function itemData(CartItem $item): array
    {
        $variant = $this->variants->findById($item->variantId);

        $product = $variant !== null
            ? $this->products->findById($variant->productId)
            : null;

        return [
            'id' => $item->id,
            'cart_id' => $item->cartId,
            'variant_id' => $item->variantId,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice,
            'created_at' => $item->createdAt,
            'updated_at' => $item->updatedAt,

            'variant' => $variant === null
                ? null
                : [
                    'id' => $variant->id,
                    'product_id' => $variant->productId,
                    'sku' => $variant->sku,
                    'name' => $variant->name,
                    'price' => $variant->price,
                    'currency' => $variant->currency,
                ],

            'product' => $product === null
                ? null
                : [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' =>
                    $product->shortDescription,
                ],
        ];
    }
    private function currentUserId(): int
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        return $userId;
    }

    private function ownedCart(
        int $cartId,
        int $userId
    ): Cart {
        $service = new CartService();

        $cart = $service->findById($cartId);

        if ($cart === null) {
            $this->error(
                'سبد خرید موردنظر پیدا نشد.',
                404
            );
        }

        if ($cart->userId !== $userId) {
            $this->error(
                'دسترسی به این سبد خرید مجاز نیست.',
                403
            );
        }

        return $cart;
    }

    /*
    |--------------------------------------------------------------------------
    | 12.31 — Current User Cart
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $userId = $this->currentUserId();

        $service = new CartService();

        $cart = $service->findByUser($userId);

        $items = $cart === null
            ? []
            : (new CartItemService())
            ->findByCart($cart->id);

        $this->success([
            'cart' => $cart,

            'items' => array_map(
                fn(CartItem $item): array =>
                $this->itemData($item),
                $items
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 12.32 — Show Cart
    |--------------------------------------------------------------------------
    */

    public function show(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $cartId = (int)($parameters['id'] ?? 0);

        if ($cartId <= 0) {
            $this->error(
                'شناسه سبد خرید نامعتبر است.',
                422
            );
        }

        $cart = $this->ownedCart(
            $cartId,
            $userId
        );

        $this->success([
            'cart' => $cart,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 12.33 — Create Cart
    |--------------------------------------------------------------------------
    */

    public function store(): never
    {
        $userId = $this->currentUserId();

        $currency = Request::post(
            'currency',
            'IRR'
        );

        $status = Request::post(
            'status',
            'active'
        );

        $expiresAt = Request::post(
            'expires_at'
        );

        $validator = new CartValidator();

        $errors = $validator->validate([
            'userId' => $userId,
            'sessionTokenHash' => null,
            'status' => $status,
            'currency' => $currency,
            'expiresAt' => $expiresAt,
        ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات سبد خرید نامعتبر است.',
                422,
                $errors
            );
        }

        $service = new CartService();

        $existing = $service->findByUser(
            $userId
        );

        if ($existing !== null) {
            $this->success(
                ['cart' => $existing],
                'سبد خرید فعال کاربر موجود است.'
            );
        }

        $cart = $service->create(
            $userId,
            null,
            (string)$status,
            (string)$currency,
            $expiresAt !== null
                ? (string)$expiresAt
                : null
        );

        $this->success(
            ['cart' => $cart],
            'سبد خرید با موفقیت ایجاد شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 12.34 — Update Cart
    |--------------------------------------------------------------------------
    */

    public function update(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $cartId = (int)($parameters['id'] ?? 0);

        if ($cartId <= 0) {
            $this->error(
                'شناسه سبد خرید نامعتبر است.',
                422
            );
        }

        $this->ownedCart(
            $cartId,
            $userId
        );

        $currency = Request::post(
            'currency',
            'IRR'
        );

        $status = Request::post(
            'status',
            'active'
        );

        $expiresAt = Request::post(
            'expires_at'
        );

        $validator = new CartValidator();

        $errors = $validator->validate([
            'userId' => $userId,
            'sessionTokenHash' => null,
            'status' => $status,
            'currency' => $currency,
            'expiresAt' => $expiresAt,
        ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات سبد خرید نامعتبر است.',
                422,
                $errors
            );
        }

        $service = new CartService();

        $cart = $service->update(
            $cartId,
            $userId,
            null,
            (string)$status,
            (string)$currency,
            $expiresAt !== null
                ? (string)$expiresAt
                : null
        );
        if ($cart === null) {
            $this->error(
                'سبد خرید موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            ['cart' => $cart],
            'سبد خرید با موفقیت به‌روزرسانی شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 12.35 — Cart Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $cartId = (int)($parameters['id'] ?? 0);

        if ($cartId <= 0) {
            $this->error(
                'شناسه سبد خرید نامعتبر است.',
                422
            );
        }

        $this->ownedCart(
            $cartId,
            $userId
        );

        $status = Request::post(
            'status'
        );

        $validator = new CartValidator();

        $errors = $validator->validate([
            'userId' => $userId,
            'sessionTokenHash' => null,
            'status' => $status,
            'currency' => 'IRR',
            'expiresAt' => null,
        ]);

        if ($errors !== []) {
            $this->error(
                'وضعیت سبد خرید نامعتبر است.',
                422,
                $errors
            );
        }

        $service = new CartService();

        $cart = $service->updateStatus(
            $cartId,
            (string)$status
        );

        if ($cart === null) {
            $this->error(
                'سبد خرید موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            ['cart' => $cart],
            'وضعیت سبد خرید با موفقیت تغییر کرد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 12.36 — Cart Items
    |--------------------------------------------------------------------------
    */

    public function items(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $cartId = (int)(
            $parameters['cartId'] ?? 0
        );

        if ($cartId <= 0) {
            $this->error(
                'شناسه سبد خرید نامعتبر است.',
                422
            );
        }

        $this->ownedCart(
            $cartId,
            $userId
        );

        $service = new CartItemService();

        $items = $service->findByCart(
            $cartId
        );

        $this->success([
            'items' => array_map(
                fn(CartItem $item): array =>
                $this->itemData($item),
                $items
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 12.37 — Add Cart Item
    |--------------------------------------------------------------------------
    */

    public function addItem(): never
    {
        $userId = $this->currentUserId();

        $cartId = (int)Request::post(
            'cart_id',
            0
        );

        $variantId = (int)Request::post(
            'variant_id',
            0
        );

        $quantity = (int)Request::post(
            'quantity',
            1
        );

        $unitPrice = Request::post(
            'unit_price',
            '0.0000'
        );

        $validator = new CartItemValidator();

        $errors = $validator->validate([
            'cartId' => $cartId,
            'variantId' => $variantId,
            'quantity' => $quantity,
            'unitPrice' => $unitPrice,
        ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات آیتم سبد خرید نامعتبر است.',
                422,
                $errors
            );
        }

        $this->ownedCart(
            $cartId,
            $userId
        );

        $service = new CartItemService();

        if (
            $service->existsByCartAndVariant(
                $cartId,
                $variantId
            )
        ) {
            $this->error(
                'این Variant قبلاً در سبد خرید وجود دارد.',
                409
            );
        }

        $item = $service->create(
            $cartId,
            $variantId,
            $quantity,
            (string)$unitPrice
        );

        $this->success(
            ['item' => $item],
            'محصول به سبد خرید اضافه شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 12.38 — Update Cart Item
    |--------------------------------------------------------------------------
    */

    public function updateItem(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $itemId = (int)($parameters['id'] ?? 0);

        if ($itemId <= 0) {
            $this->error(
                'شناسه آیتم سبد خرید نامعتبر است.',
                422
            );
        }

        $service = new CartItemService();

        $item = $service->findById(
            $itemId
        );

        if ($item === null) {
            $this->error(
                'آیتم سبد خرید موردنظر پیدا نشد.',
                404
            );
        }

        $this->ownedCart(
            $item->cartId,
            $userId
        );

        $quantity = (int)Request::post(
            'quantity',
            $item->quantity
        );

        $unitPrice = Request::post(
            'unit_price',
            $item->unitPrice
        );

        $validator = new CartItemValidator();

        $errors = $validator->validate([
            'cartId' => $item->cartId,
            'variantId' => $item->variantId,
            'quantity' => $quantity,
            'unitPrice' => $unitPrice,
        ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات آیتم سبد خرید نامعتبر است.',
                422,
                $errors
            );
        }

        $updated = $service->update(
            $itemId,
            $quantity,
            (string)$unitPrice
        );

        if ($updated === null) {
            $this->error(
                'به‌روزرسانی آیتم سبد خرید انجام نشد.',
                404
            );
        }

        $this->success(
            ['item' => $updated],
            'آیتم سبد خرید با موفقیت به‌روزرسانی شد.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 12.39 — Remove Cart Item
    |--------------------------------------------------------------------------
    */

    public function removeItem(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $itemId = (int)($parameters['id'] ?? 0);

        if ($itemId <= 0) {
            $this->error(
                'شناسه آیتم سبد خرید نامعتبر است.',
                422
            );
        }

        $service = new CartItemService();

        $item = $service->findById(
            $itemId
        );

        if ($item === null) {
            $this->error(
                'آیتم سبد خرید موردنظر پیدا نشد.',
                404
            );
        }

        $this->ownedCart(
            $item->cartId,
            $userId
        );

        if (!$service->delete($itemId)) {
            $this->error(
                'حذف آیتم سبد خرید انجام نشد.',
                500
            );
        }

        $this->success(
            null,
            'آیتم از سبد خرید حذف شد.'
        );
    }
}
