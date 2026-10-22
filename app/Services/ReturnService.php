<?php

declare(strict_types=1);

final class ReturnService
{
    private ReturnRepository $repository;
    private OrderRepository $orderRepository;
    private OrderItemRepository $orderItemRepository;
    private PDO $pdo;

    private const ACTIVE_RETURN_STATUSES = [
        ReturnRequest::STATUS_REQUESTED,
        ReturnRequest::STATUS_APPROVED,
        ReturnRequest::STATUS_RECEIVED,
        ReturnRequest::STATUS_INSPECTED,
        ReturnRequest::STATUS_COMPLETED,
    ];

    private const TRANSITIONS = [
        ReturnRequest::STATUS_REQUESTED => [
            ReturnRequest::STATUS_APPROVED,
            ReturnRequest::STATUS_REJECTED,
            ReturnRequest::STATUS_CANCELLED,
        ],
        ReturnRequest::STATUS_APPROVED => [
            ReturnRequest::STATUS_RECEIVED,
            ReturnRequest::STATUS_CANCELLED,
        ],
        ReturnRequest::STATUS_RECEIVED => [
            ReturnRequest::STATUS_INSPECTED,
            ReturnRequest::STATUS_CANCELLED,
        ],
        ReturnRequest::STATUS_INSPECTED => [
            ReturnRequest::STATUS_COMPLETED,
            ReturnRequest::STATUS_CANCELLED,
        ],
        ReturnRequest::STATUS_REJECTED => [],
        ReturnRequest::STATUS_COMPLETED => [],
        ReturnRequest::STATUS_CANCELLED => [],
    ];

    public function __construct(
        ?ReturnRepository $repository = null,
        ?OrderRepository $orderRepository = null,
        ?OrderItemRepository $orderItemRepository = null,
        ?PDO $pdo = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();

        $this->repository =
            $repository
            ?? new ReturnRepository($this->pdo);

        $this->orderRepository =
            $orderRepository
            ?? new OrderRepository($this->pdo);

        $this->orderItemRepository =
            $orderItemRepository
            ?? new OrderItemRepository($this->pdo);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findByUserId(
        int $userId
    ): array {
        return $this->repository->findByUserId(
            $userId
        );
    }

    public function findByOrderId(
        int $orderId
    ): array {
        return $this->repository->findByOrderId(
            $orderId
        );
    }

    public function findItems(
        int $returnId
    ): array {
        return $this->repository->findItems(
            $returnId
        );
    }

    public function findStatusHistory(
        int $returnId
    ): array {
        return $this->repository->findStatusHistory(
            $returnId
        );
    }

    public function findById(
        int $id
    ): ?ReturnRequest {
        return $this->repository->findById($id);
    }

    public function createReturn(
        int $userId,
        int $orderId,
        string $returnNumber,
        array $items,
        ?string $reason = null,
        ?string $customerNote = null
    ): ReturnRequest {
        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'Invalid user id.'
            );
        }

        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        $returnNumber =
            trim($returnNumber);

        if ($returnNumber === '') {
            throw new InvalidArgumentException(
                'Return number is required.'
            );
        }

        if (mb_strlen($returnNumber) > 50) {
            throw new InvalidArgumentException(
                'Return number is too long.'
            );
        }

        if ($items === []) {
            throw new InvalidArgumentException(
                'Return must contain at least one item.'
            );
        }

        if (
            $reason !== null
            && mb_strlen($reason) > 500
        ) {
            throw new InvalidArgumentException(
                'Return reason is too long.'
            );
        }

        if (
            $customerNote !== null
            && mb_strlen($customerNote) > 1000
        ) {
            throw new InvalidArgumentException(
                'Customer note is too long.'
            );
        }

        if (
            $this->repository
            ->findByReturnNumber($returnNumber)
            !== null
        ) {
            throw new InvalidArgumentException(
                'Return number already exists.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $order =
                $this->orderRepository
                ->findByIdForUpdate($orderId);

            if ($order === null) {
                throw new RuntimeException(
                    'Order not found.'
                );
            }

            if ($order->userId !== $userId) {
                throw new RuntimeException(
                    'Order does not belong to this user.'
                );
            }

            $validatedItems = [];
            $requestedQuantities = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    throw new InvalidArgumentException(
                        'Invalid return item.'
                    );
                }

                $orderItemId =
                    (int)($item['orderItemId'] ?? 0);

                $quantity =
                    (int)($item['quantity'] ?? 0);

                if ($orderItemId <= 0) {
                    throw new InvalidArgumentException(
                        'Invalid order item id.'
                    );
                }

                if ($quantity <= 0) {
                    throw new InvalidArgumentException(
                        'Return quantity must be greater than zero.'
                    );
                }

                $orderItem =
                    $this->orderItemRepository
                    ->findById($orderItemId);

                if ($orderItem === null) {
                    throw new RuntimeException(
                        'Order item not found.'
                    );
                }

                if ($orderItem->orderId !== $orderId) {
                    throw new RuntimeException(
                        'Order item does not belong to the order.'
                    );
                }

                $requestedQuantities[$orderItemId] =
                    ($requestedQuantities[$orderItemId] ?? 0)
                    + $quantity;

                if (
                    $requestedQuantities[$orderItemId]
                    > $orderItem->quantity
                ) {
                    throw new InvalidArgumentException(
                        'Return quantity exceeds ordered quantity.'
                    );
                }

                $itemReason =
                    $item['reason'] ?? null;

                $conditionNote =
                    $item['conditionNote'] ?? null;

                if (
                    $itemReason !== null
                    && mb_strlen((string)$itemReason) > 500
                ) {
                    throw new InvalidArgumentException(
                        'Return item reason is too long.'
                    );
                }

                if (
                    $conditionNote !== null
                    && mb_strlen((string)$conditionNote) > 1000
                ) {
                    throw new InvalidArgumentException(
                        'Condition note is too long.'
                    );
                }

                $validatedItems[] = [
                    'orderItemId' => $orderItemId,
                    'quantity' => $quantity,
                    'reason' =>
                    $itemReason !== null
                        ? (string)$itemReason
                        : null,
                    'conditionNote' =>
                    $conditionNote !== null
                        ? (string)$conditionNote
                        : null,
                ];
            }

            $existingReturns =
                $this->repository
                ->findByOrderId($orderId);

            $alreadyReturned = [];

            foreach ($existingReturns as $existingReturn) {
                if (
                    !in_array(
                        $existingReturn->status,
                        self::ACTIVE_RETURN_STATUSES,
                        true
                    )
                ) {
                    continue;
                }

                $existingItems =
                    $this->repository
                    ->findItems(
                        $existingReturn->id
                    );

                foreach ($existingItems as $existingItem) {
                    $alreadyReturned[$existingItem->orderItemId] =
                        (
                            $alreadyReturned[$existingItem->orderItemId] ?? 0
                        )
                        + $existingItem->quantity;
                }
            }

            foreach ($validatedItems as $validatedItem) {
                $orderItem =
                    $this->orderItemRepository
                    ->findById(
                        $validatedItem['orderItemId']
                    );

                if ($orderItem === null) {
                    throw new RuntimeException(
                        'Order item not found.'
                    );
                }

                $existingQuantity =
                    $alreadyReturned[$validatedItem['orderItemId']] ?? 0;

                $newQuantity =
                    $existingQuantity
                    + $validatedItem['quantity'];

                if ($newQuantity > $orderItem->quantity) {
                    throw new InvalidArgumentException(
                        'Return quantity exceeds remaining returnable quantity.'
                    );
                }
            }

            $return =
                $this->repository->create(
                    $orderId,
                    $userId,
                    $returnNumber,
                    ReturnRequest::STATUS_REQUESTED,
                    $reason,
                    $customerNote,
                    null
                );

            foreach ($validatedItems as $validatedItem) {
                $this->repository->createItem(
                    $return->id,
                    $validatedItem['orderItemId'],
                    $validatedItem['quantity'],
                    $validatedItem['reason'],
                    $validatedItem['conditionNote']
                );
            }

            $this->repository->createStatusHistory(
                $return->id,
                null,
                ReturnRequest::STATUS_REQUESTED,
                null,
                null
            );

            $this->pdo->commit();

            return $return;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function changeStatus(
        int $returnId,
        string $toStatus,
        ?int $changedByAdminId = null,
        ?string $note = null
    ): ReturnRequest {
        if ($returnId <= 0) {
            throw new InvalidArgumentException(
                'Invalid return id.'
            );
        }

        $toStatus =
            trim($toStatus);

        if (
            !array_key_exists(
                $toStatus,
                self::TRANSITIONS
            )
            && !in_array(
                $toStatus,
                [
                    ReturnRequest::STATUS_REQUESTED,
                    ReturnRequest::STATUS_APPROVED,
                    ReturnRequest::STATUS_REJECTED,
                    ReturnRequest::STATUS_RECEIVED,
                    ReturnRequest::STATUS_INSPECTED,
                    ReturnRequest::STATUS_COMPLETED,
                    ReturnRequest::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid return status.'
            );
        }

        if (
            $changedByAdminId !== null
            && $changedByAdminId <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid admin id.'
            );
        }

        if (
            $note !== null
            && mb_strlen($note) > 1000
        ) {
            throw new InvalidArgumentException(
                'Return status note is too long.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $return =
                $this->repository
                ->findById($returnId);

            if ($return === null) {
                throw new RuntimeException(
                    'Return request not found.'
                );
            }

            $fromStatus =
                $return->status;

            if ($fromStatus === $toStatus) {
                throw new InvalidArgumentException(
                    'Return status has not changed.'
                );
            }

            $allowed =
                self::TRANSITIONS[$fromStatus]
                ?? [];

            if (
                !in_array(
                    $toStatus,
                    $allowed,
                    true
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid return status transition.'
                );
            }

            $stmt = $this->pdo->prepare(
                'UPDATE returns
                 SET status = :status,
                     approved_at =
                         CASE
                             WHEN :status_approved = 1
                             THEN COALESCE(
                                 approved_at,
                                 CURRENT_TIMESTAMP
                             )
                             ELSE approved_at
                         END,
                     received_at =
                         CASE
                             WHEN :status_received = 1
                             THEN COALESCE(
                                 received_at,
                                 CURRENT_TIMESTAMP
                             )
                             ELSE received_at
                         END,
                     completed_at =
                         CASE
                             WHEN :status_completed = 1
                             THEN COALESCE(
                                 completed_at,
                                 CURRENT_TIMESTAMP
                             )
                             ELSE completed_at
                         END,
                     cancelled_at =
                         CASE
                             WHEN :status_cancelled = 1
                             THEN COALESCE(
                                 cancelled_at,
                                 CURRENT_TIMESTAMP
                             )
                             ELSE cancelled_at
                         END
                 WHERE id = :id
                   AND status = :from_status
                 LIMIT 1'
            );

            $stmt->execute([
                'status' =>
                $toStatus,

                'status_approved' =>
                $toStatus === ReturnRequest::STATUS_APPROVED
                    ? 1
                    : 0,

                'status_received' =>
                $toStatus === ReturnRequest::STATUS_RECEIVED
                    ? 1
                    : 0,

                'status_completed' =>
                $toStatus === ReturnRequest::STATUS_COMPLETED
                    ? 1
                    : 0,

                'status_cancelled' =>
                $toStatus === ReturnRequest::STATUS_CANCELLED
                    ? 1
                    : 0,

                'id' =>
                $returnId,

                'from_status' =>
                $fromStatus,
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException(
                    'Return status could not be changed.'
                );
            }

            $this->repository->createStatusHistory(
                $returnId,
                $fromStatus,
                $toStatus,
                $changedByAdminId,
                $note
            );

            $updated =
                $this->repository
                ->findById($returnId);

            if ($updated === null) {
                throw new RuntimeException(
                    'Updated return request could not be loaded.'
                );
            }

            $this->pdo->commit();

            return $updated;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
