<?php

declare(strict_types=1);

final class ProductVariantValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $productId = $data['productId'] ?? null;

        $sku = isset($data['sku'])
            ? trim((string) $data['sku'])
            : '';

        $barcode = isset($data['barcode'])
            ? trim((string) $data['barcode'])
            : '';

        /*
         * product_variants.price has a database default of 0.0000.
         * Therefore omitted price is normalized to zero.
         */
        $price = $data['price'] ?? '0';

        $compareAtPrice = $data['compareAtPrice'] ?? null;

        /*
         * product_variants.currency has a database default of IRR.
         */
        $currency = isset($data['currency'])
            ? strtoupper(trim((string) $data['currency']))
            : 'IRR';

        $status = isset($data['status'])
            ? trim((string) $data['status'])
            : '';

        $isDefault = $data['isDefault'] ?? false;

        $sortOrder = $data['sortOrder'] ?? 0;

        /*
         * product_id
         */
        if (
            filter_var(
                $productId,
                FILTER_VALIDATE_INT
            ) === false
            || (int) $productId <= 0
        ) {
            $errors['productId'] =
                'شناسه محصول باید یک عدد صحیح بزرگ‌تر از صفر باشد.';
        }

        /*
         * sku
         */
        if ($sku === '') {
            $errors['sku'] = 'SKU الزامی است.';
        } elseif (strlen($sku) > 100) {
            $errors['sku'] =
                'SKU نمی‌تواند بیشتر از 100 کاراکتر باشد.';
        }

        /*
         * status
         */
        if ($status === '') {
            $errors['status'] = 'وضعیت Variant الزامی است.';
        } elseif (
            !in_array(
                $status,
                [
                    'active',
                    'inactive',
                    'out_of_stock',
                    'discontinued',
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت Variant نامعتبر است.';
        }

        /*
         * barcode
         */
        if ($barcode !== '' && strlen($barcode) > 100) {
            $errors['barcode'] =
                'بارکد نمی‌تواند بیشتر از 100 کاراکتر باشد.';
        }

        /*
         * price
         */
        if (
            !is_numeric($price)
            || (float) $price < 0
        ) {
            $errors['price'] =
                'قیمت باید یک مقدار عددی بزرگ‌تر یا مساوی صفر باشد.';
        }

        /*
         * compareAtPrice
         */
        if (
            $compareAtPrice !== null
            && $compareAtPrice !== ''
            && (
                !is_numeric($compareAtPrice)
                || (float) $compareAtPrice < 0
            )
        ) {
            $errors['compareAtPrice'] =
                'قیمت مقایسه‌ای باید یک مقدار عددی بزرگ‌تر یا مساوی صفر باشد.';
        }

        /*
         * currency
         */
        if (
            strlen($currency) !== 3
            || !preg_match('/^[A-Z]{3}$/', $currency)
        ) {
            $errors['currency'] =
                'واحد پول باید یک کد سه حرفی معتبر باشد.';
        }

        /*
         * isDefault
         */
        if (
            !is_bool($isDefault)
            && !in_array(
                $isDefault,
                [0, 1, '0', '1'],
                true
            )
        ) {
            $errors['isDefault'] =
                'مقدار isDefault باید Boolean باشد.';
        }

        /*
         * sortOrder
         */
        if (
            filter_var(
                $sortOrder,
                FILTER_VALIDATE_INT
            ) === false
            || (int) $sortOrder < 0
        ) {
            $errors['sortOrder'] =
                'ترتیب Variant باید یک عدد صحیح بزرگ‌تر یا مساوی صفر باشد.';
        }

        return $errors;
    }

    public function validateAdmin(array $data): array
    {
        $errors = [];

        $productId = filter_var(
            $data['productId'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($productId === false || $productId < 1) {
            $errors['productId'] =
                'شناسه محصول نامعتبر است.';
        }

        $sku = trim(
            (string)($data['sku'] ?? '')
        );

        if ($sku === '') {
            $errors['sku'] =
                'SKU Variant الزامی است.';
        } elseif (strlen($sku) > 100) {
            $errors['sku'] =
                'SKU Variant نمی‌تواند بیشتر از 100 کاراکتر باشد.';
        }

        $price = (string)($data['price'] ?? '0');

        if (
            !is_numeric($price) ||
            (float)$price < 0
        ) {
            $errors['price'] =
                'قیمت Variant نامعتبر است.';
        }

        $currency = strtoupper(
            trim((string)($data['currency'] ?? 'IRR'))
        );

        if (strlen($currency) !== 3) {
            $errors['currency'] =
                'Currency باید سه حرف باشد.';
        }

        $status = trim(
            (string)($data['status'] ?? 'active')
        );

        if (
            !in_array(
                $status,
                [
                    'active',
                    'inactive',
                    'out_of_stock',
                    'discontinued',
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت Variant نامعتبر است.';
        }

        $sortOrder = filter_var(
            $data['sortOrder'] ?? 0,
            FILTER_VALIDATE_INT
        );

        if (
            $sortOrder === false ||
            $sortOrder < 0
        ) {
            $errors['sortOrder'] =
                'ترتیب Variant نامعتبر است.';
        }

        return $errors;
    }
}
