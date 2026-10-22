<?php

declare(strict_types=1);

final class ComparisonService
{
    private ProductService $products;
    private ProductVariantService $variants;
    private ProductMediaService $media;
    private InventoryService $inventory;
    private ProductSpecificationService $productSpecifications;
    private SpecificationService $specifications;
    private SpecGroupService $groups;
    private CompatibilityService $compatibility;

    public function __construct(
        ?ProductService $products = null,
        ?ProductVariantService $variants = null,
        ?ProductMediaService $media = null,
        ?InventoryService $inventory = null,
        ?ProductSpecificationService $productSpecifications = null,
        ?SpecificationService $specifications = null,
        ?SpecGroupService $groups = null,
        ?CompatibilityService $compatibility = null
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

        $this->productSpecifications =
            $productSpecifications ??
            new ProductSpecificationService();

        $this->specifications =
            $specifications ??
            new SpecificationService();

        $this->groups =
            $groups ??
            new SpecGroupService();

        $this->compatibility =
            $compatibility ??
            new CompatibilityService();
    }

    public function compare(
        array $productIds,
        ?int $deviceId = null
    ): array {
        $ids =
            $this->normalizeIds(
                $productIds
            );

        $items = [];
        $invalid = [];
        $seen = [];

        foreach ($ids as $id) {
            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;

            $product =
                $this->products->findById($id);

            if (
                $product === null ||
                $product->status !== 'active'
            ) {
                $invalid[] = $id;
                continue;
            }

            $items[] =
                $this->productData(
                    $product,
                    $deviceId
                );
        }

        $specifications =
            $this->mergeSpecifications(
                $items
            );

        return [
            'items' => $items,

            'count' =>
            count($items),

            'invalid_product_ids' =>
            $invalid,

            'specifications' =>
            $specifications,

            'comparison' =>
            $this->comparisonSummary(
                $items,
                $specifications
            ),

            'selection' => [
                'max' => 4,

                'ids' =>
                array_map(
                    static fn(array $item): int =>
                    (int)$item['id'],
                    $items
                ),
            ],
        ];
    }

    private function normalizeIds(
        array $ids
    ): array {
        $normalized = [];

        foreach ($ids as $value) {
            if (
                !is_scalar($value) ||
                !ctype_digit((string)$value)
            ) {
                continue;
            }

            $id = (int)$value;

            if ($id > 0) {
                $normalized[] = $id;
            }
        }

        return array_slice(
            array_values(
                array_unique($normalized)
            ),
            0,
            4
        );
    }

    private function productData(
        Product $product,
        ?int $deviceId
    ): array {
        $variants = [];

        foreach (
            $this->variants
                ->findActiveByProduct(
                    $product->id
                )
            as $variant
        ) {
            $inventory =
                $this->inventory
                ->findByVariant(
                    $variant->id
                );

            $availability = null;

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

                    'quantity' =>
                    $inventory->quantity,

                    'reserved_quantity' =>
                    $inventory->reservedQuantity,

                    'available_quantity' =>
                    $available,

                    'low_stock_threshold' =>
                    $inventory->lowStockThreshold,
                ];
            }

            $variants[] = [
                'id' =>
                $variant->id,

                'name' =>
                $variant->name,

                'sku' =>
                $variant->sku,

                'price' =>
                $variant->price,

                'compare_at_price' =>
                $variant->compareAtPrice,

                'currency' =>
                $variant->currency,

                'is_default' =>
                $variant->isDefault,

                'availability' =>
                $availability,
            ];
        }

        $defaultVariant =
            $this->variants
            ->findDefaultByProduct(
                $product->id
            );

        $default = null;

        if ($defaultVariant !== null) {
            foreach ($variants as $variant) {
                if (
                    (int)$variant['id'] ===
                    $defaultVariant->id
                ) {
                    $default = $variant;
                    break;
                }
            }
        }

        $media = [];

        foreach (
            $this->media
                ->findAllByProduct(
                    $product->id
                )
            as $item
        ) {
            if ($item->status !== 'active') {
                continue;
            }

            $media[] = [
                'id' =>
                $item->id,

                'media_type' =>
                $item->mediaType,

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

                'sort_order' =>
                $item->sortOrder,

                'is_primary' =>
                $item->isPrimary,
            ];
        }

        $specifications = [];

        foreach (
            $this->productSpecifications
                ->findAllByProduct(
                    $product->id
                )
            as $value
        ) {
            $spec =
                $this->specifications
                ->findActiveById(
                    $value->specificationId
                );

            if ($spec === null) {
                continue;
            }

            $group =
                $this->groups
                ->findActiveById(
                    $spec->specGroupId
                );

            if ($group === null) {
                continue;
            }

            $specifications[] = [
                'id' =>
                $spec->id,

                'slug' =>
                $spec->slug,

                'name' =>
                $spec->name,

                'group_id' =>
                $group->id,

                'group_name' =>
                $group->name,

                'group_sort_order' =>
                $group->sortOrder,

                'sort_order' =>
                $spec->sortOrder,

                'value_type' =>
                $spec->valueType,

                'unit' =>
                $spec->unit,

                'value' =>
                $this->resolveValue(
                    $value,
                    $spec->valueType
                ),
            ];
        }

        usort(
            $specifications,
            static fn(
                array $a,
                array $b
            ): int =>
            [
                $a['group_sort_order'],
                $a['sort_order'],
                $a['id'],
            ] <=>
                [
                    $b['group_sort_order'],
                    $b['sort_order'],
                    $b['id'],
                ]
        );

        $compatibility = null;

        if (
            $deviceId !== null &&
            $deviceId > 0
        ) {
            $mapping =
                $this->compatibility
                ->findMapping(
                    $product->id,
                    $deviceId
                );

            $compatibility =
                $mapping === null
                ? [
                    'state' =>
                    'unknown',

                    'note' =>
                    null,
                ]
                : [
                    'state' =>
                    $mapping
                        ->compatibilityType,

                    'note' =>
                    $mapping->note,
                ];
        }

        $price =
            $default['price'] ?? null;

        $compareAt =
            $default['compare_at_price'] ?? null;

        $discount = null;

        if (
            is_numeric($price) &&
            is_numeric($compareAt) &&
            (float)$compareAt >
            (float)$price
        ) {
            $discount =
                round(
                    (
                        (
                            (float)$compareAt -
                            (float)$price
                        ) /
                        (float)$compareAt
                    ) * 100,
                    2
                );
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

            'is_featured' =>
            $product->isFeatured,

            'variant' =>
            $default,

            'variants' =>
            $variants,

            'media' =>
            $media,

            'specifications' =>
            $specifications,

            'availability' =>
            $default['availability'] ?? null,

            'compatibility' =>
            $compatibility,

            'decision' => [
                'discount_percent' =>
                $discount,

                'specification_count' =>
                count($specifications),

                'featured' =>
                $product->isFeatured,
            ],
        ];
    }

    private function resolveValue(
        ProductSpecification $value,
        string $type
    ): mixed {
        return match ($type) {
            'number',
            'numeric',
            'decimal' =>
            $value->valueNumber !== null &&
                is_numeric($value->valueNumber)
                ? (float)$value->valueNumber
                : null,

            'boolean',
            'bool' =>
            $value->valueBoolean,

            'date' =>
            $value->valueDate,

            default =>
            $value->valueText,
        };
    }

    private function mergeSpecifications(
        array $items
    ): array {
        $map = [];

        foreach ($items as $item) {
            foreach (
                $item['specifications']
                as $spec
            ) {
                $id =
                    (int)$spec['id'];

                if (!isset($map[$id])) {
                    $map[$id] = [
                        'id' =>
                        $id,

                        'slug' =>
                        $spec['slug'],

                        'name' =>
                        $spec['name'],

                        'group_id' =>
                        $spec['group_id'],

                        'group_name' =>
                        $spec['group_name'],

                        'group_sort_order' =>
                        $spec['group_sort_order'],

                        'sort_order' =>
                        $spec['sort_order'],

                        'value_type' =>
                        $spec['value_type'],

                        'unit' =>
                        $spec['unit'],

                        'values' =>
                        [],
                    ];
                }

                $map[$id]['values'][(int)$item['id']] =
                    $spec['value'];
            }
        }

        $rows =
            array_values($map);

        usort(
            $rows,
            static fn(
                array $a,
                array $b
            ): int =>
            [
                $a['group_sort_order'],
                $a['sort_order'],
                $a['id'],
            ] <=>
                [
                    $b['group_sort_order'],
                    $b['sort_order'],
                    $b['id'],
                ]
        );

        foreach ($rows as &$row) {
            $values =
                array_values(
                    $row['values']
                );

            $row['shared'] =
                count($row['values']) ===
                count($items);

            $row['different'] =
                count($items) > 1 &&
                count(
                    array_unique(
                        array_map(
                            static fn(
                                mixed $value
                            ): string =>
                            is_bool($value)
                                ? (
                                    $value
                                    ? '1'
                                    : '0'
                                )
                                : strval($value ?? ''),
                            $values
                        )
                    )
                ) > 1;
        }

        unset($row);

        return $rows;
    }

    private function comparisonSummary(
        array $items,
        array $specifications
    ): array {
        $prices = [];

        foreach ($items as $item) {
            $price =
                $item['variant']['price']
                ?? null;

            if (is_numeric($price)) {
                $prices[(int)$item['id']] =
                    (float)$price;
            }
        }

        $priceLeader = null;

        if (
            count($prices) ===
            count($items) &&
            count($items) > 1
        ) {
            asort(
                $prices,
                SORT_NUMERIC
            );

            $priceLeader =
                (int)array_key_first(
                    $prices
                );
        }

        return [
            'shared_specification_count' =>
            count(
                array_filter(
                    $specifications,
                    static fn(
                        array $row
                    ): bool =>
                    $row['shared'] === true
                )
            ),

            'different_specification_count' =>
            count(
                array_filter(
                    $specifications,
                    static fn(
                        array $row
                    ): bool =>
                    $row['different'] === true
                )
            ),

            'price_leader_product_id' =>
            $priceLeader,

            /*
             * Intentionally null.
             *
             * Phase 18 must not fabricate
             * an overall winner.
             *
             * Phase 22 owns confidence/reason.
             */
            'overall_winner_product_id' =>
            null,

            'winner_reason' =>
            null,

            'confidence' =>
            null,
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
