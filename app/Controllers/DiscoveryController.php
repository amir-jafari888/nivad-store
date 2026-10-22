<?php

declare(strict_types=1);

final class DiscoveryController extends Controller
{
    private DiscoveryService $service;

    public function __construct(
        ?DiscoveryService $service = null
    ) {
        $this->service =
            $service ?? new DiscoveryService();
    }

    public function tags(): never
    {
        $this->success([
            'tags' => $this->service->getActiveTags(),
        ]);
    }

    public function productsByTag(
        array $parameters = []
    ): never {
        $tagId = (int)($parameters['tagId'] ?? 0);

        if ($tagId <= 0) {
            $this->error(
                'شناسه تگ Discovery نامعتبر است.',
                422
            );
        }

        $limit = max(
            1,
            min(
                100,
                (int)($_GET['limit'] ?? 20)
            )
        );

        $offset = max(
            0,
            (int)($_GET['offset'] ?? 0)
        );

        $this->success(
            $this->service->getPaginatedProductsByTagId(
                $tagId,
                $limit,
                $offset
            )
        );
    }

    public function productsBySlug(
        array $parameters = []
    ): never {
        $slug = trim(
            (string)($parameters['slug'] ?? '')
        );

        if ($slug === '') {
            $this->error(
                'شناسه متنی Discovery نامعتبر است.',
                422
            );
        }

        $limit = max(
            1,
            min(
                100,
                (int)($_GET['limit'] ?? 20)
            )
        );

        $offset = max(
            0,
            (int)($_GET['offset'] ?? 0)
        );

        $this->success([
            'items' => $this->service
                ->getProductsByTagSlug(
                    $slug,
                    $limit,
                    $offset
                ),
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function adminTags(): never
    {
        $this->success([
            'tags' => $this->service->getActiveTags(),
        ]);
    }

    public function storeTag(): never
    {
        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $name = trim(
            (string)($data['name'] ?? '')
        );

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        $status = trim(
            (string)($data['status'] ?? DiscoveryTag::STATUS_ACTIVE)
        );

        if ($name === '' || $slug === '') {
            $this->error(
                'نام و slug تگ الزامی است.',
                422
            );
        }

        if (
            !in_array(
                $status,
                [
                    DiscoveryTag::STATUS_ACTIVE,
                    DiscoveryTag::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $this->error(
                'وضعیت تگ نامعتبر است.',
                422
            );
        }

        $tag = $this->service->createTag(
            $name,
            $slug,
            isset($data['description'])
                ? (string)$data['description']
                : null,
            $status,
            (int)($data['sortOrder'] ?? 0)
        );

        $this->success(
            $tag,
            'تگ Discovery ایجاد شد.'
        );
    }

    public function updateTag(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه تگ نامعتبر است.',
                422
            );
        }

        $data = Request::isJson()
            ? Request::json()
            : $_POST;

        $name = trim(
            (string)($data['name'] ?? '')
        );

        $slug = trim(
            (string)($data['slug'] ?? '')
        );

        $status = trim(
            (string)($data['status'] ?? '')
        );

        if ($name === '' || $slug === '') {
            $this->error(
                'نام و slug تگ الزامی است.',
                422
            );
        }

        if (
            !in_array(
                $status,
                [
                    DiscoveryTag::STATUS_ACTIVE,
                    DiscoveryTag::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $this->error(
                'وضعیت تگ نامعتبر است.',
                422
            );
        }

        $tag = $this->service->updateTag(
            $id,
            $name,
            $slug,
            isset($data['description'])
                ? (string)$data['description']
                : null,
            $status,
            (int)($data['sortOrder'] ?? 0)
        );

        if ($tag === null) {
            $this->error(
                'تگ Discovery پیدا نشد.',
                404
            );
        }

        $this->success(
            $tag,
            'تگ Discovery بروزرسانی شد.'
        );
    }

    public function destroyTag(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه تگ نامعتبر است.',
                422
            );
        }

        if (!$this->service->deleteTag($id)) {
            $this->error(
                'تگ Discovery پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'تگ Discovery حذف شد.'
        );
    }
}
