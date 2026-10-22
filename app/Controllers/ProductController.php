<?php

declare(strict_types=1);

final class ProductController extends Controller
{
    private ProductService $service;

    public function __construct(
        ?ProductService $service = null
    ) {
        $this->service =
            $service ??
            new ProductService();
    }

    /*
     * 09.63
     * Product list
     */
    public function index(): never
    {
        $products =
            $this->service->findAll();

        $this->success(
            $products,
            'لیست محصولات دریافت شد.'
        );
    }

    /*
     * 09.64
     * Product details
     */
    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $product =
            $this->service->findById($id);

        if ($product === null) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $product,
            'اطلاعات محصول دریافت شد.'
        );
    }

    /*
     * 09.65
     * Product create
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['categoryId'] =
            (int)($data['categoryId'] ?? 0);

        $data['brandId'] =
            array_key_exists('brandId', $data) &&
            $data['brandId'] !== ''
            ? (int)$data['brandId']
            : null;

        $data['name'] =
            trim((string)($data['name'] ?? ''));

        $data['slug'] =
            trim((string)($data['slug'] ?? ''));

        $data['shortDescription'] =
            array_key_exists(
                'shortDescription',
                $data
            )
            ? trim(
                (string)$data['shortDescription']
            )
            : null;

        $data['description'] =
            array_key_exists(
                'description',
                $data
            )
            ? trim(
                (string)$data['description']
            )
            : null;

        $data['status'] =
            trim(
                (string)(
                    $data['status'] ?? 'active'
                )
            );

        $data['isFeatured'] =
            filter_var(
                $data['isFeatured'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

        $errors =
            (new ProductValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات محصول نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            $this->service->findBySlug(
                $data['slug']
            ) !== null
        ) {
            $this->error(
                'اسلاگ محصول قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ محصول تکراری است.'
                ]
            );
        }

        $product =
            $this->service->create(
                $data['categoryId'],
                $data['brandId'],
                $data['name'],
                $data['slug'],
                $data['shortDescription'],
                $data['description'],
                $data['status'],
                $data['isFeatured']
            );

        $this->success(
            $product,
            'محصول با موفقیت ایجاد شد.',
            201
        );
    }

    /*
     * 09.66
     * Product update
     */
    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $product =
            $this->service->findById($id);

        if ($product === null) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['categoryId'] =
            (int)($data['categoryId'] ?? 0);

        $data['brandId'] =
            array_key_exists('brandId', $data) &&
            $data['brandId'] !== ''
            ? (int)$data['brandId']
            : null;

        $data['name'] =
            trim((string)($data['name'] ?? ''));

        $data['slug'] =
            trim((string)($data['slug'] ?? ''));

        $data['shortDescription'] =
            array_key_exists(
                'shortDescription',
                $data
            )
            ? trim(
                (string)$data['shortDescription']
            )
            : null;

        $data['description'] =
            array_key_exists(
                'description',
                $data
            )
            ? trim(
                (string)$data['description']
            )
            : null;

        $data['status'] =
            trim(
                (string)(
                    $data['status'] ?? 'active'
                )
            );

        $data['isFeatured'] =
            filter_var(
                $data['isFeatured'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

        $errors =
            (new ProductValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات محصول نامعتبر است.',
                422,
                $errors
            );
        }

        $existing =
            $this->service->findBySlug(
                $data['slug']
            );

        if (
            $existing !== null &&
            $existing->id !== $id
        ) {
            $this->error(
                'اسلاگ محصول قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ محصول تکراری است.'
                ]
            );
        }

        $updated =
            $this->service->update(
                $id,
                $data['categoryId'],
                $data['brandId'],
                $data['name'],
                $data['slug'],
                $data['shortDescription'],
                $data['description'],
                $data['status'],
                $data['isFeatured']
            );

        if ($updated === null) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $updated,
            'محصول با موفقیت بروزرسانی شد.'
        );
    }

    /*
     * 09.67
     * Product delete
     */
    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'محصول با موفقیت حذف شد.'
        );
    }

    /*
     * 09.68
     * Active + restore
     */
    public function active(): never
    {
        $products =
            $this->service->findActive();

        $this->success(
            $products,
            'لیست محصولات فعال دریافت شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->restore($id)
        ) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'محصول با موفقیت بازیابی شد.'
        );
    }

    /*
     * 09.69
     * Product status
     */
    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)(
                    $data['status'] ?? ''
                )
            );

        if ($status === '') {
            $this->error(
                'وضعیت محصول الزامی است.',
                422,
                [
                    'status' =>
                    'وضعیت محصول الزامی است.'
                ]
            );
        }

        $product =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($product === null) {
            $this->error(
                'محصول موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $product,
            'وضعیت محصول بروزرسانی شد.'
        );
    }
}
