<?php

declare(strict_types=1);

final class SearchRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ?? Database::connection();
    }

    public function search(
        SearchQuery $query
    ): array {
        [
            $where,
            $params
        ] = $this->buildFilters(
            $query
        );

        $orderBy =
            $this->resolveSort(
                $query
            );

        if (
            $query->sort ===
            SearchQuery::SORT_RELEVANCE
        ) {
            $params[':sort_prefix'] =
                $query->normalizedQuery() . '%';
        }

        $sql = "
            SELECT
                p.id,
                COALESCE(
                    (
                        SELECT pc.category_id
                        FROM product_categories pc
                        WHERE pc.product_id = p.id
                        ORDER BY
                            pc.is_primary DESC,
                            pc.sort_order ASC,
                            pc.category_id ASC
                        LIMIT 1
                    ),
                    0
                ) AS category_id,
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.created_at,
                p.updated_at,
                p.deleted_at
            FROM products p
            {$where}
            ORDER BY {$orderBy}
            LIMIT :limit
            OFFSET :offset
        ";

        $statement =
            $this->db->prepare($sql);

        foreach (
            $params as $key => $value
        ) {
            $statement->bindValue(
                $key,
                $value
            );
        }

        $statement->bindValue(
            ':limit',
            $query->perPage,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':offset',
            $query->offset(),
            PDO::PARAM_INT
        );

        $statement->execute();

        $rows =
            $statement->fetchAll(
                PDO::FETCH_ASSOC
            );

        $products = [];

        foreach ($rows as $row) {
            $products[] =
                $this->hydrateProduct(
                    $row
                );
        }

        return $products;
    }

    public function count(
        SearchQuery $query
    ): int {
        [
            $where,
            $params
        ] = $this->buildFilters(
            $query
        );

        $sql = "
            SELECT COUNT(*)
            FROM products p
            {$where}
        ";

        $statement =
            $this->db->prepare($sql);

        foreach (
            $params as $key => $value
        ) {
            $statement->bindValue(
                $key,
                $value
            );
        }

        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    public function suggest(
        SearchQuery $query,
        int $limit = 6
    ): array {
        $limit =
            max(
                1,
                min(20, $limit)
            );

        $normalizedQuery =
            $query->suggestionQuery();

        $identifier =
            $query->identifier ?? '';

        if (
            $normalizedQuery === '' &&
            $identifier === ''
        ) {
            return [];
        }

        $conditions = [
            "p.status = 'active'",
            "p.deleted_at IS NULL",
        ];

        $params = [];
        $rankParts = [];

        if ($normalizedQuery !== '') {
            $conditions[] = "
                (
                    p.name LIKE :suggest_name_prefix
                    OR p.slug LIKE :suggest_slug_prefix
                )
            ";

            $prefix =
                $normalizedQuery . '%';

            $params[':suggest_name_prefix'] =
                $prefix;

            $params[':suggest_slug_prefix'] =
                $prefix;

            $params[':suggest_name_rank'] =
                $prefix;

            $params[':suggest_slug_rank'] =
                $prefix;

            $rankParts[] = "
                CASE
                    WHEN p.name LIKE :suggest_name_rank
                    THEN 0
                    WHEN p.slug LIKE :suggest_slug_rank
                    THEN 1
                    ELSE 2
                END
            ";
        }

        if ($identifier !== '') {
            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM product_variants pv_identifier
                    WHERE
                        pv_identifier.product_id = p.id
                        AND pv_identifier.status = 'active'
                        AND pv_identifier.deleted_at IS NULL
                        AND (
                            pv_identifier.sku LIKE :identifier_prefix_sku
                            OR pv_identifier.barcode LIKE :identifier_prefix_barcode
                        )
                )
            ";

            $identifierPrefix =
                $identifier . '%';

            $params[':identifier_prefix_sku'] =
                $identifierPrefix;

            $params[':identifier_prefix_barcode'] =
                $identifierPrefix;

            $params[':identifier_rank_sku'] =
                $identifierPrefix;

            $params[':identifier_rank_barcode'] =
                $identifierPrefix;

            $rankParts[] = "
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM product_variants pv_identifier_rank
                        WHERE
                            pv_identifier_rank.product_id = p.id
                            AND pv_identifier_rank.status = 'active'
                            AND pv_identifier_rank.deleted_at IS NULL
                            AND (
                                pv_identifier_rank.sku LIKE :identifier_rank_sku
                                OR pv_identifier_rank.barcode LIKE :identifier_rank_barcode
                            )
                    )
                    THEN 2
                    ELSE 3
                END
            ";
        }

        $sql = "
            SELECT
                p.id,
                p.name,
                p.slug,
                b.id AS brand_id,
                b.name AS brand_name,
                b.slug AS brand_slug,
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                pt.id AS product_type_id,
                pt.name AS product_type_name,
                pt.slug AS product_type_slug
            FROM products p
            LEFT JOIN brands b
                ON b.id = p.brand_id
                AND b.status = 'active'
                AND b.deleted_at IS NULL
            LEFT JOIN product_types pt
                ON pt.id = p.product_type_id
                AND pt.status = 'active'
                AND pt.deleted_at IS NULL
            LEFT JOIN product_categories pc_primary
                ON pc_primary.product_id = p.id
                AND pc_primary.is_primary = 1
            LEFT JOIN categories c
                ON c.id = pc_primary.category_id
                AND c.status = 'active'
                AND c.deleted_at IS NULL
            WHERE
                " . implode(' AND ', $conditions) . "
            ORDER BY
                " . implode(', ', $rankParts) . " ASC,
                p.name ASC,
                p.id ASC
            LIMIT :limit
        ";

        $statement =
            $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(
                $key,
                $value,
                PDO::PARAM_STR
            );
        }

        $statement->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $statement->execute();

        $rows =
            $statement->fetchAll(
                PDO::FETCH_ASSOC
            );

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'id' =>
                (int)$row['id'],

                'name' =>
                (string)$row['name'],

                'slug' =>
                (string)$row['slug'],

                'metadata' => [
                    'brand' =>
                    $row['brand_id'] !== null
                        ? [
                            'id' =>
                            (int)$row['brand_id'],

                            'name' =>
                            (string)$row['brand_name'],

                            'slug' =>
                            (string)$row['brand_slug'],
                        ]
                        : null,

                    'category' =>
                    $row['category_id'] !== null
                        ? [
                            'id' =>
                            (int)$row['category_id'],

                            'name' =>
                            (string)$row['category_name'],

                            'slug' =>
                            (string)$row['category_slug'],
                        ]
                        : null,

                    'product_type' =>
                    $row['product_type_id'] !== null
                        ? [
                            'id' =>
                            (int)$row['product_type_id'],

                            'name' =>
                            (string)$row['product_type_name'],

                            'slug' =>
                            (string)$row['product_type_slug'],
                        ]
                        : null,
                ],
            ];
        }

        return $result;
    }

    public function filters(): array
    {
        $categoriesStatement =
            $this->db->query("
            SELECT
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                COUNT(DISTINCT p.id) AS product_count
            FROM categories c
            LEFT JOIN product_categories pc
                ON pc.category_id = c.id
            LEFT JOIN products p
                ON p.id = pc.product_id
                AND p.status = 'active'
                AND p.deleted_at IS NULL
            WHERE
                c.status = 'active'
                AND c.deleted_at IS NULL
            GROUP BY
                c.id,
                c.parent_id,
                c.name,
                c.slug
            ORDER BY
                c.sort_order ASC,
                c.name ASC,
                c.id ASC
        ");

        $brandsStatement =
            $this->db->query("
            SELECT
                b.id,
                b.name,
                b.slug,
                COUNT(DISTINCT p.id) AS product_count
            FROM brands b
            LEFT JOIN products p
                ON p.brand_id = b.id
                AND p.status = 'active'
                AND p.deleted_at IS NULL
            WHERE
                b.status = 'active'
                AND b.deleted_at IS NULL
            GROUP BY
                b.id,
                b.name,
                b.slug
            ORDER BY
                b.sort_order ASC,
                b.name ASC,
                b.id ASC
        ");

        $productTypesStatement =
            $this->db->query("
            SELECT
                pt.id,
                pt.name,
                pt.slug,
                COUNT(DISTINCT p.id) AS product_count
            FROM product_types pt
            LEFT JOIN products p
                ON p.product_type_id = pt.id
                AND p.status = 'active'
                AND p.deleted_at IS NULL
            WHERE
                pt.status = 'active'
                AND pt.deleted_at IS NULL
            GROUP BY
                pt.id,
                pt.name,
                pt.slug
            ORDER BY
                pt.sort_order ASC,
                pt.name ASC,
                pt.id ASC
        ");

        $attributesStatement =
            $this->db->query("
            SELECT
                a.id,
                a.name,
                a.slug,
                a.input_type,
                a.sort_order,
                av.id AS value_id,
                av.value,
                av.slug AS value_slug,
                av.value_code,
                av.color_hex,
                av.sort_order AS value_sort_order,
                COUNT(
                    DISTINCT
                    CASE
                        WHEN
                            pv.status = 'active'
                            AND pv.deleted_at IS NULL
                            AND p.status = 'active'
                            AND p.deleted_at IS NULL
                        THEN p.id
                    END
                ) AS product_count
            FROM attributes a
            LEFT JOIN attribute_values av
                ON av.attribute_id = a.id
                AND av.status = 'active'
                AND av.deleted_at IS NULL
            LEFT JOIN variant_attribute_values vav
                ON vav.attribute_value_id = av.id
            LEFT JOIN product_variants pv
                ON pv.id = vav.variant_id
            LEFT JOIN products p
                ON p.id = pv.product_id
            WHERE
                a.status = 'active'
                AND a.deleted_at IS NULL
                AND a.is_filterable = 1
                AND a.is_variant = 1
            GROUP BY
                a.id,
                a.name,
                a.slug,
                a.input_type,
                a.sort_order,
                av.id,
                av.value,
                av.slug,
                av.value_code,
                av.color_hex,
                av.sort_order
            ORDER BY
                a.sort_order ASC,
                a.id ASC,
                av.sort_order ASC,
                av.id ASC
        ");

        $priceStatement =
            $this->db->query("
            SELECT
                MIN(pv.price) AS min_price,
                MAX(pv.price) AS max_price
            FROM product_variants pv
            INNER JOIN products p
                ON p.id = pv.product_id
            WHERE
                pv.status = 'active'
                AND pv.deleted_at IS NULL
                AND p.status = 'active'
                AND p.deleted_at IS NULL
        ");

        $categories = [];

        foreach (
            $categoriesStatement->fetchAll(
                PDO::FETCH_ASSOC
            ) as $row
        ) {
            $categories[] = [
                'id' =>
                (int)$row['id'],

                'parent_id' =>
                $row['parent_id'] !== null
                    ? (int)$row['parent_id']
                    : null,

                'name' =>
                (string)$row['name'],

                'slug' =>
                (string)$row['slug'],

                'count' =>
                (int)$row['product_count'],
            ];
        }

        $brands = [];

        foreach (
            $brandsStatement->fetchAll(
                PDO::FETCH_ASSOC
            ) as $row
        ) {
            $brands[] = [
                'id' =>
                (int)$row['id'],

                'name' =>
                (string)$row['name'],

                'slug' =>
                (string)$row['slug'],

                'count' =>
                (int)$row['product_count'],
            ];
        }

        $productTypes = [];

        foreach (
            $productTypesStatement->fetchAll(
                PDO::FETCH_ASSOC
            ) as $row
        ) {
            $productTypes[] = [
                'id' =>
                (int)$row['id'],

                'name' =>
                (string)$row['name'],

                'slug' =>
                (string)$row['slug'],

                'count' =>
                (int)$row['product_count'],
            ];
        }

        $attributes = [];

        foreach (
            $attributesStatement->fetchAll(
                PDO::FETCH_ASSOC
            ) as $row
        ) {
            $attributeId =
                (int)$row['id'];

            if (
                !isset(
                    $attributes[$attributeId]
                )
            ) {
                $attributes[$attributeId] = [
                    'id' =>
                    $attributeId,

                    'name' =>
                    (string)$row['name'],

                    'slug' =>
                    (string)$row['slug'],

                    'input_type' =>
                    (string)$row['input_type'],

                    'values' => [],
                ];
            }

            if (
                $row['value_id'] !== null
            ) {
                $attributes[$attributeId]['values'][] = [
                    'id' =>
                    (int)$row['value_id'],

                    'value' =>
                    (string)$row['value'],

                    'slug' =>
                    (string)$row['value_slug'],

                    'value_code' =>
                    $row['value_code'] !== null
                        ? (string)$row['value_code']
                        : null,

                    'color_hex' =>
                    $row['color_hex'] !== null
                        ? (string)$row['color_hex']
                        : null,

                    'count' =>
                    (int)$row['product_count'],
                ];
            }
        }

        $price =
            $priceStatement->fetch(
                PDO::FETCH_ASSOC
            ) ?: [];

        return [
            'categories' =>
            array_values(
                $categories
            ),

            'brands' =>
            array_values(
                $brands
            ),

            'product_types' =>
            array_values(
                $productTypes
            ),

            'attributes' =>
            array_values(
                $attributes
            ),

            'price' => [
                'min' =>
                $price['min_price'] !== null
                    ? (string)$price['min_price']
                    : null,

                'max' =>
                $price['max_price'] !== null
                    ? (string)$price['max_price']
                    : null,
            ],
        ];
    }
    public function searchWithMeta(
        SearchQuery $query
    ): array {
        $total =
            $this->count($query);

        $items =
            $this->search($query);

        $totalPages =
            $total > 0
            ? (int)ceil(
                $total / $query->perPage
            )
            : 0;

        return [
            'items' =>
            $items,

            'total' =>
            $total,

            'page' =>
            $query->page,

            'per_page' =>
            $query->perPage,

            'total_pages' =>
            $totalPages,
        ];
    }

    private function buildFilters(
        SearchQuery $query
    ): array {
        $conditions = [
            "p.status = 'active'",
            "p.deleted_at IS NULL",
        ];

        $params = [];

        if ($query->hasQuery()) {
            $conditions[] = "
                (
                    p.name LIKE :search_query
                    OR p.slug LIKE :search_query_slug
                    OR p.short_description LIKE :search_query_short
                    OR p.description LIKE :search_query_description
                )
            ";

            $search =
                '%' .
                $query->normalizedQuery() .
                '%';

            $params[':search_query'] =
                $search;

            $params[':search_query_slug'] =
                $search;

            $params[':search_query_short'] =
                $search;

            $params[':search_query_description'] =
                $search;
        }

        if ($query->hasCategoryFilter()) {
            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM product_categories pc_filter
                    WHERE
                        pc_filter.product_id = p.id
                        AND pc_filter.category_id = :category_id
                )
            ";

            $params[':category_id'] =
                $query->categoryId;
        }

        if ($query->hasBrandFilter()) {
            $conditions[] =
                "p.brand_id = :brand_id";

            $params[':brand_id'] =
                $query->brandId;
        }

        if ($query->hasProductTypeFilter()) {
            $conditions[] =
                "p.product_type_id = :product_type_id";

            $params[':product_type_id'] =
                $query->productTypeId;
        }

        if ($query->minPrice !== null) {
            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM product_variants pv_min
                    WHERE
                        pv_min.product_id = p.id
                        AND pv_min.status = 'active'
                        AND pv_min.deleted_at IS NULL
                        AND pv_min.price >= :min_price
                )
            ";

            $params[':min_price'] =
                $query->minPrice;
        }

        if ($query->maxPrice !== null) {
            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM product_variants pv_max
                    WHERE
                        pv_max.product_id = p.id
                        AND pv_max.status = 'active'
                        AND pv_max.deleted_at IS NULL
                        AND pv_max.price <= :max_price
                )
            ";

            $params[':max_price'] =
                $query->maxPrice;
        }

        if ($query->hasAttributeValueFilter()) {
            $attributePlaceholders = [];

            foreach (
                $query->attributeValueIds
                as $index => $attributeValueId
            ) {
                $placeholder =
                    ':attribute_value_' . $index;

                $attributePlaceholders[] =
                    $placeholder;

                $params[$placeholder] =
                    $attributeValueId;
            }

            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM product_variants pv_attribute
                    INNER JOIN variant_attribute_values vav
                        ON vav.variant_id = pv_attribute.id
                    INNER JOIN attribute_values av
                        ON av.id = vav.attribute_value_id
                    INNER JOIN attributes a
                        ON a.id = av.attribute_id
                    WHERE
                        pv_attribute.product_id = p.id
                        AND pv_attribute.status = 'active'
                        AND pv_attribute.deleted_at IS NULL
                        AND av.status = 'active'
                        AND av.deleted_at IS NULL
                        AND a.status = 'active'
                        AND a.deleted_at IS NULL
                        AND a.is_filterable = 1
                        AND a.is_variant = 1
                        AND vav.attribute_value_id IN (" .
                implode(
                    ', ',
                    $attributePlaceholders
                ) .
                ")
                )
            ";
        }

        if ($query->hasStockFilter()) {
            if ($query->inStock === true) {
                $conditions[] = "
                    EXISTS (
                        SELECT 1
                        FROM product_variants pv_stock
                        INNER JOIN inventory i_stock
                            ON i_stock.variant_id = pv_stock.id
                        WHERE
                            pv_stock.product_id = p.id
                            AND pv_stock.status = 'active'
                            AND pv_stock.deleted_at IS NULL
                            AND (
                                i_stock.quantity -
                                i_stock.reserved_quantity
                            ) > 0
                    )
                ";
            } else {
                $conditions[] = "
                    EXISTS (
                        SELECT 1
                        FROM product_variants pv_stock
                        INNER JOIN inventory i_stock
                            ON i_stock.variant_id = pv_stock.id
                        WHERE
                            pv_stock.product_id = p.id
                            AND pv_stock.status = 'active'
                            AND pv_stock.deleted_at IS NULL
                            AND (
                                i_stock.quantity -
                                i_stock.reserved_quantity
                            ) <= 0
                    )
                ";
            }
        }

        return [
            'WHERE ' .
                implode(
                    ' AND ',
                    $conditions
                ),

            $params,
        ];
    }

    private function resolveSort(
        SearchQuery $query
    ): string {
        return match ($query->sort) {
            SearchQuery::SORT_NEWEST =>
            'p.created_at DESC, p.id DESC',

            SearchQuery::SORT_OLDEST =>
            'p.created_at ASC, p.id ASC',

            SearchQuery::SORT_FEATURED =>
            'p.is_featured DESC,
                 p.sort_order ASC,
                 p.id DESC',

            default =>
            'CASE
                    WHEN p.name LIKE CONCAT(
                        :sort_prefix,
                        "%"
                    )
                    THEN 0
                    ELSE 1
                 END ASC,
                 p.is_featured DESC,
                 p.sort_order ASC,
                 p.id DESC',
        };
    }

    private function hydrateProduct(
        array $row
    ): Product {
        return new Product(
            (int)$row['id'],

            (int)$row['category_id'],

            isset($row['brand_id'])
                ? (int)$row['brand_id']
                : null,

            (string)$row['name'],

            (string)$row['slug'],

            $row['short_description'] !== null
                ? (string)$row['short_description']
                : null,

            $row['description'] !== null
                ? (string)$row['description']
                : null,

            (string)$row['status'],

            (bool)$row['is_featured'],

            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,

            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null,

            $row['deleted_at'] !== null
                ? (string)$row['deleted_at']
                : null
        );
    }
}
