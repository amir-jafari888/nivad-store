<?php

declare(strict_types=1);

final class Notification
{
    public int $id;
    public int $userId;
    public string $type;
    public string $title;
    public string $message;
    public ?string $data;
    public bool $isRead;
    public ?string $readAt;
    public string $createdAt;

    public function __construct(
        int $id,
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $data,
        bool $isRead,
        ?string $readAt,
        string $createdAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->isRead = $isRead;
        $this->readAt = $readAt;
        $this->createdAt = $createdAt;
    }
}
