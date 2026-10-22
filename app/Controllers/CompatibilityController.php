<?php

declare(strict_types=1);

final class CompatibilityController extends Controller
{
    private CompatibilityService $service;

    public function __construct(
        ?CompatibilityService $service = null
    ) {
        $this->service =
            $service ??
            new CompatibilityService();
    }

    public function devices(): never
    {
        $this->success(
            [
                'items' =>
                $this->service->findActiveDevices(),
            ],
            'دستگاه‌های سازگاری دریافت شد.'
        );
    }

    public function products(
        array $parameters = []
    ): never {
        $deviceId =
            (int)($parameters['deviceId'] ?? 0);

        if ($deviceId < 1) {
            $this->error(
                'شناسه دستگاه نامعتبر است.',
                422
            );
        }

        $type =
            isset($_GET['type'])
            ? trim((string)$_GET['type'])
            : null;

        if ($type === '') {
            $type = null;
        }

        if (
            $type !== null &&
            !in_array(
                $type,
                [
                    ProductCompatibility::TYPE_COMPATIBLE,
                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
                    ProductCompatibility::TYPE_NOT_COMPATIBLE,
                ],
                true
            )
        ) {
            $this->error(
                'نوع سازگاری نامعتبر است.',
                422
            );
        }

        $device =
            $this->service->findDeviceById(
                $deviceId
            );

        if (
            $device === null ||
            $device->status !==
            CompatibilityDevice::STATUS_ACTIVE ||
            $device->deletedAt !== null
        ) {
            $this->error(
                'دستگاه موردنظر پیدا نشد.',
                404
            );
        }

        $products =
            $this->service->findCompatibleProducts(
                $deviceId,
                $type
            );

        $this->success(
            [
                'device' => [
                    'id' =>
                    $device->id,

                    'brandName' =>
                    $device->brandName,

                    'name' =>
                    $device->name,

                    'model' =>
                    $device->model,

                    'deviceType' =>
                    $device->deviceType,

                    'slug' =>
                    $device->slug,
                ],

                'items' =>
                $products,
            ],
            'محصولات سازگار دریافت شد.'
        );
    }

    public function adminIndex(): never
    {
        $summary =
            $this->service->getAdminSummary();

        $this->success(
            [
                'summary' => $summary,
            ],
            'خلاصه سازگاری دریافت شد.'
        );
    }

    public function adminDevices(): never
    {
        $this->success(
            [
                'items' => $this->service->findAllDevices(),
            ],
            'دستگاه‌ها دریافت شدند.'
        );
    }

    public function adminStoreDevice(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values = $this->deviceData($data);

        $device =
            $this->service->createDevice(
                ...$values
            );

        if ($device === null) {
            $this->error(
                'ایجاد دستگاه انجام نشد.',
                500
            );
        }

        $this->success(
            ['device' => $device],
            'دستگاه ایجاد شد.'
        );
    }

    public function adminUpdateDevice(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id < 1) {
            $this->error(
                'شناسه دستگاه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values = $this->deviceData($data);

        $device =
            $this->service->updateDevice(
                $id,
                ...$values
            );

        if ($device === null) {
            $this->error(
                'ویرایش دستگاه انجام نشد.',
                404
            );
        }

        $this->success(
            ['device' => $device],
            'دستگاه ویرایش شد.'
        );
    }

    public function adminDeleteDevice(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if (
            $id < 1 ||
            !$this->service->deleteDevice($id)
        ) {
            $this->error(
                'حذف دستگاه انجام نشد.',
                404
            );
        }

        $this->success(
            [],
            'دستگاه حذف شد.'
        );
    }

    public function adminStatusDevice(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)($data['status'] ?? '')
            );

        $device =
            $this->service->updateDeviceStatus(
                $id,
                $status
            );

        if ($device === null) {
            $this->error(
                'وضعیت دستگاه نامعتبر است.',
                422
            );
        }

        $this->success(
            ['device' => $device],
            'وضعیت دستگاه تغییر کرد.'
        );
    }

    public function adminMappings(): never
    {
        $this->success(
            [
                'items' =>
                $this->service->findAllMappings(),
            ],
            'ارتباط‌های سازگاری دریافت شدند.'
        );
    }

    public function adminStoreMapping(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values =
            $this->mappingData($data);

        try {
            $mapping =
                $this->service->createMapping(
                    $values['product_id'],
                    $values['device_id'],
                    $values['compatibility_type'],
                    $values['note'],
                    $values['sort_order']
                );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }

        $this->success(
            [
                'mapping' => $mapping,
            ],
            'ارتباط سازگاری ایجاد شد.'
        );
    }

    public function adminUpdateMapping(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values =
            $this->mappingData($data);

        $mapping =
            $this->service->updateMapping(
                $values['product_id'],
                $values['device_id'],
                $values['compatibility_type'],
                $values['note'],
                $values['sort_order']
            );

        if ($mapping === null) {
            $this->error(
                'ارتباط سازگاری پیدا نشد.',
                404
            );
        }

        $this->success(
            [
                'mapping' => $mapping,
            ],
            'ارتباط سازگاری ویرایش شد.'
        );
    }

    public function adminDeleteMapping(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $productId =
            (int)(
                $data['product_id'] ?? 0
            );

        $deviceId =
            (int)(
                $data['device_id'] ?? 0
            );

        if (
            $productId < 1 ||
            $deviceId < 1
        ) {
            $this->error(
                'شناسه Mapping نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->deleteMapping(
                $productId,
                $deviceId
            )
        ) {
            $this->error(
                'حذف ارتباط سازگاری انجام نشد.',
                404
            );
        }

        $this->success(
            [],
            'ارتباط سازگاری حذف شد.'
        );
    }

    private function mappingData(
        array $data
    ): array {
        $productId =
            (int)(
                $data['product_id'] ?? 0
            );

        $deviceId =
            (int)(
                $data['compatibility_device_id']
                ?? $data['device_id']
                ?? 0
            );

        $compatibilityType =
            trim(
                (string)(
                    $data['compatibility_type']
                    ?? ''
                )
            );

        $note =
            isset($data['note']) &&
            trim((string)$data['note']) !== ''
            ? trim((string)$data['note'])
            : null;

        $sortOrder =
            (int)(
                $data['sort_order'] ?? 0
            );

        if (
            $productId < 1 ||
            $deviceId < 1
        ) {
            $this->error(
                'شناسه محصول یا دستگاه نامعتبر است.',
                422
            );
        }

        if (
            $sortOrder < 0
        ) {
            $this->error(
                'ترتیب نامعتبر است.',
                422
            );
        }

        if (
            !in_array(
                $compatibilityType,
                [
                    ProductCompatibility::TYPE_COMPATIBLE,
                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
                    ProductCompatibility::TYPE_NOT_COMPATIBLE,
                ],
                true
            )
        ) {
            $this->error(
                'نوع سازگاری نامعتبر است.',
                422
            );
        }

        return [
            'product_id' =>
            $productId,

            'device_id' =>
            $deviceId,

            'compatibility_type' =>
            $compatibilityType,

            'note' =>
            $note,

            'sort_order' =>
            $sortOrder,
        ];
    }

    private function deviceData(
        array $data
    ): array {
        $brandName =
            trim((string)($data['brand_name'] ?? ''));

        $name =
            trim((string)($data['name'] ?? ''));

        $model =
            isset($data['model']) &&
            $data['model'] !== ''
            ? trim((string)$data['model'])
            : null;

        $deviceType =
            trim((string)($data['device_type'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $description =
            isset($data['description']) &&
            $data['description'] !== ''
            ? trim((string)$data['description'])
            : null;

        $image =
            isset($data['image']) &&
            $data['image'] !== ''
            ? trim((string)$data['image'])
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? CompatibilityDevice::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        if (
            $brandName === '' ||
            $name === '' ||
            $deviceType === '' ||
            $slug === '' ||
            $sortOrder < 0 ||
            !in_array(
                $status,
                [
                    CompatibilityDevice::STATUS_ACTIVE,
                    CompatibilityDevice::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $this->error(
                'اطلاعات دستگاه نامعتبر است.',
                422
            );
        }

        return [
            $brandName,
            $name,
            $model,
            $deviceType,
            $slug,
            $description,
            $image,
            $status,
            $sortOrder,
        ];
    }
}
