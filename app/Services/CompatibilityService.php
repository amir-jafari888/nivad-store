<?php

declare(strict_types=1);

final class CompatibilityService
{
    private CompatibilityRepository $repository;

    public function __construct(
        ?CompatibilityRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new CompatibilityRepository();
    }

    public function findDeviceById(
        int $deviceId
    ): ?CompatibilityDevice {
        return $this->repository->findDeviceById(
            $deviceId
        );
    }

    public function findDeviceBySlug(
        string $slug
    ): ?CompatibilityDevice {
        return $this->repository->findDeviceBySlug(
            $slug
        );
    }

    public function findActiveDevices(): array
    {
        return $this->repository->findActiveDevices();
    }

    public function findCompatibleProducts(
        int $deviceId,
        ?string $compatibilityType = null
    ): array {
        return $this->repository->findCompatibleProducts(
            $deviceId,
            $compatibilityType
        );
    }

    public function findMapping(
        int $productId,
        int $deviceId
    ): ?ProductCompatibility {
        return $this->repository->findMapping(
            $productId,
            $deviceId
        );
    }

    public function createMapping(
        int $productId,
        int $deviceId,
        string $compatibilityType,
        ?string $note,
        int $sortOrder = 0
    ): ProductCompatibility {
        return $this->repository->createMapping(
            $productId,
            $deviceId,
            $compatibilityType,
            $note,
            $sortOrder
        );
    }

    public function updateMapping(
        int $productId,
        int $deviceId,
        string $compatibilityType,
        ?string $note,
        int $sortOrder
    ): ?ProductCompatibility {
        return $this->repository->updateMapping(
            $productId,
            $deviceId,
            $compatibilityType,
            $note,
            $sortOrder
        );
    }

    public function deleteMapping(
        int $productId,
        int $deviceId
    ): bool {
        return $this->repository->deleteMapping(
            $productId,
            $deviceId
        );
    }

    public function getAdminSummary(): array
    {
        return [
            'activeDevices' =>
            $this->repository->countActiveDevices(),
            'mappings' =>
            $this->repository->countMappings(),
        ];
    }

    public function findAllMappings(): array
    {
        return $this->repository->findAllMappings();
    }

    public function findAllDevices(): array
    {
        return $this->repository->findAllDevices();
    }

    public function createDevice(
        string $brandName,
        string $name,
        ?string $model,
        string $deviceType,
        string $slug,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder
    ): ?CompatibilityDevice {
        return $this->repository->createDevice(
            $brandName,
            $name,
            $model,
            $deviceType,
            $slug,
            $description,
            $image,
            $status,
            $sortOrder
        );
    }

    public function updateDevice(
        int $id,
        string $brandName,
        string $name,
        ?string $model,
        string $deviceType,
        string $slug,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder
    ): ?CompatibilityDevice {
        return $this->repository->updateDevice(
            $id,
            $brandName,
            $name,
            $model,
            $deviceType,
            $slug,
            $description,
            $image,
            $status,
            $sortOrder
        );
    }

    public function deleteDevice(int $id): bool
    {
        return $this->repository->deleteDevice($id);
    }

    public function restoreDevice(int $id): ?CompatibilityDevice
    {
        return $this->repository->restoreDevice($id);
    }

    public function updateDeviceStatus(
        int $id,
        string $status
    ): ?CompatibilityDevice {
        return $this->repository->updateDeviceStatus(
            $id,
            $status
        );
    }

    public function findDevices(): array
    {
        return $this->repository->findActiveDevices();
    }

    public function findDevicesByProduct(
        int $productId
    ): array {
        return $this->repository->findDevicesByProduct(
            $productId
        );
    }

    public function findAllMappingsByProduct(
        int $productId
    ): array {
        return $this->repository->findAllMappingsByProduct(
            $productId
        );
    }
}
