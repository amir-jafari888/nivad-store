<?php
// app/Models/Slide.php

declare(strict_types=1);

final class Slide
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const TARGET_SELF = '_self';
    public const TARGET_BLANK = '_blank';

    public int $id;
    public int $sliderId;
    public ?string $title;
    public ?string $subtitle;
    public string $imagePath;
    public ?string $mobileImagePath;
    public ?string $linkUrl;
    public string $linkTarget;
    public string $status;
    public int $sortOrder;
    public ?string $startsAt;
    public ?string $endsAt;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $sliderId,
        ?string $title,
        ?string $subtitle,
        string $imagePath,
        ?string $mobileImagePath,
        ?string $linkUrl,
        string $linkTarget,
        string $status,
        int $sortOrder,
        ?string $startsAt,
        ?string $endsAt,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $deletedAt
    ) {
        $this->id = $id;
        $this->sliderId = $sliderId;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->imagePath = $imagePath;
        $this->mobileImagePath = $mobileImagePath;
        $this->linkUrl = $linkUrl;
        $this->linkTarget = $linkTarget;
        $this->status = $status;
        $this->sortOrder = $sortOrder;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
