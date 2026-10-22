<?php

declare(strict_types=1);

final class OrderStatusHistoryService
{
    private OrderStatusHistoryRepository $repository;
    private OrderStatusHistoryValidator $validator;

    public function __construct(
        ?OrderStatusHistoryRepository $repository = null,
        ?OrderStatusHistoryValidator $validator = null
    ) {
        $this->repository =
            $repository
            ?? new OrderStatusHistoryRepository();

        $this->validator =
            $validator
            ?? new OrderStatusHistoryValidator();
    }

    public function findById(
        int $id
    ): ?OrderStatusHistory {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'شناسه تاریخچه وضعیت نامعتبر است.'
            );
        }

        return $this->repository->findById($id);
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'شناسه سفارش نامعتبر است.'
            );
        }

        return $this->repository->findAllByOrder(
            $orderId
        );
    }

    public function create(
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByUserId = null,
        ?string $note = null
    ): OrderStatusHistory {
        $errors = $this->validator->validate(
            $orderId,
            $fromStatus,
            $toStatus,
            $changedByUserId,
            $note
        );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                'اطلاعات تاریخچه وضعیت سفارش نامعتبر است.'
            );
        }

        return $this->repository->create(
            $orderId,
            $fromStatus,
            $toStatus,
            $changedByUserId,
            $note
        );
    }
}
