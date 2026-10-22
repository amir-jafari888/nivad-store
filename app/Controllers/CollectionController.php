<?php

declare(strict_types=1);

final class CollectionController extends Controller
{
    private CollectionService $service;

    public function __construct(
        ?CollectionService $service = null
    ) {
        $this->service =
            $service ??
            new CollectionService();
    }

    public function index(): never
    {
        $this->success([
            'items' => $this->service->findActive(),
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->findActiveById($id);

        if ($collection === null) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'collection' => $collection,
            'items' => $this->service->findProducts($id),
        ]);
    }

    public function showBySlug(
        array $parameters = []
    ): never {
        $slug =
            trim(
                (string)($parameters['slug'] ?? '')
            );

        if ($slug === '') {
            $this->error(
                'نامک مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->findBySlug($slug);

        if (
            $collection === null ||
            !$this->service->isCurrentlyAvailable(
                $collection
            )
        ) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'collection' => $collection,
            'items' =>
            $this->service->findProducts(
                $collection->id
            ),
        ]);
    }

    public function adminIndex(): never
    {
        $this->success([
            'items' => $this->service->findAll(),
        ]);
    }

    public function adminShow(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->findById($id);

        if ($collection === null) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'collection' => $collection,
            'productIds' =>
            $this->service->findProductIds($id),
        ]);
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $name =
            trim((string)($data['name'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $description =
            isset($data['description'])
            ? trim((string)$data['description'])
            : null;

        $type =
            trim(
                (string)(
                    $data['collection_type']
                    ?? Collection::TYPE_MANUAL
                )
            );

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Collection::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? (string)$data['starts_at']
            : null;

        $endsAt =
            isset($data['ends_at']) &&
            $data['ends_at'] !== ''
            ? (string)$data['ends_at']
            : null;

        if (
            $name === '' ||
            $slug === '' ||
            !in_array(
                $type,
                [
                    Collection::TYPE_MANUAL,
                    Collection::TYPE_AUTOMATIC,
                ],
                true
            ) ||
            !in_array(
                $status,
                [
                    Collection::STATUS_ACTIVE,
                    Collection::STATUS_INACTIVE,
                ],
                true
            ) ||
            $sortOrder < 0
        ) {
            $this->error(
                'اطلاعات مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->create(
                $name,
                $slug,
                $description,
                $type,
                $status,
                $sortOrder,
                $startsAt,
                $endsAt
            );

        if ($collection === null) {
            $this->error(
                'ایجاد مجموعه انجام نشد.',
                500
            );
        }

        $this->success(
            ['collection' => $collection],
            'مجموعه ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        if ($this->service->findById($id) === null) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $name =
            trim((string)($data['name'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $description =
            isset($data['description'])
            ? trim((string)$data['description'])
            : null;

        $type =
            trim(
                (string)(
                    $data['collection_type']
                    ?? Collection::TYPE_MANUAL
                )
            );

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Collection::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? (string)$data['starts_at']
            : null;

        $endsAt =
            isset($data['ends_at']) &&
            $data['ends_at'] !== ''
            ? (string)$data['ends_at']
            : null;

        if (
            $name === '' ||
            $slug === '' ||
            !in_array(
                $type,
                [
                    Collection::TYPE_MANUAL,
                    Collection::TYPE_AUTOMATIC,
                ],
                true
            ) ||
            !in_array(
                $status,
                [
                    Collection::STATUS_ACTIVE,
                    Collection::STATUS_INACTIVE,
                ],
                true
            ) ||
            $sortOrder < 0
        ) {
            $this->error(
                'اطلاعات مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->update(
                $id,
                $name,
                $slug,
                $description,
                $type,
                $status,
                $sortOrder,
                $startsAt,
                $endsAt
            );

        if ($collection === null) {
            $this->error(
                'ویرایش مجموعه انجام نشد.',
                500
            );
        }

        $this->success(
            ['collection' => $collection],
            'مجموعه ویرایش شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        if (!$this->service->delete($id)) {
            $this->error(
                'حذف مجموعه انجام نشد.',
                404
            );
        }

        $this->success(
            [],
            'مجموعه حذف شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        $collection =
            $this->service->restore($id);

        if ($collection === null) {
            $this->error(
                'بازیابی مجموعه انجام نشد.',
                404
            );
        }

        $this->success(
            ['collection' => $collection],
            'مجموعه بازیابی شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id = (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)($data['status'] ?? '')
            );

        $collection =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($collection === null) {
            $this->error(
                'وضعیت مجموعه نامعتبر است.',
                422
            );
        }

        $this->success(
            ['collection' => $collection],
            'وضعیت مجموعه تغییر کرد.'
        );
    }

    public function attachProduct(
        array $parameters = []
    ): never {
        $collectionId =
            (int)($parameters['id'] ?? 0);

        if ($collectionId <= 0) {
            $this->error(
                'شناسه مجموعه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $productId =
            (int)($data['product_id'] ?? 0);

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        $errors =
            CollectionValidator::validateProductMapping(
                $collectionId,
                $productId,
                $sortOrder
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات اتصال محصول نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById(
                $collectionId
            ) === null
        ) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        if (
            !$this->service->attachProductValidated(
                $collectionId,
                $productId,
                $sortOrder
            )
        ) {
            $this->error(
                'اتصال محصول به مجموعه انجام نشد.',
                409
            );
        }

        $this->success(
            [
                'collectionId' => $collectionId,
                'productId' => $productId,
                'sortOrder' => $sortOrder,
            ],
            'محصول به مجموعه متصل شد.'
        );
    }

    public function detachProduct(
        array $parameters = []
    ): never {
        $collectionId =
            (int)($parameters['id'] ?? 0);

        $productId =
            (int)($parameters['productId'] ?? 0);

        if (
            $collectionId <= 0 ||
            $productId <= 0
        ) {
            $this->error(
                'شناسه مجموعه یا محصول نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById(
                $collectionId
            ) === null
        ) {
            $this->error(
                'مجموعه موردنظر پیدا نشد.',
                404
            );
        }

        if (
            !$this->service->detachProductValidated(
                $collectionId,
                $productId
            )
        ) {
            $this->error(
                'اتصال محصول حذف نشد.',
                404
            );
        }

        $this->success(
            [
                'collectionId' => $collectionId,
                'productId' => $productId,
            ],
            'محصول از مجموعه جدا شد.'
        );
    }
}
