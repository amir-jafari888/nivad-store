<?php

declare(strict_types=1);

final class SearchController extends Controller
{
    private SearchService $service;

    private SearchQueryValidator $validator;

    public function __construct(
        ?SearchService $service = null,
        ?SearchQueryValidator $validator = null
    ) {
        $this->service =
            $service ??
            new SearchService();

        $this->validator =
            $validator ??
            new SearchQueryValidator();
    }

    public function index(): never
    {
        $data = [
            'q' =>
            Request::query(
                'q',
                Request::query('query', '')
            ),

            'page' =>
            Request::query(
                'page',
                SearchQuery::DEFAULT_PAGE
            ),

            'per_page' =>
            Request::query(
                'per_page',
                SearchQuery::DEFAULT_PER_PAGE
            ),

            'category_id' =>
            Request::query(
                'category_id'
            ),

            'brand_id' =>
            Request::query(
                'brand_id'
            ),

            'product_type_id' =>
            Request::query(
                'product_type_id'
            ),

            'sort' =>
            Request::query(
                'sort',
                SearchQuery::DEFAULT_SORT
            ),

            'min_price' =>
            Request::query(
                'min_price'
            ),

            'max_price' =>
            Request::query(
                'max_price'
            ),

            'attribute_value_ids' =>
            Request::query(
                'attribute_value_ids',
                []
            ),

            'in_stock' =>
            Request::query(
                'in_stock'
            ),
        ];

        if (
            is_string(
                $data['attribute_value_ids']
            )
        ) {
            $data['attribute_value_ids'] =
                array_filter(
                    explode(
                        ',',
                        $data['attribute_value_ids']
                    ),
                    static function (
                        string $value
                    ): bool {
                        return trim($value) !== '';
                    }
                );
        }

        $errors =
            $this->validator->validate(
                $data
            );

        if ($errors !== []) {
            $this->error(
                'پارامترهای جستجو نامعتبر هستند.',
                422,
                $errors
            );
        }

        $query =
            new SearchQuery(
                $this->validator->normalizeQuery(
                    $data['q']
                ),

                $this->validator->normalizePage(
                    $data['page']
                ),

                $this->validator->normalizePerPage(
                    $data['per_page']
                ),

                $this->validator->normalizeId(
                    $data['category_id']
                ),

                $this->validator->normalizeId(
                    $data['brand_id']
                ),

                $this->validator->normalizeId(
                    $data['product_type_id']
                ),

                $this->validator->normalizeSort(
                    $data['sort']
                ),

                $this->validator->normalizePrice(
                    $data['min_price']
                ),

                $this->validator->normalizePrice(
                    $data['max_price']
                ),

                $this->validator
                    ->normalizeAttributeValueIds(
                        $data['attribute_value_ids']
                    ),

                $this->validator->normalizeStock(
                    $data['in_stock']
                )
            );

        $result =
            $this->service->searchWithMeta(
                $query
            );

        $this->success(
            $result,
            'نتایج جستجو دریافت شد.'
        );
    }

    public function suggest(): never
    {
        $value =
            Request::query(
                'q',
                Request::query(
                    'query',
                    ''
                )
            );

        $identifier =
            Request::query(
                'identifier',
                ''
            );

        $errors =
            $this->validator->validate([
                'q' =>
                $value,

                'identifier' =>
                $identifier,
            ]);

        if ($errors !== []) {
            $this->error(
                'پارامترهای پیشنهاد جستجو نامعتبر هستند.',
                422,
                $errors
            );
        }

        $query =
            new SearchQuery(
                $this->validator->normalizeQuery(
                    $value
                ),

                SearchQuery::DEFAULT_PAGE,

                SearchQuery::DEFAULT_PER_PAGE,

                null,

                null,

                null,

                SearchQuery::DEFAULT_SORT,

                null,

                null,

                [],

                null,

                $this->validator->normalizeIdentifier(
                    $identifier
                )
            );

        $suggestions =
            $this->service->suggest(
                $query
            );

        $this->success(
            $suggestions,
            'پیشنهادهای جستجو دریافت شد.'
        );
    }

    public function filters(): never
    {
        $filters =
            $this->service->filters();

        $this->success(
            $filters,
            'فیلترهای جستجو دریافت شد.'
        );
    }
}
