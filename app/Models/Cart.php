<?php

declare(strict_types=1);

final class Cart
{
    public int $id;
    public ?int $userId;
    public ?string $sessionTokenHash;
    public string $status;
    public string $currency;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $expiresAt;

    public function __construct(
        int $id,
        ?int $userId = null,
        ?string $sessionTokenHash = null,
        string $status = 'active',
        string $currency = 'IRR',
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $expiresAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionTokenHash = $sessionTokenHash;
        $this->status = $status;
        $this->currency = $currency;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->expiresAt = $expiresAt;
    }
}
