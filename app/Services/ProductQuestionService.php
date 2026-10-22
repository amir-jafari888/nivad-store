<?php

declare(strict_types=1);

final class ProductQuestionService
{
    private ProductQuestionRepository $repository;

    private ProductQuestionValidator $validator;

    public function __construct(
        ?ProductQuestionRepository $repository = null,
        ?ProductQuestionValidator $validator = null
    ) {
        $this->repository =
            $repository ??
            new ProductQuestionRepository();

        $this->validator =
            $validator ??
            new ProductQuestionValidator();
    }

    public function findById(
        int $questionId
    ): ?ProductQuestion {
        return $this->repository->findById($questionId);
    }

    public function findByProductId(
        int $productId
    ): array {
        return $this->repository->findByProductId($productId);
    }

    public function findByUserId(
        int $userId
    ): array {
        return $this->repository->findByUserId($userId);
    }

    public function findByStatus(
        string $status
    ): array {
        $errors =
            ProductQuestionValidator::validateStatus(
                $status
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                $errors['status']
            );
        }

        return $this->repository->findByStatus($status);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function validate(
        array $data
    ): array {
        return $this->validator->validate($data);
    }

    public function create(
        int $productId,
        int $userId,
        string $question,
        string $status = ProductQuestion::STATUS_PENDING,
        ?string $adminNote = null
    ): ProductQuestion {
        $data = [
            'productId' => $productId,
            'userId' => $userId,
            'question' => $question,
            'status' => $status,
            'adminNote' => $adminNote,
        ];

        $errors = $this->validator->validate($data);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        return $this->repository->create(
            $productId,
            $userId,
            trim($question),
            $status,
            $adminNote
        );
    }

    public function updateStatus(
        int $questionId,
        string $status
    ): ?ProductQuestion {
        $errors =
            ProductQuestionValidator::validateStatus(
                $status
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                $errors['status']
            );
        }

        return $this->repository->updateStatus(
            $questionId,
            $status
        );
    }

    public function updateAdminNote(
        int $questionId,
        ?string $adminNote
    ): ?ProductQuestion {
        $errors =
            ProductQuestionValidator::validateAdminNote(
                $adminNote
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                $errors['adminNote']
            );
        }

        return $this->repository->updateAdminNote(
            $questionId,
            $adminNote
        );
    }

    public function moderate(
        int $questionId,
        string $status,
        ?string $adminNote = null
    ): ?ProductQuestion {
        $statusErrors =
            ProductQuestionValidator::validateStatus(
                $status
            );

        if ($statusErrors !== []) {
            throw new InvalidArgumentException(
                $statusErrors['status']
            );
        }

        $noteErrors =
            ProductQuestionValidator::validateAdminNote(
                $adminNote
            );

        if ($noteErrors !== []) {
            throw new InvalidArgumentException(
                $noteErrors['adminNote']
            );
        }

        $question =
            $this->repository->findById($questionId);

        if (!$question instanceof ProductQuestion) {
            return null;
        }

        if ($question->status === $status) {
            throw new InvalidArgumentException(
                'Question already has this status.'
            );
        }

        $updated =
            $this->repository->updateStatus(
                $questionId,
                $status
            );

        if (
            $updated instanceof ProductQuestion
            && $adminNote !== null
        ) {
            $updated =
                $this->repository->updateAdminNote(
                    $questionId,
                    $adminNote
                );
        }

        return $updated;
    }

    public function approve(
        int $questionId,
        ?string $adminNote = null
    ): ?ProductQuestion {
        return $this->moderate(
            $questionId,
            ProductQuestion::STATUS_APPROVED,
            $adminNote
        );
    }

    public function reject(
        int $questionId,
        ?string $adminNote = null
    ): ?ProductQuestion {
        return $this->moderate(
            $questionId,
            ProductQuestion::STATUS_REJECTED,
            $adminNote
        );
    }

    public function hide(
        int $questionId,
        ?string $adminNote = null
    ): ?ProductQuestion {
        return $this->moderate(
            $questionId,
            ProductQuestion::STATUS_HIDDEN,
            $adminNote
        );
    }

    public function markPending(
        int $questionId,
        ?string $adminNote = null
    ): ?ProductQuestion {
        return $this->moderate(
            $questionId,
            ProductQuestion::STATUS_PENDING,
            $adminNote
        );
    }

    public function canModerate(
        int $questionId
    ): bool {
        return $this->repository->findById(
            $questionId
        ) instanceof ProductQuestion;
    }

    public function findPublicByProductId(int $productId): array
    {
        return array_values(
            array_filter(
                $this->repository->findByProductId($productId),
                static fn(ProductQuestion $question): bool =>
                $question->status === ProductQuestion::STATUS_APPROVED
            )
        );
    }
}
