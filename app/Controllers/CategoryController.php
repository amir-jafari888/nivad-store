<?php

declare(strict_types=1);

final class CategoryController extends Controller
{
    private CategoryService $service;

    public function __construct(
        ?CategoryService $service = null
    ) {
        $this->service =
            $service ??
            new CategoryService();
    }

    /*
     * 09.73
     * List
     */
    public function index(): never
    {
        $categories =
            $this->service->findAll();

        $this->success(
            $categories,
            'لیست دسته‌بندی‌ها دریافت شد.'
        );
    }

    /*
     * 09.73
     * Show
     */
    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دسته‌بندی نامعتبر است.',
                422
            );
        }

        $category =
            $this->service->findById($id);

        if ($category === null) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $category,
            'اطلاعات دسته‌بندی دریافت شد.'
        );
    }

    /*
     * 09.73
     * Create
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['parentId'] =
            array_key_exists(
                'parentId',
                $data
            ) &&
            $data['parentId'] !== ''
            ? (int)$data['parentId']
            : null;

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
            (new CategoryValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات دسته‌بندی نامعتبر است.',
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
                'اسلاگ دسته‌بندی قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ دسته‌بندی تکراری است.'
                ]
            );
        }

        if (
            $data['parentId'] !== null &&
            !$this->service->exists(
                $data['parentId']
            )
        ) {
            $this->error(
                'دسته‌بندی والد پیدا نشد.',
                422,
                [
                    'parentId' =>
                    'دسته‌بندی والد نامعتبر است.'
                ]
            );
        }

        $category =
            $this->service->create(
                $data['parentId'],
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['status'],
                $data['sortOrder']
            );

        $this->success(
            $category,
            'دسته‌بندی با موفقیت ایجاد شد.',
            201
        );
    }

    /*
     * 09.73
     * Update
     */
    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دسته‌بندی نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById($id) === null
        ) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['parentId'] =
            array_key_exists(
                'parentId',
                $data
            ) &&
            $data['parentId'] !== ''
            ? (int)$data['parentId']
            : null;

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
            (new CategoryValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات دسته‌بندی نامعتبر است.',
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
                'اسلاگ دسته‌بندی قبلاً استفاده شده است.',
                422,
                [
                    'slug' =>
                    'اسلاگ دسته‌بندی تکراری است.'
                ]
            );
        }

        if (
            $data['parentId'] !== null &&
            !$this->service->exists(
                $data['parentId']
            )
        ) {
            $this->error(
                'دسته‌بندی والد پیدا نشد.',
                422,
                [
                    'parentId' =>
                    'دسته‌بندی والد نامعتبر است.'
                ]
            );
        }

        if (
            $data['parentId'] === $id
        ) {
            $this->error(
                'دسته‌بندی نمی‌تواند والد خودش باشد.',
                422,
                [
                    'parentId' =>
                    'شناسه والد نامعتبر است.'
                ]
            );
        }

        $category =
            $this->service->update(
                $id,
                $data['parentId'],
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['status'],
                $data['sortOrder']
            );

        if ($category === null) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $category,
            'دسته‌بندی با موفقیت بروزرسانی شد.'
        );
    }

    /*
     * 09.73
     * Delete
     */
    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دسته‌بندی نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'دسته‌بندی با موفقیت حذف شد.'
        );
    }

    /*
     * 09.74
     * Active
     */
    public function active(): never
    {
        $categories =
            $this->service->findActive();

        $this->success(
            $categories,
            'لیست دسته‌بندی‌های فعال دریافت شد.'
        );
    }

    /*
     * 09.74
     * Restore
     */
    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دسته‌بندی نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->restore($id)
        ) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'دسته‌بندی با موفقیت بازیابی شد.'
        );
    }

    /*
     * 09.74
     * Status
     */
    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دسته‌بندی نامعتبر است.',
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
                'وضعیت دسته‌بندی نامعتبر است.',
                422,
                [
                    'status' =>
                    'وضعیت باید active یا inactive باشد.'
                ]
            );
        }

        $category =
            $this->service->setStatus(
                $id,
                $status
            );

        if ($category === null) {
            $this->error(
                'دسته‌بندی موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $category,
            'وضعیت دسته‌بندی بروزرسانی شد.'
        );
    }
}
