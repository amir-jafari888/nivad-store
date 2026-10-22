<?php

declare(strict_types=1);

final class ReviewMedia
{
    public const MEDIA_TYPE_IMAGE = 'image';

    public const MEDIA_TYPE_VIDEO = 'video';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIDDEN = 'hidden';

    public int $id;
    public int $reviewId;
    public string $mediaType;
    public string $filePath;
    public ?string $thumbnailPath;
    public ?string $altText;
    public int $sortOrder;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $reviewId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath = null,
        ?string $altText = null,
        int $sortOrder = 0,
        string $status = self::STATUS_PENDING,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->reviewId = $reviewId;
        $this->mediaType = $mediaType;
        $this->filePath = $filePath;
        $this->thumbnailPath = $thumbnailPath;
        $this->altText = $altText;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
