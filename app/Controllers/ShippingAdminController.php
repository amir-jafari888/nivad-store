<?php

declare(strict_types=1);

final class ShippingAdminController extends Controller
{
    private ShippingService $service;

    public function __construct(
        ?ShippingService $service = null
    ) {
        $this->service =
            $service ??
            new ShippingService();
    }

    public function index(): never
    {
        $this->success([
            'methods' =>
            $this->service
                ->findAllShippingMethods(),
        ]);
    }

    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values = $this->methodData($data);

        $method =
            $this->service->createShippingMethod(
                ...$values
            );

        if ($method === null) {
            $this->error(
                'ایجاد روش ارسال انجام نشد.',
                500
            );
        }

        $this->success(
            ['method' => $method],
            'روش ارسال ایجاد شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id < 1) {
            $this->error(
                'شناسه روش ارسال نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $values = $this->methodData($data);

        $method =
            $this->service->updateShippingMethod(
                $id,
                ...$values
            );

        if ($method === null) {
            $this->error(
                'روش ارسال پیدا نشد.',
                404
            );
        }

        $this->success(
            ['method' => $method],
            'روش ارسال به‌روزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if (
            $id < 1 ||
            !$this->service->deleteShippingMethod($id)
        ) {
            $this->error(
                'حذف روش ارسال انجام نشد.',
                404
            );
        }

        $this->success(
            [],
            'روش ارسال حذف شد.'
        );
    }

    public function zones(): never
    {
        $this->success([
            'zones' =>
            $this->service
                ->findAllShippingZones(),
        ]);
    }

    public function zoneShow(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه منطقه ارسال نامعتبر است.',
                422
            );
        }

        $zone =
            $this->service
            ->findShippingZoneById($id);

        if ($zone === null) {
            $this->error(
                'منطقه ارسال پیدا نشد.',
                404
            );
        }

        $this->success([
            'zone' => $zone,
            'locations' =>
            $this->service
                ->findZoneLocations($id),
        ]);
    }

    public function zoneStatus(
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
                (string)(
                    $data['status'] ?? ''
                )
            );

        if (
            $id <= 0 ||
            !in_array(
                $status,
                [
                    ShippingZone::STATUS_ACTIVE,
                    ShippingZone::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $this->error(
                'اطلاعات وضعیت منطقه ارسال نامعتبر است.',
                422
            );
        }

        $zone =
            $this->service
            ->updateShippingZoneStatus(
                $id,
                $status
            );

        if ($zone === null) {
            $this->error(
                'منطقه ارسال پیدا نشد.',
                404
            );
        }

        $this->success(
            $zone,
            'وضعیت منطقه ارسال به‌روزرسانی شد.'
        );
    }

    public function shipments(): never
    {
        $this->success([
            'shipments' =>
            $this->service
                ->findAllShipments(),
        ]);
    }

    public function shipmentShow(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه مرسوله نامعتبر است.',
                422
            );
        }

        $shipment =
            $this->service
            ->findShipmentById($id);

        if ($shipment === null) {
            $this->error(
                'مرسوله پیدا نشد.',
                404
            );
        }

        $this->success([
            'shipment' => $shipment,
            'items' =>
            $this->service
                ->findShipmentItems($id),
        ]);
    }

    public function shipmentStatus(
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
                (string)(
                    $data['status'] ?? ''
                )
            );

        $allowed = [
            Shipment::STATUS_PENDING,
            Shipment::STATUS_READY,
            Shipment::STATUS_SHIPPED,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_DELIVERED,
            Shipment::STATUS_RETURNED,
            Shipment::STATUS_CANCELLED,
        ];

        if (
            $id <= 0 ||
            !in_array(
                $status,
                $allowed,
                true
            )
        ) {
            $this->error(
                'اطلاعات وضعیت مرسوله نامعتبر است.',
                422
            );
        }

        $shipment =
            $this->service
            ->updateShipmentStatus(
                $id,
                $status
            );

        if ($shipment === null) {
            $this->error(
                'مرسوله پیدا نشد.',
                404
            );
        }

        $this->success(
            $shipment,
            'وضعیت مرسوله به‌روزرسانی شد.'
        );
    }

    private function methodData(
        array $data
    ): array {
        $name =
            trim((string)($data['name'] ?? ''));

        $slug =
            trim((string)($data['slug'] ?? ''));

        $description =
            isset($data['description'])
            && $data['description'] !== ''
            ? trim((string)$data['description'])
            : null;

        $baseCost =
            trim((string)($data['base_cost'] ?? ''));

        $currency =
            trim((string)($data['currency'] ?? ''));

        $minDays =
            isset($data['estimated_min_days'])
            && $data['estimated_min_days'] !== ''
            ? (int)$data['estimated_min_days']
            : null;

        $maxDays =
            isset($data['estimated_max_days'])
            && $data['estimated_max_days'] !== ''
            ? (int)$data['estimated_max_days']
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? ShippingMethod::STATUS_ACTIVE
                )
            );

        $sortOrder =
            (int)($data['sort_order'] ?? 0);

        if (
            $name === '' ||
            $slug === '' ||
            $baseCost === '' ||
            $currency === '' ||
            $sortOrder < 0 ||
            ($minDays !== null && $minDays < 0) ||
            ($maxDays !== null && $maxDays < 0) ||
            (
                $minDays !== null &&
                $maxDays !== null &&
                $maxDays < $minDays
            ) ||
            !in_array(
                $status,
                [
                    ShippingMethod::STATUS_ACTIVE,
                    ShippingMethod::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $this->error(
                'اطلاعات روش ارسال نامعتبر است.',
                422
            );
        }

        return [
            $name,
            $slug,
            $description,
            $baseCost,
            $currency,
            $minDays,
            $maxDays,
            $status,
            $sortOrder,
        ];
    }
}
