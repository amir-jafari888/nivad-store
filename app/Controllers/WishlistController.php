<?php

declare(strict_types=1);

final class WishlistController extends Controller
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

    private function ownedWishlist(
        int $wishlistId,
        int $userId
    ): Wishlist {
        $wishlist = (new WishlistService())
            ->findById($wishlistId);

        if ($wishlist === null) {
            $this->error(
                'Wishlist موردنظر پیدا نشد.',
                404
            );
        }

        if ($wishlist->userId !== $userId) {
            $this->error(
                'دسترسی به این Wishlist مجاز نیست.',
                403
            );
        }

        return $wishlist;
    }

    private function itemData(WishlistItem $item): array
    {
        $variant = $this->variants->findById($item->variantId);

        $product = $variant !== null
            ? $this->products->findById($variant->productId)
            : null;

        return [
            'id' => $item->id,
            'wishlist_id' => $item->wishlistId,
            'variant_id' => $item->variantId,
            'created_at' => $item->createdAt,

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
                    'short_description' => $product->shortDescription,
                ],
        ];
    }

    public function index(): never
    {
        $userId = $this->currentUserId();

        $wishlists = (new WishlistService())
            ->findAllByUser($userId);

        $items = [];

        $itemService = new WishlistItemService();

        foreach ($wishlists as $wishlist) {
            $wishlistItems = $itemService
                ->findByWishlist($wishlist->id);

            $items[(string) $wishlist->id] = array_map(
                fn(WishlistItem $item): array =>
                $this->itemData($item),
                $wishlistItems
            );
        }

        $this->success([
            'wishlists' => $wishlists,
            'items' => $items,
        ]);
    }

    public function store(): never
    {
        $userId = $this->currentUserId();

        $name = Request::post(
            'name',
            'علاقه‌مندی‌ها'
        );

        $isDefault = Request::post(
            'is_default',
            true
        );

        if (is_string($isDefault)) {
            $isDefault = in_array(
                $isDefault,
                ['1', 'true'],
                true
            );
        } elseif (is_int($isDefault)) {
            $isDefault = $isDefault === 1;
        }

        $errors = (new WishlistValidator())
            ->validate([
                'userId' => $userId,
                'name' => $name,
                'isDefault' => $isDefault,
            ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Wishlist نامعتبر است.',
                422,
                $errors
            );
        }

        $service = new WishlistService();

        $normalizedName = trim((string) $name);

        if ($service->existsByUserAndName(
            $userId,
            $normalizedName
        )) {
            $this->error(
                'Wishlist با این نام قبلاً وجود دارد.',
                409
            );
        }

        $wishlist = $service->create(
            $userId,
            $normalizedName,
            (bool) $isDefault
        );

        $this->success(
            [
                'wishlist' => $wishlist,
            ],
            'Wishlist با موفقیت ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $wishlistId = (int) (
            $parameters['id'] ?? 0
        );

        if ($wishlistId <= 0) {
            $this->error(
                'شناسه Wishlist نامعتبر است.',
                422
            );
        }

        $this->ownedWishlist(
            $wishlistId,
            $userId
        );

        $name = Request::post('name');

        $isDefault = Request::post(
            'is_default',
            false
        );

        if (is_string($isDefault)) {
            $isDefault = in_array(
                $isDefault,
                ['1', 'true'],
                true
            );
        } elseif (is_int($isDefault)) {
            $isDefault = $isDefault === 1;
        }

        $normalizedName = trim((string) $name);

        $errors = (new WishlistValidator())
            ->validate([
                'userId' => $userId,
                'name' => $normalizedName,
                'isDefault' => $isDefault,
            ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Wishlist نامعتبر است.',
                422,
                $errors
            );
        }

        $service = new WishlistService();

        $existing = $service->findById(
            $wishlistId
        );

        if (
            $existing !== null &&
            $existing->name !== $normalizedName &&
            $service->existsByUserAndName(
                $userId,
                $normalizedName
            )
        ) {
            $this->error(
                'Wishlist با این نام قبلاً وجود دارد.',
                409
            );
        }

        $wishlist = $service->update(
            $wishlistId,
            $normalizedName,
            (bool) $isDefault
        );

        if ($wishlist === null) {
            $this->error(
                'Wishlist موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            [
                'wishlist' => $wishlist,
            ],
            'Wishlist با موفقیت به‌روزرسانی شد.'
        );
    }

    public function setDefault(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $wishlistId = (int) (
            $parameters['id'] ?? 0
        );

        if ($wishlistId <= 0) {
            $this->error(
                'شناسه Wishlist نامعتبر است.',
                422
            );
        }

        $this->ownedWishlist(
            $wishlistId,
            $userId
        );

        $wishlist = (new WishlistService())
            ->setDefault($wishlistId);

        if ($wishlist === null) {
            $this->error(
                'Wishlist موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            [
                'wishlist' => $wishlist,
            ],
            'Wishlist پیش‌فرض با موفقیت تغییر کرد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $wishlistId = (int) (
            $parameters['id'] ?? 0
        );

        if ($wishlistId <= 0) {
            $this->error(
                'شناسه Wishlist نامعتبر است.',
                422
            );
        }

        $wishlist = $this->ownedWishlist(
            $wishlistId,
            $userId
        );

        if ($wishlist->isDefault) {
            $this->error(
                'Wishlist پیش‌فرض قابل حذف نیست.',
                409
            );
        }

        $deleted = (new WishlistService())
            ->delete($wishlistId);

        if (!$deleted) {
            $this->error(
                'حذف Wishlist انجام نشد.',
                500
            );
        }

        $this->success(
            [],
            'Wishlist با موفقیت حذف شد.'
        );
    }

    public function items(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $wishlistId = (int) (
            $parameters['wishlistId'] ?? 0
        );

        $this->ownedWishlist(
            $wishlistId,
            $userId
        );

        $items = (new WishlistItemService())
            ->findByWishlist($wishlistId);

        $this->success([
            'items' => array_map(
                fn(WishlistItem $item): array =>
                $this->itemData($item),
                $items
            ),
        ]);
    }

    public function addItem(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $wishlistId = (int) Request::post(
            'wishlist_id',
            0
        );

        $variantId = (int) Request::post(
            'variant_id',
            0
        );

        $this->ownedWishlist(
            $wishlistId,
            $userId
        );

        $errors = (new WishlistItemValidator())
            ->validate([
                'wishlistId' => $wishlistId,
                'variantId' => $variantId,
            ]);

        if ($errors !== []) {
            $this->error(
                'اطلاعات Wishlist Item نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            (new ProductVariantService())
            ->findById($variantId) === null
        ) {
            $this->error(
                'Variant موردنظر پیدا نشد.',
                404
            );
        }

        $service = new WishlistItemService();

        if (
            $service->existsByWishlistAndVariant(
                $wishlistId,
                $variantId
            )
        ) {
            $this->error(
                'این Variant قبلاً در Wishlist وجود دارد.',
                409
            );
        }

        $item = $service->create(
            $wishlistId,
            $variantId
        );

        $this->success(
            [
                'item' => $this->itemData($item),
            ],
            'محصول به Wishlist اضافه شد.'
        );
    }

    public function removeItem(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $itemId = (int) (
            $parameters['id'] ?? 0
        );

        if ($itemId <= 0) {
            $this->error(
                'شناسه Wishlist Item نامعتبر است.',
                422
            );
        }

        $itemService = new WishlistItemService();

        $item = $itemService->findById(
            $itemId
        );

        if ($item === null) {
            $this->error(
                'Wishlist Item موردنظر پیدا نشد.',
                404
            );
        }

        $this->ownedWishlist(
            $item->wishlistId,
            $userId
        );

        if (!$itemService->delete($itemId)) {
            $this->error(
                'حذف Wishlist Item انجام نشد.',
                500
            );
        }

        $this->success(
            [],
            'محصول از Wishlist حذف شد.'
        );
    }
}
