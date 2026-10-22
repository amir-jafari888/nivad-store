<?php

declare(strict_types=1);

final class Review
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIDDEN = 'hidden';

    public int $id;
    public int $productId;
    public int $userId;
    public ?int $orderItemId;
    public int $rating;
    public ?string $title;
    public string $body;
    public string $status;
    public bool $isVerifiedPurchase;
    public ?string $adminNote;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $productId,
        int $userId,
        ?int $orderItemId,
        int $rating,
        ?string $title,
        string $body,
        string $status,
        bool $isVerifiedPurchase,
        ?string $adminNote,
        ?string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->userId = $userId;
        $this->orderItemId = $orderItemId;
        $this->rating = $rating;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
        $this->isVerifiedPurchase = $isVerifiedPurchase;
        $this->adminNote = $adminNote;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
