<?php

declare(strict_types=1);

final class InventoryMovement
{
    public int $id;
    public int $variantId;
    public string $movementType;
    public int $quantity;
    public ?string $referenceType;
    public ?int $referenceId;
    public ?string $note;
    public ?int $createdBy;
    public ?string $createdAt;

    public function __construct(
        int $id,
        int $variantId,
        string $movementType,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $createdBy = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->variantId = $variantId;
        $this->movementType = $movementType;
        $this->quantity = $quantity;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
        $this->note = $note;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
    }
}
