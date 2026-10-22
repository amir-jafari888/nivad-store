<?php

declare(strict_types=1);

final class SmartBundleService
{
    private ProductService $productService;
    private ProductVariantService $variantService;
    private CompatibilityService $compatibilityService;
    private SetupService $setupService;
    private InventoryService $inventoryService;

    public function __construct(
        ?ProductService $productService = null,
        ?ProductVariantService $variantService = null,
        ?CompatibilityService $compatibilityService = null,
        ?SetupService $setupService = null,
        ?InventoryService $inventoryService = null
    ) {
        $this->productService = $productService ?? new ProductService();
        $this->variantService = $variantService ?? new ProductVariantService();
        $this->compatibilityService = $compatibilityService ?? new CompatibilityService();
        $this->setupService = $setupService ?? new SetupService();
        $this->inventoryService = $inventoryService ?? new InventoryService();
    }

    public function findBundle(?int $productId = null, int $limit = 4): ?SmartBundle
    {
        $primary = null;

        if ($productId !== null && $productId > 0) {
            $primary = $this->productService->findActiveById($productId);
        }

        if ($primary === null) {
            $featured = $this->productService->findActive([
                'featured' => true,
                'limit' => 1,
            ]);

            if (is_array($featured) && isset($featured[0])) {
                $primary = $featured[0];
            }
        }

        if ($primary === null) {
            $products = $this->productService->findActive([
                'limit' => 1,
            ]);

            if (is_array($products) && isset($products[0])) {
                $primary = $products[0];
            }
        }

        if ($primary === null) {
            return null;
        }

        $primaryId = (int)($primary->id ?? 0);

        if ($primaryId <= 0) {
            return null;
        }

        $primaryVariant = $this->variantService->findDefaultByProduct($primaryId);

        if ($primaryVariant === null) {
            return null;
        }

        $primaryInventory = $this->inventoryService->findByVariant(
            (int)$primaryVariant->id
        );

        if ($primaryInventory === null || $this->availableQuantity($primaryInventory) <= 0) {
            return null;
        }

        $items = [
            $this->buildBundleItem(
                $primary,
                $primaryVariant,
                'primary',
                $primaryInventory
            ),
        ];

        $deviceContexts = $this->compatibilityService->findDevicesByProduct(
            $primaryId
        );

        $deviceIds = [];

        foreach ($deviceContexts as $device) {
            $deviceId = (int)($device->id ?? 0);

            if ($deviceId > 0) {
                $deviceIds[] = $deviceId;
            }
        }

        $deviceIds = array_values(array_unique($deviceIds));

        if ($deviceIds === []) {
            return $this->buildBundle($items);
        }

        $candidates = $this->setupService->findComplementCandidatesByDevices(
            $deviceIds,
            $primaryId
        );

        if (!is_array($candidates) || $candidates === []) {
            return $this->buildBundle($items);
        }

        $ranked = [];

        foreach ($candidates as $candidate) {
            $candidateProductId = (int)($candidate['product_id'] ?? 0);

            if ($candidateProductId <= 0 || $candidateProductId === $primaryId) {
                continue;
            }

            $product = $this->productService->findActiveById(
                $candidateProductId
            );

            if ($product === null) {
                continue;
            }

            $variantId = (int)($candidate['variant_id'] ?? 0);

            $variant = $variantId > 0
                ? $this->variantService->findActiveById($variantId)
                : $this->variantService->findDefaultByProduct($candidateProductId);

            if ($variant === null) {
                continue;
            }

            $inventory = $this->inventoryService->findByVariant(
                (int)$variant->id
            );

            if ($inventory === null || $this->availableQuantity($inventory) <= 0) {
                continue;
            }

            $key = $candidateProductId . ':' . (int)$variant->id;

            if (isset($ranked[$key])) {
                continue;
            }

            $ranked[$key] = [
                'score' => $this->candidateScore($candidate),
                'product' => $product,
                'variant' => $variant,
                'inventory' => $inventory,
                'relation_type' => (string)($candidate['relation_type'] ?? 'optional'),
            ];
        }

        usort(
            $ranked,
            static fn(array $a, array $b): int =>
            $b['score'] <=> $a['score']
        );

        $maxComplements = max(0, $limit - 1);

        foreach (array_slice($ranked, 0, $maxComplements) as $candidate) {
            $items[] = $this->buildBundleItem(
                $candidate['product'],
                $candidate['variant'],
                $candidate['relation_type'],
                $candidate['inventory']
            );
        }

        return $this->buildBundle($items);
    }

    private function buildBundle(array $items): SmartBundle
    {
        $value = $this->calculateValue($items);

        $primary = $items[0];

        return new SmartBundle(
            (int)$primary['product_id'],
            (string)$primary['name'],
            $items,
            $value['original_total'],
            $value['bundle_total'],
            $value['saving_amount'],
            $value['saving_percentage'],
            (string)$primary['currency']
        );
    }

    private function buildBundleItem(
        object $product,
        object $variant,
        string $relationType,
        object $inventory
    ): array {
        $price = (float)($variant->price ?? 0);
        $compareAtPrice = (float)($variant->compareAtPrice ?? $price);

        return [
            'product_id' => (int)$product->id,
            'name' => (string)($product->name ?? $variant->name ?? ''),
            'slug' => (string)($product->slug ?? ''),
            'variant_id' => (int)$variant->id,
            'variant_name' => (string)($variant->name ?? ''),
            'price' => number_format($price, 2, '.', ''),
            'compare_at_price' => number_format(
                max($compareAtPrice, $price),
                2,
                '.',
                ''
            ),
            'currency' => (string)($variant->currency ?? 'IRR'),
            'relation_type' => $relationType,
            'quantity' => 1,
            'available_quantity' => $this->availableQuantity($inventory),
        ];
    }

    private function calculateValue(array $items): array
    {
        $originalTotal = 0.0;
        $bundleTotal = 0.0;

        foreach ($items as $item) {
            $price = (float)($item['price'] ?? 0);
            $compareAtPrice = (float)(
                $item['compare_at_price'] ?? $price
            );

            $bundleTotal += $price;
            $originalTotal += max($compareAtPrice, $price);
        }

        $savingAmount = max(0, $originalTotal - $bundleTotal);

        $savingPercentage = $originalTotal > 0
            ? ($savingAmount / $originalTotal) * 100
            : 0;

        return [
            'original_total' => number_format(
                $originalTotal,
                2,
                '.',
                ''
            ),
            'bundle_total' => number_format(
                $bundleTotal,
                2,
                '.',
                ''
            ),
            'saving_amount' => number_format(
                $savingAmount,
                2,
                '.',
                ''
            ),
            'saving_percentage' => number_format(
                $savingPercentage,
                2,
                '.',
                ''
            ),
        ];
    }

    private function candidateScore(array $candidate): int
    {
        $relation = strtolower(
            (string)($candidate['relation_type'] ?? '')
        );

        $score = match ($relation) {
            'recommended' => 30,
            'compatible' => 20,
            'optional' => 10,
            default => 0,
        };

        $price = (float)($candidate['price'] ?? 0);
        $compareAtPrice = (float)($candidate['compare_at_price'] ?? $price);

        if ($compareAtPrice > $price) {
            $score += 5;
        }

        return $score;
    }

    private function availableQuantity(object $inventory): int
    {
        $quantity = (int)($inventory->quantity ?? 0);
        $reserved = (int)($inventory->reservedQuantity ?? 0);

        return max(0, $quantity - $reserved);
    }
}
