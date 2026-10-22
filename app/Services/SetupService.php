<?php

declare(strict_types=1);

final class SetupService
{
    private SetupRepository $repository;

    public function __construct(
        ?SetupRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new SetupRepository();
    }

    public function findDeviceById(
        int $deviceId
    ): ?SetupDevice {
        return $this->repository->findDeviceById(
            $deviceId
        );
    }

    public function findDeviceBySlug(
        string $slug
    ): ?SetupDevice {
        return $this->repository->findDeviceBySlug(
            $slug
        );
    }

    public function findActiveDevices(): array
    {
        return $this->repository->findActiveDevices();
    }

    public function findCompatibleAccessories(
        int $deviceId,
        ?string $relationType = null
    ): array {
        return $this->repository
            ->findCompatibleAccessories(
                $deviceId,
                $relationType
            );
    }

    public function findCompatibleProducts(
        int $deviceId,
        ?string $relationType = null
    ): array {
        return $this->repository
            ->findCompatibleProducts(
                $deviceId,
                $relationType
            );
    }

    public function calculateTotal(
        array $prices
    ): string {
        $total = '0.0000';

        foreach ($prices as $price) {
            $value = trim((string)$price);

            if (
                $value === '' ||
                !preg_match(
                    '/^\d+(?:\.\d{1,4})?$/',
                    $value
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid setup item price.'
                );
            }

            $parts = explode('.', $value, 2);

            $integer = $parts[0];
            $fraction = $parts[1] ?? '';

            $fraction = str_pad(
                $fraction,
                4,
                '0'
            );

            $totalParts = explode('.', $total);

            $integerTotal = $totalParts[0];
            $fractionTotal = $totalParts[1];

            $fractionSum =
                (int)$fractionTotal +
                (int)$fraction;

            $carry = intdiv(
                $fractionSum,
                10000
            );

            $fractionSum %= 10000;

            $integerTotal =
                (string)(
                    (int)$integerTotal +
                    (int)$integer +
                    $carry
                );

            $total =
                $integerTotal .
                '.' .
                str_pad(
                    (string)$fractionSum,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        }

        return $total;
    }

    public function findComplementCandidatesByDevices(
        array $deviceIds,
        int $excludeProductId
    ): array {
        return $this->repository
            ->findComplementCandidatesByDevices(
                $deviceIds,
                $excludeProductId
            );
    }
}
