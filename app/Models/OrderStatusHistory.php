<?php

declare(strict_types=1);

final class OrderStatusHistory
{
    public int $id;
    public int $orderId;
    public ?string $fromStatus;
    public string $toStatus;
    public ?int $changedByUserId;
    public ?string $note;
    public ?string $createdAt;

    public function __construct(
        int $id,
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByUserId = null,
        ?string $note = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
        $this->changedByUserId = $changedByUserId;
        $this->note = $note;
        $this->createdAt = $createdAt;
    }
}
