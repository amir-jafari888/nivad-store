<?php

declare(strict_types=1);

final class ProductAnswerService
{
    private ProductAnswerRepository $repository;

    private ProductAnswerValidator $validator;

    private ProductQuestionRepository $questionRepository;

    private PDO $pdo;

    private NotificationService $notificationService;

    public function __construct(
        ?ProductAnswerRepository $repository = null,
        ?ProductAnswerValidator $validator = null,
        ?ProductQuestionRepository $questionRepository = null,
        ?PDO $pdo = null,
        ?NotificationService $notificationService = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();

        $this->repository =
            $repository ??
            new ProductAnswerRepository(
                $this->pdo
            );

        $this->validator =
            $validator ??
            new ProductAnswerValidator();

        $this->questionRepository =
            $questionRepository ??
            new ProductQuestionRepository(
                $this->pdo
            );

        $this->notificationService =
            $notificationService ??
            new NotificationService(
                new NotificationRepository(
                    $this->pdo
                )
            );
    }
    public function findById(
        int $answerId
    ): ?ProductAnswer {
        return $this->repository->findById($answerId);
    }

    public function findByQuestionId(
        int $questionId
    ): array {
        return $this->repository->findByQuestionId(
            $questionId
        );
    }

    public function findPublicByQuestionId(int $questionId): array
    {
        return array_values(
            array_filter(
                $this->repository->findByQuestionId($questionId),
                static fn(ProductAnswer $answer): bool =>
                $answer->status === ProductAnswer::STATUS_APPROVED
            )
        );
    }

    public function findByStatus(
        string $status
    ): array {
        $errors =
            ProductAnswerValidator::validateStatus(
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
        int $questionId,
        ?int $adminUserId,
        string $answer,
        string $status = ProductAnswer::STATUS_PENDING
    ): ProductAnswer {
        $data = [
            'questionId' => $questionId,
            'adminUserId' => $adminUserId,
            'answer' => $answer,
            'status' => $status,
        ];

        $errors =
            $this->validator->validate(
                $data
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', $errors)
            );
        }

        $question =
            $this->questionRepository->findById(
                $questionId
            );

        if (!$question instanceof ProductQuestion) {
            throw new RuntimeException(
                'Question not found.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $created =
                $this->repository->create(
                    $questionId,
                    $adminUserId,
                    trim($answer),
                    $status
                );

            if (!$created instanceof ProductAnswer) {
                throw new RuntimeException(
                    'Product answer could not be created.'
                );
            }

            $this->notificationService->createForUser(
                $question->userId,
                NotificationService::TYPE_PRODUCT_ANSWER_CREATED,
                'پاسخ جدید به پرسش شما',
                'برای پرسش شما یک پاسخ ثبت شد.',
                [
                    'question_id' =>
                    $question->id,
                    'answer_id' =>
                    $created->id,
                    'product_id' =>
                    $question->productId,
                    'status' =>
                    $created->status,
                ]
            );

            $this->pdo->commit();

            return $created;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateStatus(
        int $answerId,
        string $status
    ): ?ProductAnswer {
        $errors =
            ProductAnswerValidator::validateStatus(
                $status
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                $errors['status']
            );
        }

        return $this->repository->updateStatus(
            $answerId,
            $status
        );
    }

    public function moderate(
        int $answerId,
        string $status
    ): ?ProductAnswer {
        $statusErrors =
            ProductAnswerValidator::validateStatus(
                $status
            );

        if ($statusErrors !== []) {
            throw new InvalidArgumentException(
                $statusErrors['status']
            );
        }

        $answer =
            $this->repository->findById($answerId);

        if (!$answer instanceof ProductAnswer) {
            return null;
        }

        if ($answer->status === $status) {
            throw new InvalidArgumentException(
                'Answer already has this status.'
            );
        }

        return $this->repository->updateStatus(
            $answerId,
            $status
        );
    }

    public function approve(
        int $answerId
    ): ?ProductAnswer {
        return $this->moderate(
            $answerId,
            ProductAnswer::STATUS_APPROVED
        );
    }

    public function reject(
        int $answerId
    ): ?ProductAnswer {
        return $this->moderate(
            $answerId,
            ProductAnswer::STATUS_REJECTED
        );
    }

    public function hide(
        int $answerId
    ): ?ProductAnswer {
        return $this->moderate(
            $answerId,
            ProductAnswer::STATUS_HIDDEN
        );
    }

    public function markPending(
        int $answerId
    ): ?ProductAnswer {
        return $this->moderate(
            $answerId,
            ProductAnswer::STATUS_PENDING
        );
    }

    public function canModerate(
        int $answerId
    ): bool {
        return $this->repository->findById(
            $answerId
        ) instanceof ProductAnswer;
    }
}
