<?php

declare(strict_types=1);

final class ProductQuestion
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIDDEN = 'hidden';

    public int $id;
    public int $productId;
    public int $userId;
    public string $question;
    public string $status;
    public ?string $adminNote;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $productId,
        int $userId,
        string $question,
        string $status = self::STATUS_PENDING,
        ?string $adminNote = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->userId = $userId;
        $this->question = $question;
        $this->status = $status;
        $this->adminNote = $adminNote;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
