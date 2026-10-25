<?php

declare(strict_types=1);

final class StorefrontController extends Controller
{
    private ProductService $products;
    private CategoryService $categories;
    private BrandService $brands;
    private ProductVariantService $variants;
    private ProductMediaService $media;

    private CompatibilityService $compatibility;

    private SetupService $setup;

    private InventoryService $inventory;

    public function __construct(
        ?ProductService $products = null,
        ?CategoryService $categories = null,
        ?BrandService $brands = null,
        ?ProductVariantService $variants = null,
        ?ProductMediaService $media = null,
        ?InventoryService $inventory = null,
        ?CompatibilityService $compatibility = null,
        ?SetupService $setup = null
    ) {
        $this->products =
            $products ??
            new ProductService();

        $this->categories =
            $categories ??
            new CategoryService();

        $this->brands =
            $brands ??
            new BrandService();

        $this->variants =
            $variants ??
            new ProductVariantService();

        $this->media =
            $media ??
            new ProductMediaService();

        $this->inventory =
            $inventory ??
            new InventoryService();

        $this->compatibility =
            $compatibility ??
            new CompatibilityService();

        $this->setup =
            $setup ??
            new SetupService();
    }

    public function products(): never
    {
        $categoryId = $this->positiveInt(
            Request::query('category_id')
        );

        $brandId = $this->positiveInt(
            Request::query('brand_id')
        );

        $items = [];

        foreach ($this->products->findActive() as $product) {
            if (
                $categoryId !== null &&
                $product->categoryId !== $categoryId
            ) {
                continue;
            }

            if (
                $brandId !== null &&
                $product->brandId !== $brandId
            ) {
                continue;
            }

            $items[] = $this->productData($product);
        }

        $this->success(
            [
                'items' => $items,
                'count' => count($items),
            ],
            'محصولات فعال دریافت شد.'
        );
    }

    public function product(): never
    {
        $slug = trim(
            (string) Request::query('slug', '')
        );

        if ($slug === '') {
            $this->error(
                'شناسه محصول ارسال نشده است.',
                422
            );
        }

        $product =
            $this->products->findBySlug($slug);

        if (
            $product === null ||
            $product->status !== 'active'
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $variants = [];

        foreach (
            $this->variants->findActiveByProduct(
                $product->id
            ) as $variant
        ) {
            $inventory =
                $this->inventory->findByVariant(
                    $variant->id
                );

            $availability = null;

            if ($inventory !== null) {
                $availableQuantity =
                    max(
                        0,
                        $inventory->quantity -
                            $inventory->reservedQuantity
                    );

                $availability = [
                    'status' =>
                    $availableQuantity > 0
                        ? 'in_stock'
                        : 'out_of_stock',

                    'quantity' =>
                    $inventory->quantity,

                    'reserved_quantity' =>
                    $inventory->reservedQuantity,

                    'available_quantity' =>
                    $availableQuantity,

                    'low_stock_threshold' =>
                    $inventory->lowStockThreshold,
                ];
            }

            $variants[] = [
                'id' => $variant->id,

                'product_id' =>
                $variant->productId,

                'sku' =>
                $variant->sku,

                'barcode' =>
                $variant->barcode,

                'price' =>
                $variant->price,

                'compare_at_price' =>
                $variant->compareAtPrice,

                'currency' =>
                $variant->currency,

                'name' =>
                $variant->name,

                'is_default' =>
                $variant->isDefault,

                'sort_order' =>
                $variant->sortOrder,

                'availability' =>
                $availability,
            ];
        }

        $data =
            $this->productData(
                $product
            );

        $defaultVariant =
            $this->variants
            ->findDefaultByProduct(
                $product->id
            );

        $defaultAvailability = null;

        if ($defaultVariant !== null) {
            $inventory =
                $this->inventory->findByVariant(
                    $defaultVariant->id
                );

            if ($inventory !== null) {
                $availableQuantity =
                    max(
                        0,
                        $inventory->quantity -
                            $inventory->reservedQuantity
                    );

                $defaultAvailability = [
                    'status' =>
                    $availableQuantity > 0
                        ? 'in_stock'
                        : 'out_of_stock',

                    'quantity' =>
                    $inventory->quantity,

                    'reserved_quantity' =>
                    $inventory->reservedQuantity,

                    'available_quantity' =>
                    $availableQuantity,

                    'low_stock_threshold' =>
                    $inventory->lowStockThreshold,
                ];
            }
        }

        $brand = null;

        if ($product->brandId !== null) {
            $brandEntity =
                $this->brands->findById(
                    $product->brandId
                );

            if ($brandEntity !== null) {
                $brand = [
                    'id' =>
                    $brandEntity->id,

                    'name' =>
                    $brandEntity->name,

                    'slug' =>
                    $brandEntity->slug,
                ];
            }
        }

        $category = null;

        if ($product->categoryId !== null) {
            $categoryEntity =
                $this->categories->findById(
                    $product->categoryId
                );

            if ($categoryEntity !== null) {
                $category = [
                    'id' =>
                    $categoryEntity->id,

                    'name' =>
                    $categoryEntity->name,

                    'slug' =>
                    $categoryEntity->slug,
                ];
            }
        }

        $data['brand'] = $brand;

        $data['category'] = $category;

        $data['variants'] = $variants;

        $data['availability'] =
            $defaultAvailability;

        /*
     * Recommendations / alternatives / related
     *
     * No fake contract is introduced here.
     * These fields remain absent until their
     * real backend contract is audited and defined.
     */

        $this->success(
            $data,
            'جزئیات محصول دریافت شد.'
        );
    }

    public function categories(): never
    {
        $items = [];

        foreach (
            $this->categories->findActive()
            as $category
        ) {
            $items[] = [
                'id' => $category->id,
                'parent_id' => $category->parentId,
                'name' => $category->name,
                'slug' => $category->slug,
                'meta_title' => $category->metaTitle,
                'meta_description' => $category->metaDescription,
                'description' =>
                $category->description,
                'sort_order' =>
                $category->sortOrder,
            ];
        }

        $this->success(
            [
                'items' => $items,
                'count' => count($items),
            ],
            'دسته‌بندی‌های فعال دریافت شد.'
        );
    }

    public function category(): never
    {
        $slug = trim(
            (string) Request::query('slug', '')
        );

        $category =
            $slug !== ''
            ? $this->categories->findBySlug($slug)
            : null;

        if (
            $category === null ||
            $category->status !== 'active'
        ) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $items = [];

        foreach (
            $this->products->findActive()
            as $product
        ) {
            if (
                $product->categoryId ===
                $category->id
            ) {
                $items[] =
                    $this->productData($product);
            }
        }

        $this->success(
            [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'meta_title' => $category->metaTitle,
                    'meta_description' => $category->metaDescription,
                    'description' =>
                    $category->description,
                ],
                'items' => $items,
                'count' => count($items),
            ],
            'دسته‌بندی دریافت شد.'
        );
    }

    public function brands(): never
    {
        $items = [];

        foreach (
            $this->brands->findActive()
            as $brand
        ) {
            $items[] = [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' =>
                $brand->description,
                'sort_order' =>
                $brand->sortOrder,
            ];
        }

        $this->success(
            [
                'items' => $items,
                'count' => count($items),
            ],
            'برندهای فعال دریافت شد.'
        );
    }

    public function brand(): never
    {
        $slug = trim(
            (string) Request::query('slug', '')
        );

        $brand =
            $slug !== ''
            ? $this->brands->findBySlug($slug)
            : null;

        if (
            $brand === null ||
            $brand->status !== 'active'
        ) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $items = [];

        foreach (
            $this->products->findActive()
            as $product
        ) {
            if (
                $product->brandId ===
                $brand->id
            ) {
                $items[] =
                    $this->productData($product);
            }
        }

        $this->success(
            [
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' =>
                    $brand->description,
                ],
                'items' => $items,
                'count' => count($items),
            ],
            'برند دریافت شد.'
        );
    }

    private function productMediaData(
        Product $product
    ): array {
        $media = [];

        foreach ($this->media->findAllByProduct($product->id) as $item) {
            if ($item->status !== 'active' || $item->deletedAt !== null) {
                continue;
            }

            $media[] = [
                'id' => $item->id,
                'product_id' => $item->productId,
                'variant_id' => $item->variantId,
                'media_type' => $item->mediaType,
                'url' => $this->publicAssetPath($item->filePath),
                'thumbnail_url' => $this->publicAssetPath($item->thumbnailPath),
                'alt' => $item->altText,
                'sort_order' => $item->sortOrder,
                'is_primary' => $item->isPrimary,
            ];
        }

        usort(
            $media,
            static fn(array $left, array $right): int =>
                ((int) $right['is_primary'] <=> (int) $left['is_primary'])
                ?: ((int) $left['sort_order'] <=> (int) $right['sort_order'])
        );

        return $media;
    }

    private function productData(
        Product $product
    ): array {
        $variant =
            $this->variants
            ->findDefaultByProduct(
                $product->id
            );

        return [
            'id' => $product->id,
            'category_id' =>
            $product->categoryId,
            'brand_id' =>
            $product->brandId,
            'name' => $product->name,
            'slug' => $product->slug,
            'meta_title' => $product->metaTitle,
            'meta_description' => $product->metaDescription,
            'short_description' =>
            $product->shortDescription,
            'description' =>
            $product->description,
            'is_featured' =>
            $product->isFeatured,
            'variant' =>
            $variant === null
                ? null
                : [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => $variant->price,
                    'compare_at_price' =>
                    $variant->compareAtPrice,
                    'currency' =>
                    $variant->currency,
                ],
            'media' => $this->productMediaData($product),
        ];
    }

    private function publicAssetPath(
        ?string $path
    ): ?string {
        $path =
            trim(
                (string) $path
            );

        if ($path === '') {
            return null;
        }

        if (
            filter_var(
                $path,
                FILTER_VALIDATE_URL
            )
        ) {
            return $path;
        }

        return url($path);
    }

    private function positiveInt(
        mixed $value
    ): ?int {
        if (
            !is_scalar($value) ||
            !ctype_digit((string) $value)
        ) {
            return null;
        }

        $value = (int) $value;

        return $value > 0
            ? $value
            : null;
    }

    public function completeYourSetup(): never
    {
        $productId = $this->positiveInt(
            Request::query('product_id')
        );

        if ($productId === null) {
            $this->error(
                'شناسه محصول ارسال نشده است.',
                422
            );
        }

        $product =
            $this->products->findById(
                $productId
            );

        if (
            $product === null ||
            $product->status !== 'active'
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $contexts =
            $this->compatibility
            ->findDevicesByProduct(
                $productId
            );

        if ($contexts === []) {
            $this->success(
                [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                    ],
                    'context' => [],
                    'items' => [],
                    'count' => 0,
                ],
                'برای این محصول ستاپ مکملی ثبت نشده است.'
            );
        }

        $deviceIds = array_values(
            array_unique(
                array_map(
                    static fn(array $context): int =>
                    (int)$context['id'],
                    $contexts
                )
            )
        );

        $candidates =
            $this->setup
            ->findComplementCandidatesByDevices(
                $deviceIds,
                $productId
            );

        $contextByDevice = [];

        foreach ($contexts as $context) {
            $deviceId = (int)$context['id'];

            if (!isset($contextByDevice[$deviceId])) {
                $contextByDevice[$deviceId] = [
                    'device_id' => $deviceId,
                    'device_name' =>
                    (string)$context['name'],
                    'compatibility_type' =>
                    (string)$context['compatibility_type'],
                    'compatibility_note' =>
                    $context['compatibility_note'] !== null
                        ? (string)$context['compatibility_note']
                        : null,
                ];
            }
        }

        $itemsByProduct = [];

        foreach ($candidates as $candidate) {
            $candidateProductId =
                (int)$candidate['product_id'];

            $deviceId =
                (int)$candidate['setup_device_id'];

            $context =
                $contextByDevice[$deviceId] ??
                null;

            if ($context === null) {
                continue;
            }

            $relationType =
                (string)$candidate['relation_type'];

            $availabilityStatus = null;

            if ($candidate['quantity'] !== null) {
                $availableQuantity =
                    max(
                        0,
                        (int)$candidate['quantity'] -
                            (int)$candidate['reserved_quantity']
                    );

                $availabilityStatus =
                    $availableQuantity > 0
                    ? 'in_stock'
                    : 'out_of_stock';
            }

            $candidateItem = [
                'id' => $candidateProductId,

                'name' =>
                (string)$candidate['name'],

                'slug' =>
                (string)$candidate['slug'],

                'short_description' =>
                $candidate['short_description'] !== null
                    ? (string)$candidate['short_description']
                    : null,

                'brand' => [
                    'id' =>
                    $candidate['brand_id'] !== null
                        ? (int)$candidate['brand_id']
                        : null,

                    'name' =>
                    $candidate['brand_name'] !== null
                        ? (string)$candidate['brand_name']
                        : null,
                ],

                'variant' => [
                    'id' =>
                    $candidate['variant_id'] !== null
                        ? (int)$candidate['variant_id']
                        : null,

                    'name' =>
                    $candidate['variant_name'] !== null
                        ? (string)$candidate['variant_name']
                        : null,

                    'price' =>
                    $candidate['price'] !== null
                        ? (string)$candidate['price']
                        : null,

                    'compare_at_price' =>
                    $candidate['compare_at_price'] !== null
                        ? (string)$candidate['compare_at_price']
                        : null,

                    'currency' =>
                    $candidate['currency'] !== null
                        ? (string)$candidate['currency']
                        : null,

                    'availability' => [
                        'status' =>
                        $availabilityStatus,
                    ],
                ],

                'relation_type' => $relationType,

                'setup_device_id' => $deviceId,

                'setup_device_name' =>
                $context['device_name'],

                'context_compatibility_type' =>
                $context['compatibility_type'],

                'compatibility_note' =>
                $context['compatibility_note'],

                'recommendation_state' =>
                match ($context['compatibility_type']) {
                    ProductCompatibility::TYPE_COMPATIBLE =>
                    'compatible',

                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE =>
                    'partially_compatible',

                    ProductCompatibility::TYPE_NOT_COMPATIBLE =>
                    'not_compatible',

                    default =>
                    'unknown',
                },

                'compatibility_certainty' =>
                match ($context['compatibility_type']) {
                    ProductCompatibility::TYPE_COMPATIBLE =>
                    'confirmed',

                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE =>
                    'partial',

                    ProductCompatibility::TYPE_NOT_COMPATIBLE =>
                    'blocked',

                    default =>
                    'unknown',
                },
            ];

            if (
                !isset(
                    $itemsByProduct[$candidateProductId]
                )
            ) {
                $itemsByProduct[$candidateProductId] = $candidateItem;

                continue;
            }

            $existing =
                $itemsByProduct[$candidateProductId];

            $relationRank = [
                'recommended' => 0,
                'compatible' => 1,
                'optional' => 2,
            ];

            $existingRank =
                $relationRank[$existing['relation_type']] ?? 99;

            $candidateRank =
                $relationRank[$relationType] ?? 99;

            if ($candidateRank < $existingRank) {
                $itemsByProduct[$candidateProductId] = $candidateItem;
            }
        }

        $items =
            array_values(
                $itemsByProduct
            );

        $this->success(
            [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                ],

                'context' =>
                array_values($contextByDevice),

                'items' => $items,

                'count' => count($items),
            ],
            'پیشنهادهای تکمیل ستاپ دریافت شد.'
        );
    }
}
