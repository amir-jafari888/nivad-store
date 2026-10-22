<?php

declare(strict_types=1);
final class ProductTypeController extends Controller
{
    private ProductTypeService $service;

    public function __construct(?ProductTypeService $service = null)
    {
        $this->service = $service ?? new ProductTypeService();
    }

    public function index(): never
    {
        $this->success($this->service->findAll(), 'لیست انواع محصول دریافت شد.');
    }

    public function show(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        if ($id < 1) {
            $this->error('شناسه نوع محصول نامعتبر است.', 422);
        }
        $item = $this->service->findById($id);
        if ($item === null) {
            $this->error('نوع محصول موردنظر پیدا نشد.', 404);
        }
        $this->success($item, 'اطلاعات نوع محصول دریافت شد.');
    }

    public function store(): never
    {
        $data = Request::isJson() ? Request::json() : $_POST;
        $data['name'] = trim((string)($data['name'] ?? ''));
        $data['slug'] = trim((string)($data['slug'] ?? ''));
        $data['description'] = array_key_exists('description', $data) ? trim((string)$data['description']) : null;
        $data['status'] = trim((string)($data['status'] ?? 'active'));
        $data['sortOrder'] = (int)($data['sortOrder'] ?? 0);
        $errors = (new ProductTypeValidator())->validate($data);
        if ($errors !== []) {
            $this->error('اطلاعات نوع محصول نامعتبر است.', 422, $errors);
        }
        if ($this->service->findBySlug($data['slug']) !== null) {
            $this->error('اسلاگ نوع محصول تکراری است.', 422, ['slug' => 'اسلاگ نوع محصول تکراری است.']);
        }
        $item = $this->service->create($data['name'], $data['slug'], $data['description'], $data['status'], $data['sortOrder']);
        $this->success($item, 'نوع محصول با موفقیت ایجاد شد.', 201);
    }

    public function update(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        if ($id < 1) {
            $this->error('شناسه نوع محصول نامعتبر است.', 422);
        }
        if ($this->service->findById($id) === null) {
            $this->error('نوع محصول موردنظر پیدا نشد.', 404);
        }
        $data = Request::isJson() ? Request::json() : $_POST;
        $data['name'] = trim((string)($data['name'] ?? ''));
        $data['slug'] = trim((string)($data['slug'] ?? ''));
        $data['description'] = array_key_exists('description', $data) ? trim((string)$data['description']) : null;
        $data['status'] = trim((string)($data['status'] ?? 'active'));
        $data['sortOrder'] = (int)($data['sortOrder'] ?? 0);
        $errors = (new ProductTypeValidator())->validate($data);
        $same = $this->service->findBySlug($data['slug']);
        if ($errors === [] && $same !== null && $same->id !== $id) {
            $errors['slug'] = 'اسلاگ نوع محصول تکراری است.';
        }
        if ($errors !== []) {
            $this->error('اطلاعات نوع محصول نامعتبر است.', 422, $errors);
        }
        $item = $this->service->update($id, $data['name'], $data['slug'], $data['description'], $data['status'], $data['sortOrder']);
        $this->success($item, 'نوع محصول با موفقیت ویرایش شد.');
    }

    public function delete(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        $this->success(['deleted' => $this->service->delete($id)], 'نوع محصول حذف شد.');
    }

    public function restore(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        $this->success(['restored' => $this->service->restore($id)], 'نوع محصول بازیابی شد.');
    }

    public function status(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        $status = (string)(Request::post('status', 'active'));
        $this->success($this->service->setStatus($id, $status), 'وضعیت نوع محصول تغییر کرد.');
    }
}
