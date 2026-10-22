<?php

declare(strict_types=1);

final class SetupController extends Controller
{
    private SetupService $service;

    public function __construct(
        ?SetupService $service = null
    ) {
        $this->service =
            $service ??
            new SetupService();
    }

    public function devices(): never
    {
        $this->success(
            [
                'items' =>
                $this->service->findActiveDevices(),
            ],
            'دستگاه‌های Setup دریافت شد.'
        );
    }

    public function accessories(
        array $parameters = []
    ): never {
        $deviceId =
            (int)($parameters['deviceId'] ?? 0);

        if ($deviceId <= 0) {
            $this->error(
                'شناسه دستگاه نامعتبر است.',
                422
            );
        }

        $device =
            $this->service->findDeviceById(
                $deviceId
            );

        if ($device === null) {
            $this->error(
                'دستگاه موردنظر پیدا نشد.',
                404
            );
        }

        $relationType =
            isset($_GET['relationType'])
            ? trim((string)$_GET['relationType'])
            : null;

        $items =
            $this->service
            ->findCompatibleAccessories(
                $deviceId,
                $relationType
            );

        $this->success(
            [
                'device' => $device,
                'items' => $items,
            ],
            'اکسسوری‌های Setup دریافت شد.'
        );
    }

    public function products(
        array $parameters = []
    ): never {
        $deviceId =
            (int)($parameters['deviceId'] ?? 0);

        if ($deviceId <= 0) {
            $this->error(
                'شناسه دستگاه نامعتبر است.',
                422
            );
        }

        $device =
            $this->service->findDeviceById(
                $deviceId
            );

        if ($device === null) {
            $this->error(
                'دستگاه موردنظر پیدا نشد.',
                404
            );
        }

        $relationType =
            isset($_GET['relationType'])
            ? trim((string)$_GET['relationType'])
            : null;

        $items =
            $this->service
            ->findCompatibleProducts(
                $deviceId,
                $relationType
            );

        $this->success(
            [
                'device' => $device,
                'items' => $items,
            ],
            'محصولات Setup دریافت شد.'
        );
    }
}
