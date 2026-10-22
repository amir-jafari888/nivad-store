<?php

declare(strict_types=1);

final class AlternativeService
{
    private ProductService $products;
    private ProductVariantService $variants;
    private ProductMediaService $media;
    private InventoryService $inventory;
    private CompatibilityService $compatibility;
    private ProductSpecificationService $productSpecifications;

    public function __construct(
        ?ProductService $products = null,
        ?ProductVariantService $variants = null,
        ?ProductMediaService $media = null,
        ?InventoryService $inventory = null,
        ?CompatibilityService $compatibility = null,
        ?ProductSpecificationService $productSpecifications = null
    ) {
        $this->products =
            $products ??
            new ProductService();

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

        $this->productSpecifications =
            $productSpecifications ??
            new ProductSpecificationService();
    }

    public function find(int $productId): array
    {
        if ($productId < 1) {
            return $this->emptyResult();
        }

        $product =
            $this->products->findById(
                $productId
            );

        if (
            $product === null ||
            $product->status !== 'active'
        ) {
            return [
                'current_product' => null,
                'problem' => [
                    'state' =>
                    'invalid_product',

                    'reason_code' =>
                    'invalid_product',

                    'message' =>
                    'محصول موردنظر پیدا نشد.',
                ],
                'alternatives' => [],
                'count' => 0,
                'ranking' =>
                $this->rankingContract(),
            ];
        }

        /*
         * IMPORTANT:
         * All real compatibility mappings are required here.
         * findDevicesByProduct() intentionally excludes
         * not_compatible and therefore cannot be used for
         * problem detection.
         */
        $contexts =
            $this->compatibility
            ->findAllMappingsByProduct(
                $productId
            );

        $current =
            $this->currentProductData(
                $product
            );

        $problem =
            $this->resolveProblem(
                $contexts
            );

        $currentSpecIds =
            array_values(
                array_map(
                    static fn(
                        ProductSpecification $item
                    ): int =>
                    $item->specificationId,
                    $this
                        ->productSpecifications
                        ->findAllByProduct(
                            $productId
                        )
                )
            );

        /*
         * Candidate source:
         * real Compatibility Engine only.
         *
         * Never use SetupRepository here because
         * Setup Complement != Alternative.
         */
        $candidateMap = [];

        foreach ($contexts as $context) {
            $deviceId =
                (int)($context['id'] ?? 0);

            if ($deviceId < 1) {
                continue;
            }

            $candidates =
                $this->compatibility
                ->findCompatibleProducts(
                    $deviceId,
                    ProductCompatibility::TYPE_COMPATIBLE
                );

            foreach ($candidates as $candidate) {
                $candidateId =
                    (int)(
                        $candidate['product_id'] ??
                        0
                    );

                if (
                    $candidateId < 1 ||
                    $candidateId === $productId
                ) {
                    continue;
                }

                $candidateMap[$candidateId][] = [
                    'device_id' =>
                    $deviceId,

                    'device_name' =>
                    (string)(
                        $context['name'] ??
                        ''
                    ),

                    'device_model' =>
                    $context['model'] !== null
                        ? (string)$context['model']
                        : null,

                    'compatibility_type' =>
                    (string)(
                        $candidate['compatibility_type'] ?? ''
                    ),

                    'note' =>
                    $candidate['note'] !== null
                        ? (string)$candidate['note']
                        : null,

                    'sort_order' =>
                    (int)(
                        $candidate['sort_order'] ??
                        0
                    ),
                ];
            }
        }

        $alternatives = [];

        foreach (
            $candidateMap as $candidateId => $matches
        ) {
            $candidateProduct =
                $this->products->findById(
                    (int)$candidateId
                );

            if (
                $candidateProduct === null ||
                $candidateProduct->status !== 'active'
            ) {
                continue;
            }

            $alternatives[] =
                $this->alternativeData(
                    $candidateProduct,
                    $matches,
                    $current,
                    $currentSpecIds
                );
        }

        usort(
            $alternatives,
            static function (
                array $a,
                array $b
            ): int {
                return [
                    -(int)$a['ranking']['score'],

                    -(int)(
                        $a['ranking']['compatible_device_count']
                    ),

                    -(int)(
                        $a['ranking']['in_stock']
                    ),

                    -(int)(
                        $a['ranking']['shared_specification_count']
                    ),

                    (int)(
                        $a['ranking']['sort_order']
                    ),

                    (int)$a['id'],
                ] <=> [
                    -(int)$b['ranking']['score'],

                    -(int)(
                        $b['ranking']['compatible_device_count']
                    ),

                    -(int)(
                        $b['ranking']['in_stock']
                    ),

                    -(int)(
                        $b['ranking']['shared_specification_count']
                    ),

                    (int)(
                        $b['ranking']['sort_order']
                    ),

                    (int)$b['id'],
                ];
            }
        );

        foreach (
            $alternatives as $index => &$alternative
        ) {
            $alternative['rank'] =
                $index + 1;
        }

        unset($alternative);

        $alternatives =
            array_slice(
                $alternatives,
                0,
                6
            );

        return [
            'current_product' =>
            $current,

            'problem' =>
            $problem,

            'alternatives' =>
            $alternatives,

            'count' =>
            count($alternatives),

            'ranking' =>
            $this->rankingContract(),
        ];
    }

    private function currentProductData(
        Product $product
    ): array {
        $defaultVariant =
            $this->variants
            ->findDefaultByProduct(
                $product->id
            );

        $availability = null;

        if ($defaultVariant !== null) {
            $inventory =
                $this->inventory
                ->findByVariant(
                    $defaultVariant->id
                );

            if ($inventory !== null) {
                $available =
                    max(
                        0,
                        $inventory->quantity -
                            $inventory->reservedQuantity
                    );

                $availability = [
                    'status' =>
                    $available > 0
                        ? 'in_stock'
                        : 'out_of_stock',

                    'available_quantity' =>
                    $available,
                ];
            }
        }

        return [
            'id' =>
            $product->id,

            'name' =>
            $product->name,

            'slug' =>
            $product->slug,

            'status' =>
            $product->status,

            'variant' =>
            $defaultVariant === null
                ? null
                : [
                    'id' =>
                    $defaultVariant->id,

                    'price' =>
                    $defaultVariant->price,

                    'currency' =>
                    $defaultVariant->currency,

                    'availability' =>
                    $availability,
                ],
        ];
    }

    private function resolveProblem(
        array $contexts
    ): array {
        foreach ($contexts as $context) {
            if (
                ($context['compatibility_type'] ?? null) ===
                ProductCompatibility::TYPE_NOT_COMPATIBLE
            ) {
                return [
                    'state' =>
                    'not_compatible',

                    'reason_code' =>
                    'incompatible',

                    'message' =>
                    $this->problemMessage(
                        $context,
                        'این محصول برای دستگاه انتخاب‌شده سازگار ثبت نشده است.'
                    ),

                    'device_id' =>
                    (int)(
                        $context['id'] ??
                        0
                    ),

                    'device_name' =>
                    (string)(
                        $context['name'] ??
                        ''
                    ),

                    'note' =>
                    $context['compatibility_note'] ?? null,
                ];
            }
        }

        foreach ($contexts as $context) {
            if (
                ($context['compatibility_type'] ?? null) ===
                ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE
            ) {
                return [
                    'state' =>
                    'partially_compatible',

                    'reason_code' =>
                    'partial_compatibility',

                    'message' =>
                    $this->problemMessage(
                        $context,
                        'این محصول برای دستگاه انتخاب‌شده سازگاری کامل ندارد.'
                    ),

                    'device_id' =>
                    (int)(
                        $context['id'] ??
                        0
                    ),

                    'device_name' =>
                    (string)(
                        $context['name'] ??
                        ''
                    ),

                    'note' =>
                    $context['compatibility_note'] ?? null,
                ];
            }
        }

        if ($contexts !== []) {
            return [
                'state' =>
                'no_proven_problem',

                'reason_code' =>
                'no_proven_problem',

                'message' =>
                'برای این محصول ناسازگاری یا مشکل مشخصی ثبت نشده است.',
            ];
        }

        return [
            'state' =>
            'unknown',

            'reason_code' =>
            'unknown_context',

            'message' =>
            'اطلاعات کافی برای تشخیص مشکل و پیشنهاد جایگزین وجود ندارد.',
        ];
    }

    private function problemMessage(
        array $context,
        string $fallback
    ): string {
        $deviceName =
            trim(
                (string)(
                    $context['name'] ??
                    ''
                )
            );

        if ($deviceName === '') {
            return $fallback;
        }

        return
            $deviceName .
            ' — ' .
            $fallback;
    }

    private function alternativeData(
        Product $product,
        array $matches,
        array $current,
        array $currentSpecIds
    ): array {
        $defaultVariant =
            $this->variants
            ->findDefaultByProduct(
                $product->id
            );

        $availability = null;
        $inStock = 0;

        if ($defaultVariant !== null) {
            $inventory =
                $this->inventory
                ->findByVariant(
                    $defaultVariant->id
                );

            if ($inventory !== null) {
                $available =
                    max(
                        0,
                        $inventory->quantity -
                            $inventory->reservedQuantity
                    );

                $inStock =
                    $available > 0
                    ? 1
                    : 0;

                $availability = [
                    'status' =>
                    $available > 0
                        ? 'in_stock'
                        : 'out_of_stock',

                    'available_quantity' =>
                    $available,
                ];
            }
        }

        $media = null;

        foreach (
            $this->media->findAllByProduct(
                $product->id
            ) as $item
        ) {
            if ($item->status !== 'active') {
                continue;
            }

            $candidateMedia = [
                'url' =>
                $this->asset(
                    $item->filePath
                ),

                'thumbnail_url' =>
                $this->asset(
                    $item->thumbnailPath
                ),

                'alt' =>
                $item->altText,
            ];

            if ($media === null) {
                $media =
                    $candidateMedia;
            }

            if ($item->isPrimary) {
                $media =
                    $candidateMedia;

                break;
            }
        }

        $deviceIds =
            array_values(
                array_unique(
                    array_map(
                        static fn(
                            array $match
                        ): int =>
                        (int)$match['device_id'],
                        $matches
                    )
                )
            );

        $notes = [];

        foreach ($matches as $match) {
            $note =
                trim(
                    (string)(
                        $match['note'] ??
                        ''
                    )
                );

            if ($note !== '') {
                $notes[] = $note;
            }
        }

        $currentPrice =
            $current['variant']['price'] ?? null;

        $candidatePrice =
            $defaultVariant?->price;

        $lowerPrice =
            is_numeric($currentPrice) &&
            is_numeric($candidatePrice) &&
            (float)$candidatePrice <
            (float)$currentPrice;

        $candidateSpecIds =
            array_values(
                array_map(
                    static fn(
                        ProductSpecification $item
                    ): int =>
                    $item->specificationId,
                    $this
                        ->productSpecifications
                        ->findAllByProduct(
                            $product->id
                        )
                )
            );

        $sharedSpecCount =
            count(
                array_intersect(
                    $currentSpecIds,
                    $candidateSpecIds
                )
            );

        /*
         * Deterministic ranking.
         *
         * Compatibility and availability dominate.
         * Specification overlap is only used when
         * actual specification records exist.
         */
        $score =
            count($deviceIds) * 100 +
            ($inStock * 20) +
            ($sharedSpecCount * 2) +
            ($lowerPrice ? 10 : 0);

        $reasonParts = [];

        $reasonParts[] =
            'سازگار با ' .
            count($deviceIds) .
            ' دستگاه مرتبط';

        if ($inStock === 1) {
            $reasonParts[] =
                'موجود';
        }

        if ($sharedSpecCount > 0) {
            $reasonParts[] =
                $sharedSpecCount .
                ' مشخصه مشترک';
        }

        if ($lowerPrice) {
            $reasonParts[] =
                'قیمت پایین‌تر از محصول فعلی';
        }

        return [
            'id' =>
            $product->id,

            'name' =>
            $product->name,

            'slug' =>
            $product->slug,

            'short_description' =>
            $product->shortDescription,

            'brand' => [
                'id' =>
                $product->brandId,
            ],

            'media' =>
            $media,

            'variant' =>
            $defaultVariant === null
                ? null
                : [
                    'id' =>
                    $defaultVariant->id,

                    'name' =>
                    $defaultVariant->name,

                    'price' =>
                    $defaultVariant->price,

                    'compare_at_price' =>
                    $defaultVariant->compareAtPrice,

                    'currency' =>
                    $defaultVariant->currency,

                    'availability' =>
                    $availability,
                ],

            'compatibility' => [
                'state' =>
                'compatible',

                'device_ids' =>
                $deviceIds,

                'notes' =>
                array_values(
                    array_unique(
                        $notes
                    )
                ),
            ],

            'reason' => [
                'code' =>
                'compatible_alternative',

                'text' =>
                implode(
                    ' · ',
                    $reasonParts
                ),

                'lower_price' =>
                $lowerPrice,

                'shared_specification_count' =>
                $sharedSpecCount,
            ],

            'ranking' => [
                'score' =>
                $score,

                'compatible_device_count' =>
                count($deviceIds),

                'shared_specification_count' =>
                $sharedSpecCount,

                'in_stock' =>
                $inStock,

                'lower_price' =>
                $lowerPrice,

                'sort_order' =>
                min(
                    array_map(
                        static fn(
                            array $match
                        ): int =>
                        (int)$match['sort_order'],
                        $matches
                    )
                ),
            ],
        ];
    }

    private function rankingContract(): array
    {
        return [
            'deterministic' =>
            true,

            'criteria' => [
                'compatible_device_count',
                'availability',
                'shared_specification_count',
                'lower_price_when_proven',
                'sort_order',
                'product_id',
            ],

            /*
             * Phase 22 owns recommendation
             * confidence. Do not fabricate it here.
             */
            'confidence' =>
            null,

            'overall_winner' =>
            null,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'current_product' =>
            null,

            'problem' => [
                'state' =>
                'unknown',

                'reason_code' =>
                'invalid_context',

                'message' =>
                'اطلاعات کافی برای پیشنهاد جایگزین وجود ندارد.',
            ],

            'alternatives' =>
            [],

            'count' =>
            0,

            'ranking' =>
            $this->rankingContract(),
        ];
    }

    private function asset(
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)$path
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
}
