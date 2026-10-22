<?php

declare(strict_types=1);

final class BudgetOptimizerService
{
    private CompatibilityService $compatibility;
    private ProductService $products;
    private ProductVariantRepository $variants;
    private InventoryService $inventory;

    public function __construct(
        ?CompatibilityService $compatibility = null,
        ?ProductService $products = null,
        ?ProductVariantRepository $variants = null,
        ?InventoryService $inventory = null
    ) {
        $this->compatibility =
            $compatibility ?? new CompatibilityService();

        $this->products =
            $products ?? new ProductService();

        $this->variants =
            $variants ?? new ProductVariantRepository();

        $this->inventory =
            $inventory ?? new InventoryService();
    }

    public function optimize(
        int $deviceId,
        string $budget
    ): array {
        $deviceId = (int) $deviceId;
        $budget = trim($budget);

        if ($deviceId < 1) {
            return $this->emptyResult(
                'invalid_device'
            );
        }

        if (
            $budget === '' ||
            !is_numeric($budget) ||
            (float) $budget < 0
        ) {
            return $this->emptyResult(
                'invalid_budget'
            );
        }

        $device =
            $this->compatibility->findDeviceById(
                $deviceId
            );

        if ($device === null) {
            return $this->emptyResult(
                'device_not_found'
            );
        }

        if (
            property_exists($device, 'status') &&
            $device->status !== CompatibilityDevice::STATUS_ACTIVE
        ) {
            return $this->emptyResult(
                'device_not_active'
            );
        }

        $rawCandidates =
            $this->compatibility->findCompatibleProducts(
                $deviceId
            );

        $candidates =
            $this->normalizeCandidates(
                $rawCandidates,
                $deviceId
            );

        $currency =
            $this->resolveCurrency(
                $candidates
            );

        if ($currency === null) {
            return [
                'success' => true,
                'state' => 'currency_unavailable',
                'device' => $this->deviceData($device),
                'budget' => $this->decimal($budget),
                'currency' => null,
                'candidates' => [],
                'selected' => [],
                'total' => '0',
                'remaining' => $this->decimal($budget),
                'within_budget' => true,
                'trade_off' => null,
                'message' => 'واحد پول قابل اتکا برای بهینه‌سازی پیدا نشد.',
            ];
        }

        $currencyCandidates =
            array_values(
                array_filter(
                    $candidates,
                    static function (array $candidate) use ($currency): bool {
                        return $candidate['currency'] === $currency;
                    }
                )
            );

        $ranked =
            $this->rankCandidates(
                $currencyCandidates
            );

        $selection =
            $this->selectWithinBudget(
                $ranked,
                $budget
            );

        $total =
            $this->sumPrices(
                $selection['selected']
            );

        $budgetDecimal =
            (float) $budget;

        $totalDecimal =
            (float) $total;

        return [
            'success' => true,
            'state' => $selection['state'],
            'device' => $this->deviceData($device),
            'budget' => $this->decimal($budget),
            'currency' => $currency,
            'candidates' => $ranked,
            'selected' => $selection['selected'],
            'total' => $total,
            'remaining' => $this->decimal(
                max(0, $budgetDecimal - $totalDecimal)
            ),
            'within_budget' =>
            $totalDecimal <= $budgetDecimal,
            'trade_off' =>
            $selection['trade_off'],
            'message' =>
            $selection['message'],
        ];
    }

    private function normalizeCandidates(
        array $rawCandidates,
        int $deviceId
    ): array {
        $result = [];
        $seen = [];

        foreach ($rawCandidates as $row) {
            $productId =
                (int) (
                    is_array($row)
                    ? ($row['product_id'] ?? 0)
                    : 0
                );

            if ($productId < 1 || isset($seen[$productId])) {
                continue;
            }

            $seen[$productId] = true;

            $product =
                $this->products->findActiveById(
                    $productId
                );

            if ($product === null) {
                continue;
            }

            $variant =
                $this->variants->findDefaultByProduct(
                    $productId
                );

            if ($variant === null) {
                $activeVariants =
                    $this->variants->findActiveByProduct(
                        $productId
                    );

                $variant =
                    $activeVariants[0] ?? null;
            }

            if ($variant === null) {
                continue;
            }

            if ($variant->status !== 'active') {
                continue;
            }

            $price =
                (float) $variant->price;

            if (!is_finite($price) || $price < 0) {
                continue;
            }

            $inventory =
                $this->inventory->findByVariant(
                    $variant->id
                );

            $availableQuantity = null;

            if ($inventory !== null) {
                $availableQuantity =
                    max(
                        0,
                        $inventory->quantity -
                            $inventory->reservedQuantity
                    );
            }

            $available =
                $availableQuantity === null
                ? true
                : $availableQuantity > 0;

            $compatibilityType =
                trim(
                    (string) (
                        is_array($row)
                        ? ($row['compatibility_type'] ?? '')
                        : ''
                    )
                );

            $note =
                is_array($row)
                ? (
                    isset($row['note'])
                    ? trim((string) $row['note'])
                    : null
                )
                : null;

            $sortOrder =
                (int) (
                    is_array($row)
                    ? ($row['sort_order'] ?? 0)
                    : 0
                );

            $compareAt =
                $variant->compareAtPrice !== null
                ? (float) $variant->compareAtPrice
                : null;

            $discountPercent = 0.0;

            if (
                $compareAt !== null &&
                $compareAt > $price &&
                $compareAt > 0
            ) {
                $discountPercent =
                    (($compareAt - $price) / $compareAt) * 100;
            }

            $result[] = [
                'product_id' => $productId,
                'variant_id' => $variant->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' =>
                $product->shortDescription,
                'price' => $this->decimal($variant->price),
                'compare_at_price' =>
                $variant->compareAtPrice !== null
                    ? $this->decimal(
                        $variant->compareAtPrice
                    )
                    : null,
                'currency' =>
                strtoupper(
                    trim($variant->currency)
                ),
                'compatibility_type' =>
                $compatibilityType !== ''
                    ? $compatibilityType
                    : 'unknown',
                'compatibility_note' => $note,
                'sort_order' => $sortOrder,
                'available' => $available,
                'available_quantity' =>
                $availableQuantity,
                'discount_percent' =>
                round($discountPercent, 2),
                'featured' =>
                (bool) $product->isFeatured,
            ];
        }

        return $result;
    }

    private function rankCandidates(
        array $candidates
    ): array {
        foreach ($candidates as &$candidate) {
            $score = 0;

            if ($candidate['available']) {
                $score += 100;
            }

            if (
                $candidate['compatibility_type'] === 'recommended'
            ) {
                $score += 50;
            } elseif (
                $candidate['compatibility_type'] === 'compatible'
            ) {
                $score += 30;
            } elseif (
                $candidate['compatibility_type'] === 'optional'
            ) {
                $score += 10;
            }

            if ($candidate['featured']) {
                $score += 5;
            }

            $score +=
                min(
                    20,
                    $candidate['discount_percent'] / 5
                );

            $candidate['_rank_score'] = $score;
        }

        unset($candidate);

        usort(
            $candidates,
            static function (
                array $a,
                array $b
            ): int {
                $scoreCompare =
                    $b['_rank_score']
                    <=>
                    $a['_rank_score'];

                if ($scoreCompare !== 0) {
                    return $scoreCompare;
                }

                $priceCompare =
                    (float) $a['price']
                    <=>
                    (float) $b['price'];

                if ($priceCompare !== 0) {
                    return $priceCompare;
                }

                return
                    $a['product_id']
                    <=>
                    $b['product_id'];
            }
        );

        foreach ($candidates as &$candidate) {
            unset($candidate['_rank_score']);
        }

        unset($candidate);

        return $candidates;
    }

    private function selectWithinBudget(
        array $ranked,
        string $budget
    ): array {
        $budgetValue = (float) $budget;

        $selected = [];
        $total = 0.0;

        foreach ($ranked as $candidate) {
            if (!$candidate['available']) {
                continue;
            }

            $price =
                (float) $candidate['price'];

            if (
                $price < 0 ||
                ($total + $price) > $budgetValue
            ) {
                continue;
            }

            $selected[] = $candidate;
            $total += $price;
        }

        if ($selected !== []) {
            return [
                'state' => 'optimized',
                'selected' => $selected,
                'trade_off' => null,
                'message' => 'ترکیب بهینه در محدوده بودجه پیدا شد.',
            ];
        }

        $cheapest =
            $this->cheapestAvailable(
                $ranked
            );

        if ($cheapest === null) {
            return [
                'state' => 'no_candidate',
                'selected' => [],
                'trade_off' => null,
                'message' => 'محصول قابل خریدی برای این دستگاه پیدا نشد.',
            ];
        }

        $price =
            (float) $cheapest['price'];

        if ($price > $budgetValue) {
            return [
                'state' => 'over_budget',
                'selected' => [],
                'trade_off' => [
                    'product_id' =>
                    $cheapest['product_id'],
                    'required_extra' =>
                    $this->decimal(
                        $price - $budgetValue
                    ),
                ],
                'message' =>
                'ارزان‌ترین گزینه قابل خرید نیز از بودجه بیشتر است.',
            ];
        }

        return [
            'state' => 'no_candidate',
            'selected' => [],
            'trade_off' => null,
            'message' => 'ترکیب قابل انتخاب پیدا نشد.',
        ];
    }

    private function cheapestAvailable(
        array $candidates
    ): ?array {
        $available =
            array_values(
                array_filter(
                    $candidates,
                    static fn(array $candidate): bool =>
                    $candidate['available']
                )
            );

        if ($available === []) {
            return null;
        }

        usort(
            $available,
            static function (
                array $a,
                array $b
            ): int {
                $price =
                    (float) $a['price']
                    <=>
                    (float) $b['price'];

                if ($price !== 0) {
                    return $price;
                }

                return
                    $a['product_id']
                    <=>
                    $b['product_id'];
            }
        );

        return $available[0];
    }

    private function sumPrices(
        array $items
    ): string {
        $total = 0.0;

        foreach ($items as $item) {
            $total +=
                (float) (
                    $item['price'] ?? 0
                );
        }

        return $this->decimal($total);
    }

    private function resolveCurrency(
        array $candidates
    ): ?string {
        $currencies = [];

        foreach ($candidates as $candidate) {
            $currency =
                strtoupper(
                    trim(
                        (string) (
                            $candidate['currency'] ?? ''
                        )
                    )
                );

            if ($currency !== '') {
                $currencies[$currency] = true;
            }
        }

        if (count($currencies) !== 1) {
            return null;
        }

        return array_key_first($currencies);
    }

    private function deviceData(
        object $device
    ): array {
        return [
            'id' =>
            (int) $device->id,
            'name' =>
            (string) $device->name,
            'slug' =>
            (string) $device->slug,
            'device_type' =>
            (string) $device->deviceType,
        ];
    }

    private function decimal(
        mixed $value
    ): string {
        return number_format(
            (float) $value,
            2,
            '.',
            ''
        );
    }

    private function emptyResult(
        string $state
    ): array {
        return [
            'success' => false,
            'state' => $state,
            'device' => null,
            'budget' => '0.00',
            'currency' => null,
            'candidates' => [],
            'selected' => [],
            'total' => '0.00',
            'remaining' => '0.00',
            'within_budget' => false,
            'trade_off' => null,
            'message' => null,
        ];
    }
}
