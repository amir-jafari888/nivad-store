<?php

declare(strict_types=1);

final class Wishlist
{
    public int $id;
    public int $userId;
    public string $name;
    public bool $isDefault;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $userId,
        string $name = 'علاقه‌مندی‌ها',
        bool $isDefault = true,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
