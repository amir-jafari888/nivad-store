<?php

declare(strict_types=1);

final class RefundService
{
    private RefundRepository $repository;
    private ReturnRepository $returnRepository;
    private PaymentRepository $paymentRepository;
    private PDO $pdo;

    private const TRANSITIONS = [
        Refund::STATUS_PENDING => [
            Refund::STATUS_PROCESSING,
            Refund::STATUS_CANCELLED,
        ],

        Refund::STATUS_PROCESSING => [
            Refund::STATUS_SUCCESSFUL,
            Refund::STATUS_FAILED,
            Refund::STATUS_CANCELLED,
        ],

        Refund::STATUS_SUCCESSFUL => [],
        Refund::STATUS_FAILED => [],
        Refund::STATUS_CANCELLED => [],
    ];

    public function __construct(
        ?RefundRepository $repository = null,
        ?ReturnRepository $returnRepository = null,
        ?PaymentRepository $paymentRepository = null,
        ?PDO $pdo = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();

        $this->repository =
            $repository
            ?? new RefundRepository($this->pdo);

        $this->returnRepository =
            $returnRepository
            ?? new ReturnRepository($this->pdo);

        $this->paymentRepository =
            $paymentRepository
            ?? new PaymentRepository($this->pdo);
    }

    public function findByReturnId(
        int $returnId
    ): array {
        return $this->repository->findByReturnId(
            $returnId
        );
    }

    public function findById(
        int $id
    ): ?Refund {
        return $this->repository->findById($id);
    }

    public function createRefund(
        int $returnId,
        string $refundNumber,
        string $amount,
        string $currency = 'IRR',
        ?int $paymentId = null,
        ?string $reason = null,
        ?string $gateway = null
    ): Refund {
        if ($returnId <= 0) {
            throw new InvalidArgumentException(
                'Invalid return id.'
            );
        }

        $refundNumber =
            trim($refundNumber);

        if ($refundNumber === '') {
            throw new InvalidArgumentException(
                'Refund number is required.'
            );
        }

        if (mb_strlen($refundNumber) > 50) {
            throw new InvalidArgumentException(
                'Refund number is too long.'
            );
        }

        $amount =
            trim($amount);

        if (
            $amount === ''
            || !is_numeric($amount)
            || (float)$amount <= 0
        ) {
            throw new InvalidArgumentException(
                'Refund amount must be greater than zero.'
            );
        }

        $currency =
            strtoupper(trim($currency));

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException(
                'Invalid refund currency.'
            );
        }

        if (
            $paymentId !== null
            && $paymentId <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid payment id.'
            );
        }

        if (
            $reason !== null
            && mb_strlen($reason) > 500
        ) {
            throw new InvalidArgumentException(
                'Refund reason is too long.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $existing =
                $this->repository
                ->findByRefundNumber(
                    $refundNumber
                );

            if ($existing !== null) {
                if (
                    $existing->returnId !== $returnId
                    || $existing->amount !== $amount
                    || $existing->currency !== $currency
                ) {
                    throw new RuntimeException(
                        'Refund number already belongs to another refund.'
                    );
                }

                $this->pdo->commit();

                return $existing;
            }

            $return =
                $this->returnRepository
                ->findById($returnId);

            if ($return === null) {
                throw new RuntimeException(
                    'Return request not found.'
                );
            }

            if (
                $return->status
                !== ReturnRequest::STATUS_COMPLETED
            ) {
                throw new RuntimeException(
                    'Refund can only be created for a completed return.'
                );
            }

            $payment = null;

            if ($paymentId !== null) {
                $payment =
                    $this->paymentRepository
                    ->findById($paymentId);

                if ($payment === null) {
                    throw new RuntimeException(
                        'Payment not found.'
                    );
                }

                if ($payment->orderId !== $return->orderId) {
                    throw new RuntimeException(
                        'Payment does not belong to the return order.'
                    );
                }

                if ($payment->status !== 'successful') {
                    throw new RuntimeException(
                        'Only successful payments can be refunded.'
                    );
                }

                if ($payment->currency !== $currency) {
                    throw new RuntimeException(
                        'Refund currency does not match payment currency.'
                    );
                }

                if (
                    (float)$amount
                    > (float)$payment->amount
                ) {
                    throw new InvalidArgumentException(
                        'Refund amount exceeds payment amount.'
                    );
                }
            }

            $refund =
                $this->repository->create(
                    $return->id,
                    $return->orderId,
                    $payment?->id,
                    $refundNumber,
                    $amount,
                    $currency,
                    Refund::STATUS_PENDING,
                    $gateway,
                    null,
                    $reason
                );

            $this->pdo->commit();

            return $refund;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function changeStatus(
        int $refundId,
        string $toStatus
    ): Refund {
        if ($refundId <= 0) {
            throw new InvalidArgumentException(
                'Invalid refund id.'
            );
        }

        $toStatus =
            trim($toStatus);

        if (
            !in_array(
                $toStatus,
                [
                    Refund::STATUS_PENDING,
                    Refund::STATUS_PROCESSING,
                    Refund::STATUS_SUCCESSFUL,
                    Refund::STATUS_FAILED,
                    Refund::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid refund status.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $refund =
                $this->repository
                ->findById($refundId);

            if ($refund === null) {
                throw new RuntimeException(
                    'Refund not found.'
                );
            }

            if ($refund->status === $toStatus) {
                $this->pdo->commit();

                return $refund;
            }

            $allowed =
                self::TRANSITIONS[$refund->status] ?? [];

            if (
                !in_array(
                    $toStatus,
                    $allowed,
                    true
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid refund status transition.'
                );
            }

            $updated =
                $this->repository
                ->updateStatus(
                    $refundId,
                    $toStatus
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
}
