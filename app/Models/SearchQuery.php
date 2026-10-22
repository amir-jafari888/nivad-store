<?php

declare(strict_types=1);

final class SearchQuery
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE = 100;
    public const MAX_QUERY_LENGTH = 150;

    public const DEFAULT_SORT = 'relevance';

    public const SORT_RELEVANCE = 'relevance';
    public const SORT_NEWEST = 'newest';
    public const SORT_OLDEST = 'oldest';
    public const SORT_FEATURED = 'featured';

    public const ALLOWED_SORTS = [
        self::SORT_RELEVANCE,
        self::SORT_NEWEST,
        self::SORT_OLDEST,
        self::SORT_FEATURED,
    ];

    public string $query;
    public int $page;
    public int $perPage;
    public ?int $categoryId;
    public ?int $brandId;
    public ?int $productTypeId;
    public ?string $sort;
    public ?string $minPrice;
    public ?string $maxPrice;

    /**
     * null = بدون فیلتر
     * true = فقط موجود
     * false = فقط ناموجود
     */
    public ?bool $inStock;

    /** @var int[] */
    public array $attributeValueIds;

    public ?string $identifier;

    public function __construct(
        string $query,
        int $page = self::DEFAULT_PAGE,
        int $perPage = self::DEFAULT_PER_PAGE,
        ?int $categoryId = null,
        ?int $brandId = null,
        ?int $productTypeId = null,
        string $sort = self::DEFAULT_SORT,
        ?string $minPrice = null,
        ?string $maxPrice = null,
        array $attributeValueIds = [],
        ?bool $inStock = null,
        ?string $identifier = null
    ) {
        $this->query = trim($query);

        $this->page = max(
            1,
            $page
        );

        $this->perPage = min(
            self::MAX_PER_PAGE,
            max(1, $perPage)
        );

        $this->identifier =
            $identifier !== null
            ? trim($identifier)
            : null;

        $this->categoryId =
            $categoryId !== null && $categoryId > 0
            ? $categoryId
            : null;

        $this->brandId =
            $brandId !== null && $brandId > 0
            ? $brandId
            : null;

        $this->productTypeId =
            $productTypeId !== null && $productTypeId > 0
            ? $productTypeId
            : null;

        $this->sort =
            in_array(
                $sort,
                self::ALLOWED_SORTS,
                true
            )
            ? $sort
            : self::DEFAULT_SORT;

        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;

        $normalizedAttributeValueIds = [];

        foreach ($attributeValueIds as $attributeValueId) {
            $attributeValueId = (int)$attributeValueId;

            if ($attributeValueId > 0) {
                $normalizedAttributeValueIds[] =
                    $attributeValueId;
            }
        }

        $this->attributeValueIds =
            array_values(
                array_unique(
                    $normalizedAttributeValueIds
                )
            );

        $this->inStock = $inStock;
    }

    public function offset(): int
    {
        return (
            $this->page - 1
        ) * $this->perPage;
    }

    public function hasQuery(): bool
    {
        return $this->query !== '';
    }

    public function normalizedQuery(): string
    {
        return preg_replace(
            '/\s+/u',
            ' ',
            trim($this->query)
        ) ?? '';
    }

    public function suggestionQuery(): string
    {
        return $this->normalizedQuery();
    }

    public function hasSuggestionQuery(): bool
    {
        return $this->suggestionQuery() !== '';
    }

    public function hasCategoryFilter(): bool
    {
        return $this->categoryId !== null;
    }

    public function hasBrandFilter(): bool
    {
        return $this->brandId !== null;
    }

    public function hasProductTypeFilter(): bool
    {
        return $this->productTypeId !== null;
    }

    public function hasPriceFilter(): bool
    {
        return (
            $this->minPrice !== null ||
            $this->maxPrice !== null
        );
    }

    public function hasAttributeValueFilter(): bool
    {
        return $this->attributeValueIds !== [];
    }

    public function hasStockFilter(): bool
    {
        return $this->inStock !== null;
    }

    public function hasIdentifier(): bool
    {
        return $this->identifier !== null
            && $this->identifier !== '';
    }
}
