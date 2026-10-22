<?php

declare(strict_types=1);

final class ShippingService
{
    private ShippingRepository $repository;
    private ShippingService $shippingService;

    public function __construct(
        ?ShippingRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new ShippingRepository();

        $this->shippingService =
            new ShippingService(
                new ShippingRepository(
                    
                )
            );
    }

    public function findShipmentById(
        int $id
    ): ?Shipment {
        return $this->repository
            ->findShipmentById($id);
    }

    public function findShipmentsByOrderId(
        int $orderId
    ): array {
        return $this->repository
            ->findShipmentsByOrderId($orderId);
    }

    public function findShipmentItems(
        int $shipmentId
    ): array {
        return $this->repository
            ->findShipmentItems($shipmentId);
    }

    public function findShippingMethodById(
        int $id
    ): ?ShippingMethod {
        return $this->repository
            ->findShippingMethodById($id);
    }

    public function findActiveShippingMethods(): array
    {
        return $this->repository
            ->findActiveShippingMethods();
    }

    public function findAllShippingZones(): array
    {
        return $this->repository
            ->findAllShippingZones();
    }

    public function createShippingZone(
        string $name,
        string $slug,
        ?string $description,
        string $status = ShippingZone::STATUS_ACTIVE,
        int $sortOrder = 0
    ): ShippingZone {
        return $this->repository->createShippingZone(
            $name,
            $slug,
            $description,
            $status,
            $sortOrder
        );
    }

    public function updateShippingZoneStatus(
        int $id,
        string $status
    ): ?ShippingZone {
        return $this->repository
            ->updateShippingZoneStatus(
                $id,
                $status
            );
    }

    public function findAllShipments(): array
    {
        return $this->repository
            ->findAllShipments();
    }

    public function updateShipmentStatus(
        int $id,
        string $status
    ): ?Shipment {
        return $this->repository
            ->updateShipmentStatus(
                $id,
                $status
            );
    }

    public function findShippingZoneById(
        int $id
    ): ?ShippingZone {
        return $this->repository
            ->findShippingZoneById($id);
    }

    public function findZoneLocations(
        int $shippingZoneId
    ): array {
        return $this->repository
            ->findZoneLocations(
                $shippingZoneId
            );
    }

    public function findShippingMethodZone(
        int $shippingMethodId,
        int $shippingZoneId
    ): ?ShippingMethodZone {
        return $this->repository
            ->findShippingMethodZone(
                $shippingMethodId,
                $shippingZoneId
            );
    }

    public function findZoneByLocation(
        ?string $province,
        ?string $city,
        ?string $postalPrefix = null
    ): ?ShippingZone {
        return $this->repository
            ->findZoneByLocation(
                $province,
                $city,
                $postalPrefix
            );
    }

    public function createShipment(
        int $orderId,
        int $shippingMethodId,
        ?string $trackingNumber,
        string $status,
        string $shippingCost,
        string $currency
    ): Shipment {
        return $this->repository->createShipment(
            $orderId,
            $shippingMethodId,
            $trackingNumber,
            $status,
            $shippingCost,
            $currency
        );
    }

    public function createShipmentItem(
        int $shipmentId,
        int $orderItemId,
        int $quantity
    ): ShipmentItem {
        return $this->repository->createShipmentItem(
            $shipmentId,
            $orderItemId,
            $quantity
        );
    }

    public function findAllShippingMethods(): array
    {
        return $this->repository
            ->findAllShippingMethods();
    }

    public function createShippingMethod(
        string $name,
        string $slug,
        ?string $description,
        string $baseCost,
        string $currency,
        ?int $minDays,
        ?int $maxDays,
        string $status,
        int $sortOrder
    ): ?ShippingMethod {
        return $this->repository
            ->createShippingMethod(
                $name,
                $slug,
                $description,
                $baseCost,
                $currency,
                $minDays,
                $maxDays,
                $status,
                $sortOrder
            );
    }

    public function updateShippingMethod(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $baseCost,
        string $currency,
        ?int $minDays,
        ?int $maxDays,
        string $status,
        int $sortOrder
    ): ?ShippingMethod {
        return $this->repository
            ->updateShippingMethod(
                $id,
                $name,
                $slug,
                $description,
                $baseCost,
                $currency,
                $minDays,
                $maxDays,
                $status,
                $sortOrder
            );
    }

    public function deleteShippingMethod(
        int $id
    ): bool {
        return $this->repository
            ->deleteShippingMethod($id);
    }
}
