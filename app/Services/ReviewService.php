<?php

declare(strict_types=1);

final class ReviewService
{
    private ReviewRepository $repository;

    private ReviewValidator $validator;

    private PDO $pdo;

    private NotificationService $notificationService;

    public function __construct(
        ?ReviewRepository $repository = null,
        ?ReviewValidator $validator = null,
        ?PDO $pdo = null,
        ?NotificationService $notificationService = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();

        $this->repository =
            $repository ??
            new ReviewRepository($this->pdo);

        $this->validator =
            $validator ??
            new ReviewValidator();

        $this->notificationService =
            $notificationService ??
            new NotificationService(
                new NotificationRepository($this->pdo)
            );
    }

    public function findById(
        int $reviewId
    ): ?Review {
        return $this->repository->findById(
            $reviewId
        );
    }

    public function findByProductId(
        int $productId
    ): array {
        return $this->repository->findByProductId(
            $productId
        );
    }

    public function findByUserId(
        int $userId
    ): array {
        return $this->repository->findByUserId(
            $userId
        );
    }

    public function findByOrderItemId(
        int $orderItemId
    ): ?Review {
        return $this->repository->findByOrderItemId(
            $orderItemId
        );
    }

    public function findByStatus(
        string $status
    ): array {
        if (
            ReviewValidator::validateStatus(
                $status
            ) !== []
        ) {
            return [];
        }

        return $this->repository->findByStatus(
            $status
        );
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function validate(
        array $data
    ): array {
        return $this->validator->validate(
            $data
        );
    }

    public function create(
        int $productId,
        int $userId,
        ?int $orderItemId,
        int $rating,
        ?string $title,
        string $body,
        string $status = Review::STATUS_PENDING,
        bool $isVerifiedPurchase = false,
        ?string $adminNote = null
    ): ?Review {
        $data = [
            'productId' => $productId,
            'userId' => $userId,
            'orderItemId' => $orderItemId,
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'status' => $status,
            'isVerifiedPurchase' =>
            $isVerifiedPurchase,
            'adminNote' => $adminNote,
        ];

        $errors = $this->validate(
            $data
        );

        if ($errors !== []) {
            return null;
        }

        return $this->repository->create(
            $productId,
            $userId,
            $orderItemId,
            $rating,
            $title,
            $body,
            $status,
            $isVerifiedPurchase,
            $adminNote
        );
    }

    public function updateStatus(
        int $reviewId,
        string $status
    ): ?Review {
        if ($reviewId < 1) {
            return null;
        }

        if (
            ReviewValidator::validateStatus(
                $status
            ) !== []
        ) {
            return null;
        }

        return $this->repository->updateStatus(
            $reviewId,
            $status
        );
    }

    public function updateAdminNote(
        int $reviewId,
        ?string $adminNote
    ): ?Review {
        if ($reviewId < 1) {
            return null;
        }

        if (
            ReviewValidator::validateAdminNote(
                $adminNote
            ) !== []
        ) {
            return null;
        }

        return $this->repository->updateAdminNote(
            $reviewId,
            $adminNote
        );
    }

    public function moderate(
        int $reviewId,
        string $status,
        ?string $adminNote = null
    ): ?Review {
        if ($reviewId < 1) {
            return null;
        }

        if (
            ReviewValidator::validateStatus(
                $status
            ) !== []
        ) {
            return null;
        }

        if (
            ReviewValidator::validateAdminNote(
                $adminNote
            ) !== []
        ) {
            return null;
        }

        $review =
            $this->repository->findById(
                $reviewId
            );

        if ($review === null) {
            return null;
        }

        if ($review->status === $status) {
            return null;
        }

        $this->pdo->beginTransaction();

        try {
            $updated =
                $this->repository->updateStatus(
                    $reviewId,
                    $status
                );

            if ($updated === null) {
                throw new RuntimeException(
                    'Review status could not be updated.'
                );
            }

            if ($adminNote !== null) {
                $updated =
                    $this->repository->updateAdminNote(
                        $reviewId,
                        $adminNote
                    );

                if ($updated === null) {
                    throw new RuntimeException(
                        'Review admin note could not be updated.'
                    );
                }
            }

            $this->notificationService->createForUser(
                $updated->userId,
                NotificationService::TYPE_REVIEW_STATUS_CHANGED,
                'تغییر وضعیت دیدگاه',
                'وضعیت دیدگاه شما تغییر کرد.',
                [
                    'review_id' =>
                    $updated->id,
                    'product_id' =>
                    $updated->productId,
                    'from_status' =>
                    $review->status,
                    'to_status' =>
                    $updated->status,
                ]
            );

            $this->pdo->commit();

            return $updated;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function approve(
        int $reviewId,
        ?string $adminNote = null
    ): ?Review {
        return $this->moderate(
            $reviewId,
            Review::STATUS_APPROVED,
            $adminNote
        );
    }

    public function reject(
        int $reviewId,
        ?string $adminNote = null
    ): ?Review {
        return $this->moderate(
            $reviewId,
            Review::STATUS_REJECTED,
            $adminNote
        );
    }

    public function hide(
        int $reviewId,
        ?string $adminNote = null
    ): ?Review {
        return $this->moderate(
            $reviewId,
            Review::STATUS_HIDDEN,
            $adminNote
        );
    }

    public function markPending(
        int $reviewId,
        ?string $adminNote = null
    ): ?Review {
        return $this->moderate(
            $reviewId,
            Review::STATUS_PENDING,
            $adminNote
        );
    }

    public function canModerate(
        int $reviewId,
        string $status
    ): bool {
        if ($reviewId < 1) {
            return false;
        }

        if (
            ReviewValidator::validateStatus(
                $status
            ) !== []
        ) {
            return false;
        }

        $review =
            $this->repository->findById(
                $reviewId
            );

        if ($review === null) {
            return false;
        }

        return $review->status !== $status;
    }

    public function findPublicByProductId(int $productId): array
    {
        return array_values(
            array_filter(
                $this->repository->findByProductId($productId),
                static fn(Review $review): bool =>
                $review->status === Review::STATUS_APPROVED
            )
        );
    }

    public function getProductRatingSummary(int $productId): array
    {
        $reviews = $this->findPublicByProductId($productId);
        $count = count($reviews);

        if ($count === 0) {
            return [
                'average' => 0,
                'count' => 0,
            ];
        }

        $total = array_reduce(
            $reviews,
            static fn(int $sum, Review $review): int => $sum + $review->rating,
            0
        );

        return [
            'average' => round($total / $count, 1),
            'count' => $count,
        ];
    }
}
