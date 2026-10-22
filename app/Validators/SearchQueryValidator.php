<?php

declare(strict_types=1);

final class SearchQueryValidator
{
    private const MAX_IDENTIFIER_LENGTH = 100;

    public function validate(
        array $data
    ): array {
        $errors = [];

        $query =
            array_key_exists('q', $data)
            ? $data['q']
            : ($data['query'] ?? '');

        if (!is_string($query)) {
            $errors['query'] =
                'عبارت جستجو نامعتبر است.';
        } else {
            $query = trim($query);

            if (
                mb_strlen($query) >
                SearchQuery::MAX_QUERY_LENGTH
            ) {
                $errors['query'] =
                    'عبارت جستجو بیش از حد طولانی است.';
            }
        }

        $page =
            $data['page'] ?? SearchQuery::DEFAULT_PAGE;

        if (
            filter_var(
                $page,
                FILTER_VALIDATE_INT
            ) === false ||
            (int)$page < 1
        ) {
            $errors['page'] =
                'شماره صفحه نامعتبر است.';
        }

        $perPage =
            $data['per_page'] ??
            SearchQuery::DEFAULT_PER_PAGE;

        if (
            filter_var(
                $perPage,
                FILTER_VALIDATE_INT
            ) === false ||
            (int)$perPage < 1 ||
            (int)$perPage >
            SearchQuery::MAX_PER_PAGE
        ) {
            $errors['per_page'] =
                'تعداد آیتم‌های صفحه نامعتبر است.';
        }

        if (
            array_key_exists(
                'category_id',
                $data
            ) &&
            $data['category_id'] !== '' &&
            (
                filter_var(
                    $data['category_id'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int)$data['category_id'] <= 0
            )
        ) {
            $errors['category_id'] =
                'شناسه دسته‌بندی نامعتبر است.';
        }

        if (
            array_key_exists(
                'brand_id',
                $data
            ) &&
            $data['brand_id'] !== '' &&
            (
                filter_var(
                    $data['brand_id'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int)$data['brand_id'] <= 0
            )
        ) {
            $errors['brand_id'] =
                'شناسه برند نامعتبر است.';
        }

        if (
            array_key_exists(
                'product_type_id',
                $data
            ) &&
            $data['product_type_id'] !== '' &&
            (
                filter_var(
                    $data['product_type_id'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int)$data['product_type_id'] <= 0
            )
        ) {
            $errors['product_type_id'] =
                'شناسه نوع محصول نامعتبر است.';
        }

        if (
            array_key_exists(
                'sort',
                $data
            ) &&
            $data['sort'] !== '' &&
            !in_array(
                (string)$data['sort'],
                SearchQuery::ALLOWED_SORTS,
                true
            )
        ) {
            $errors['sort'] =
                'مرتب‌سازی نامعتبر است.';
        }

        if (
            array_key_exists(
                'min_price',
                $data
            ) &&
            $data['min_price'] !== '' &&
            !$this->isValidPrice(
                $data['min_price']
            )
        ) {
            $errors['min_price'] =
                'حداقل قیمت نامعتبر است.';
        }

        if (
            array_key_exists(
                'max_price',
                $data
            ) &&
            $data['max_price'] !== '' &&
            !$this->isValidPrice(
                $data['max_price']
            )
        ) {
            $errors['max_price'] =
                'حداکثر قیمت نامعتبر است.';
        }

        if (
            $this->isValidPrice(
                $data['min_price'] ?? null
            ) &&
            $this->isValidPrice(
                $data['max_price'] ?? null
            )
        ) {
            $min =
                $this->normalizePrice(
                    $data['min_price']
                );

            $max =
                $this->normalizePrice(
                    $data['max_price']
                );

            if (
                $min !== null &&
                $max !== null &&
                (float)$min > (float)$max
            ) {
                $errors['price'] =
                    'حداقل قیمت نمی‌تواند بیشتر از حداکثر قیمت باشد.';
            }
        }

        if (
            array_key_exists(
                'attribute_value_ids',
                $data
            )
        ) {
            $attributeValueIds =
                $data['attribute_value_ids'];

            if (
                !is_array(
                    $attributeValueIds
                )
            ) {
                $errors['attribute_value_ids'] =
                    'لیست مقادیر ویژگی نامعتبر است.';
            } else {
                foreach (
                    $attributeValueIds
                    as $attributeValueId
                ) {
                    if (
                        filter_var(
                            $attributeValueId,
                            FILTER_VALIDATE_INT
                        ) === false ||
                        (int)$attributeValueId <= 0
                    ) {
                        $errors['attribute_value_ids'] =
                            'یکی از شناسه‌های ویژگی نامعتبر است.';
                        break;
                    }
                }
            }
        }

        if (
            array_key_exists(
                'in_stock',
                $data
            ) &&
            $data['in_stock'] !== '' &&
            !$this->isValidStockValue(
                $data['in_stock']
            )
        ) {
            $errors['in_stock'] =
                'مقدار فیلتر موجودی نامعتبر است.';
        }

        if (
            array_key_exists(
                'identifier',
                $data
            ) &&
            !$this->isValidIdentifier(
                $data['identifier']
            )
        ) {
            $errors['identifier'] =
                'شناسه محصول نامعتبر است.';
        }

        return $errors;
    }

    public function normalizeQuery(
        mixed $value
    ): string {
        if (!is_string($value)) {
            return '';
        }

        return preg_replace(
            '/\s+/u',
            ' ',
            trim($value)
        ) ?? '';
    }

    public function normalizePage(
        mixed $value
    ): int {
        $page = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if ($page === false || $page < 1) {
            return SearchQuery::DEFAULT_PAGE;
        }

        return (int)$page;
    }

    public function normalizePerPage(
        mixed $value
    ): int {
        $perPage = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if (
            $perPage === false ||
            $perPage < 1
        ) {
            return SearchQuery::DEFAULT_PER_PAGE;
        }

        return min(
            SearchQuery::MAX_PER_PAGE,
            (int)$perPage
        );
    }

    public function normalizeId(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        $id = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if (
            $id === false ||
            $id <= 0
        ) {
            return null;
        }

        return (int)$id;
    }

    public function normalizeSort(
        mixed $value
    ): string {
        if (
            is_string($value) &&
            in_array(
                $value,
                SearchQuery::ALLOWED_SORTS,
                true
            )
        ) {
            return $value;
        }

        return SearchQuery::DEFAULT_SORT;
    }

    public function normalizePrice(
        mixed $value
    ): ?string {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (!$this->isValidPrice($value)) {
            return null;
        }

        $value =
            str_replace(
                ',',
                '',
                trim((string)$value)
            );

        return $value;
    }

    public function normalizeAttributeValueIds(
        mixed $value
    ): array {
        if (!is_array($value)) {
            return [];
        }

        $ids = [];

        foreach ($value as $id) {
            $id = filter_var(
                $id,
                FILTER_VALIDATE_INT
            );

            if (
                $id !== false &&
                $id > 0
            ) {
                $ids[] = (int)$id;
            }
        }

        return array_values(
            array_unique($ids)
        );
    }

    public function isValidStockValue(
        mixed $value
    ): bool {
        if (
            is_bool($value)
        ) {
            return true;
        }

        if (
            is_int($value) &&
            ($value === 0 || $value === 1)
        ) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return in_array(
            strtolower(trim($value)),
            [
                '0',
                '1',
                'true',
                'false',
                'yes',
                'no',
            ],
            true
        );
    }

    public function normalizeStock(
        mixed $value
    ): ?bool {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            if ($value === 1) {
                return true;
            }

            if ($value === 0) {
                return false;
            }

            return null;
        }

        if (!is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value))) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }

    private function isValidPrice(
        mixed $value
    ): bool {
        if (
            $value === null ||
            $value === ''
        ) {
            return false;
        }

        $value =
            str_replace(
                ',',
                '',
                trim((string)$value)
            );

        return preg_match(
            '/^\d+(?:\.\d{1,4})?$/',
            $value
        ) === 1;
    }

    public static function isValidIdentifier(
        mixed $identifier
    ): bool {
        if ($identifier === null) {
            return true;
        }

        if (!is_string($identifier)) {
            return false;
        }

        $identifier = trim($identifier);

        if ($identifier === '') {
            return true;
        }

        return mb_strlen($identifier) <= self::MAX_IDENTIFIER_LENGTH;
    }

    public static function normalizeIdentifier(
        mixed $identifier
    ): ?string {
        if ($identifier === null) {
            return null;
        }

        if (!is_string($identifier)) {
            return null;
        }

        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        return $identifier;
    }
}
