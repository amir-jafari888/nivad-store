<?php

declare(strict_types=1);

final class NotificationService
{
    public const TYPE_ORDER_STATUS_CHANGED =
    'order_status_changed';

    public const TYPE_PAYMENT_SUCCESS =
    'payment_success';

    public const TYPE_PAYMENT_FAILED =
    'payment_failed';

    public const TYPE_REVIEW_STATUS_CHANGED =
    'review_status_changed';

    public const TYPE_PRODUCT_ANSWER_CREATED =
    'product_answer_created';

    private NotificationRepository $repository;

    public function __construct(
        ?NotificationRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new NotificationRepository();
    }

    public function findById(
        int $notificationId
    ): ?Notification {
        return $this->repository->findById(
            $notificationId
        );
    }

    public function findByUserId(
        int $userId
    ): array {
        return $this->repository->findByUserId(
            $userId
        );
    }

    public function findUnreadByUserId(
        int $userId
    ): array {
        return $this->repository->findUnreadByUserId(
            $userId
        );
    }

    public function countUnreadByUserId(
        int $userId
    ): int {
        return $this->repository->countUnreadByUserId(
            $userId
        );
    }

    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $data = null
    ): Notification {
        $userId = (int) $userId;
        $type = trim($type);
        $title = trim($title);
        $message = trim($message);

        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Notification user ID must be greater than zero.'
            );
        }

        if ($type === '') {
            throw new InvalidArgumentException(
                'Notification type is required.'
            );
        }

        if (mb_strlen($type) > 100) {
            throw new InvalidArgumentException(
                'Notification type cannot exceed 100 characters.'
            );
        }

        if ($title === '') {
            throw new InvalidArgumentException(
                'Notification title is required.'
            );
        }

        if (mb_strlen($title) > 255) {
            throw new InvalidArgumentException(
                'Notification title cannot exceed 255 characters.'
            );
        }

        if ($message === '') {
            throw new InvalidArgumentException(
                'Notification message is required.'
            );
        }

        if ($data !== null) {
            json_decode($data);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException(
                    'Notification data must contain valid JSON.'
                );
            }
        }

        return $this->repository->create(
            $userId,
            $type,
            $title,
            $message,
            $data
        );
    }

    public function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): Notification {
        $encodedData = null;

        if ($data !== null) {
            try {
                $encodedData = json_encode(
                    $data,
                    JSON_THROW_ON_ERROR |
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                );
            } catch (JsonException $exception) {
                throw new InvalidArgumentException(
                    'Notification data could not be encoded.'
                );
            }
        }

        return $this->create(
            $userId,
            $type,
            $title,
            $message,
            $encodedData
        );
    }

    public function markAsRead(
        int $notificationId
    ): ?Notification {
        if ($notificationId < 1) {
            return null;
        }

        return $this->repository->markAsRead(
            $notificationId
        );
    }

    public function markAllAsRead(
        int $userId
    ): int {
        if ($userId < 1) {
            return 0;
        }

        return $this->repository->markAllAsRead(
            $userId
        );
    }
}
