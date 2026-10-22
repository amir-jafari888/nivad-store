<?php

declare(strict_types=1);

final class ReturnItem
{
    public int $id;
    public int $returnId;
    public int $orderItemId;
    public int $quantity;
    public ?string $reason;
    public ?string $conditionNote;
    public string $createdAt;

    public function __construct(
        int $id,
        int $returnId,
        int $orderItemId,
        int $quantity,
        ?string $reason,
        ?string $conditionNote,
        string $createdAt
    ) {
        $this->id = $id;
        $this->returnId = $returnId;
        $this->orderItemId = $orderItemId;
        $this->quantity = $quantity;
        $this->reason = $reason;
        $this->conditionNote = $conditionNote;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['return_id'],
            (int)$data['order_item_id'],
            (int)$data['quantity'],
            $data['reason'] !== null
                ? (string)$data['reason']
                : null,
            $data['condition_note'] !== null
                ? (string)$data['condition_note']
                : null,
            (string)$data['created_at']
        );
    }
}
