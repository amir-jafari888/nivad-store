<?php

declare(strict_types=1);

final class BrandController extends Controller
{
    private BrandService $service;

    public function __construct(
        ?BrandService $service = null
    ) {
        $this->service =
            $service ??
            new BrandService();
    }

    /*
     * 09.78
     * List
     */
    public function index(): never
    {
        $brands =
            $this->service->findAll();

        $this->success(
            $brands,
            'لیست برندها دریافت شد.'
        );
    }

    /*
     * 09.78
     * Show
     */
    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه برند نامعتبر است.',
                422
            );
        }

        $brand =
            $this->service->findById($id);

        if ($brand === null) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $brand,
            'اطلاعات برند دریافت شد.'
        );
    }

    /*
     * 09.78
     * Create
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['name'] =
            trim(
                (string)($data['name'] ?? '')
            );

        $data['slug'] =
            trim(
                (string)($data['slug'] ?? '')
            );

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

        $data['sortOrder'] =
            (int)(
                $data['sortOrder'] ?? 0
            );

        $errors =
            (new BrandValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات برند نامعتبر است.',
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
                'اسلاگ برند قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ برند تکراری است.'
                ]
            );
        }

        $brand =
            $this->service->create(
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['status'],
                $data['sortOrder']
            );

        $this->success(
            $brand,
            'برند با موفقیت ایجاد شد.',
            201
        );
    }

    /*
     * 09.78
     * Update
     */
    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه برند نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById($id) === null
        ) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['name'] =
            trim(
                (string)($data['name'] ?? '')
            );

        $data['slug'] =
            trim(
                (string)($data['slug'] ?? '')
            );

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

        $data['sortOrder'] =
            (int)(
                $data['sortOrder'] ?? 0
            );

        $errors =
            (new BrandValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات برند نامعتبر است.',
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
                'اسلاگ برند قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ برند تکراری است.'
                ]
            );
        }

        $brand =
            $this->service->update(
                $id,
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['status'],
                $data['sortOrder']
            );

        if ($brand === null) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $brand,
            'برند با موفقیت بروزرسانی شد.'
        );
    }

    /*
     * 09.78
     * Delete
     */
    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه برند نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'برند با موفقیت حذف شد.'
        );
    }

    /*
     * 09.79
     * Active
     */
    public function active(): never
    {
        $brands =
            $this->service->findActive();

        $this->success(
            $brands,
            'لیست برندهای فعال دریافت شد.'
        );
    }

    /*
     * 09.79
     * Restore
     */
    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه برند نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->restore($id)
        ) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'برند با موفقیت بازیابی شد.'
        );
    }

    /*
     * 09.79
     * Status
     */
    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه برند نامعتبر است.',
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

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $this->error(
                'وضعیت برند نامعتبر است.',
                422,
                [
                    'status' =>
                    'وضعیت باید active یا inactive باشد.'
                ]
            );
        }

        $brand =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($brand === null) {
            $this->error(
                'برند موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $brand,
            'وضعیت برند بروزرسانی شد.'
        );
    }
}
