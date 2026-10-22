<?php

declare(strict_types=1);

final class SliderController extends Controller
{
    private SliderService $service;

    public function __construct(
        ?SliderService $service = null
    ) {
        $this->service =
            $service ??
            new SliderService();
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
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
                422
            );
        }

        $slider =
            $this->service->findActiveById($id);

        if ($slider === null) {
            $this->error(
                'اسلایدر موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'slider' => $slider,
            'slides' =>
            $this->service
                ->findActiveSlidesBySliderId($id),
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
                'نامک اسلایدر نامعتبر است.',
                422
            );
        }

        $slider =
            $this->service->findBySlug($slug);

        if (
            $slider === null ||
            !$this->service->isCurrentlyAvailable(
                $slider
            )
        ) {
            $this->error(
                'اسلایدر موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'slider' => $slider,
            'slides' =>
            $this->service
                ->findActiveSlidesBySliderId(
                    $slider->id
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
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
                422
            );
        }

        $slider =
            $this->service->findById($id);

        if ($slider === null) {
            $this->error(
                'اسلایدر موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'slider' => $slider,
            'slides' =>
            $this->service
                ->findSlidesBySliderId($id),
        ]);
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $validator =
            new SliderValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات اسلایدر نامعتبر است.',
                422,
                $errors
            );
        }

        $name =
            trim(
                (string)($data['name'] ?? '')
            );

        $slug =
            trim(
                (string)($data['slug'] ?? '')
            );

        $description =
            isset($data['description'])
            ? trim(
                (string)$data['description']
            )
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Slider::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $endsAt =
            isset($data['ends_at']) &&
            $data['ends_at'] !== ''
            ? trim(
                (string)$data['ends_at']
            )
            : null;

        $slider =
            $this->service->create(
                $name,
                $slug,
                $description,
                $status,
                $sortOrder,
                $startsAt,
                $endsAt
            );

        if ($slider === null) {
            $this->error(
                'ایجاد اسلایدر انجام نشد.',
                500
            );
        }

        $this->success(
            ['slider' => $slider],
            'اسلایدر ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById($id) === null
        ) {
            $this->error(
                'اسلایدر موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;


        $validator =
            new SliderValidator();

        $errors =
            $validator->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات اسلایدر نامعتبر است.',
                422,
                $errors
            );
        }

        $name =
            trim(
                (string)($data['name'] ?? '')
            );

        $slug =
            trim(
                (string)($data['slug'] ?? '')
            );

        $description =
            isset($data['description'])
            ? trim(
                (string)$data['description']
            )
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Slider::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $endsAt =
            isset($data['ends_at']) &&
            $data['ends_at'] !== ''
            ? trim(
                (string)$data['ends_at']
            )
            : null;

        $slider =
            $this->service->update(
                $id,
                $name,
                $slug,
                $description,
                $status,
                $sortOrder,
                $startsAt,
                $endsAt
            );

        if ($slider === null) {
            $this->error(
                'ویرایش اسلایدر انجام نشد.',
                500
            );
        }

        $this->success(
            ['slider' => $slider],
            'اسلایدر ویرایش شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'حذف اسلایدر انجام نشد.',
                404
            );
        }

        $this->success(
            [],
            'اسلایدر حذف شد.'
        );
    }

    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
                422
            );
        }

        $slider =
            $this->service->restore($id);

        if ($slider === null) {
            $this->error(
                'بازیابی اسلایدر انجام نشد.',
                404
            );
        }

        $this->success(
            ['slider' => $slider],
            'اسلایدر بازیابی شد.'
        );
    }

    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اسلایدر نامعتبر است.',
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

        $slider =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($slider === null) {
            $this->error(
                'وضعیت اسلایدر نامعتبر است.',
                422
            );
        }

        $this->success(
            ['slider' => $slider],
            'وضعیت اسلایدر تغییر کرد.'
        );
    }
}
