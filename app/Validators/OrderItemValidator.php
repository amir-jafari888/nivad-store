<?php

declare(strict_types=1);

final class OrderItemValidator
{
    public function validate(
        int $orderId,
        ?int $productId,
        ?int $variantId,
        string $productName,
        string $productSku,
        ?string $variantSku,
        ?string $variantTitle,
        int $quantity,
        string $unitPrice,
        string $discountAmount,
        string $taxAmount,
        string $totalAmount
    ): array {
        $errors = [];

        if ($orderId <= 0) {
            $errors['order_id'] =
                'شناسه سفارش نامعتبر است.';
        }

        if ($productId !== null && $productId <= 0) {
            $errors['product_id'] =
                'شناسه محصول نامعتبر است.';
        }

        if ($variantId !== null && $variantId <= 0) {
            $errors['variant_id'] =
                'شناسه Variant نامعتبر است.';
        }

        if (trim($productName) === '') {
            $errors['product_name'] =
                'نام محصول الزامی است.';
        }

        if (trim($productSku) === '') {
            $errors['product_sku'] =
                'SKU محصول الزامی است.';
        }

        if ($quantity <= 0) {
            $errors['quantity'] =
                'تعداد باید بیشتر از صفر باشد.';
        }

        foreach (
            [
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ] as $field => $value
        ) {
            if (!preg_match('/^\d+(\.\d{1,4})?$/', $value)) {
                $errors[$field] =
                    'مبلغ آیتم باید یک مقدار Decimal معتبر باشد.';
            }
        }

        return $errors;
    }
}
