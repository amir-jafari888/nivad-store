<?php

declare(strict_types=1);

final class SearchService
{
    private SearchRepository $repository;

    public function __construct(
        ?SearchRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new SearchRepository();
    }

    public function search(
        SearchQuery $query
    ): array {
        return $this->repository->search(
            $query
        );
    }

    public function count(
        SearchQuery $query
    ): int {
        return $this->repository->count(
            $query
        );
    }

    public function suggest(
        SearchQuery $query,
        int $limit = 6
    ): array {
        return $this->repository->suggest(
            $query,
            $limit
        );
    }

    public function filters(): array
    {
        return $this->repository->filters();
    }

    public function searchWithMeta(
        SearchQuery $query
    ): array {
        $items =
            $this->search($query);

        $total =
            $this->count($query);

        return [
            'items' =>
            $items,

            'total' =>
            $total,

            'page' =>
            $query->page,

            'per_page' =>
            $query->perPage,

            'query' =>
            $query->normalizedQuery(),
        ];
    }
}
