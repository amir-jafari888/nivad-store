<?php

declare(strict_types=1);

final class ProductAnswer
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIDDEN = 'hidden';

    public int $id;
    public int $questionId;
    public ?int $adminUserId;
    public string $answer;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $questionId,
        ?int $adminUserId,
        string $answer,
        string $status = self::STATUS_PENDING,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->questionId = $questionId;
        $this->adminUserId = $adminUserId;
        $this->answer = $answer;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
