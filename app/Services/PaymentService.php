<?php

declare(strict_types=1);

final class PaymentService
{
    private PaymentRepository $paymentRepository;

    private PaymentAttemptRepository $attemptRepository;

    private OrderRepository $orderRepository;

    private NotificationService $notificationService;

    private PDO $pdo;

    public function __construct(
        ?PaymentRepository $paymentRepository = null,
        ?PaymentAttemptRepository $attemptRepository = null,
        ?OrderRepository $orderRepository = null,
        ?PDO $pdo = null,
        ?NotificationService $notificationService = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();

        $this->paymentRepository =
            $paymentRepository ??
            new PaymentRepository($this->pdo);

        $this->attemptRepository =
            $attemptRepository ??
            new PaymentAttemptRepository($this->pdo);

        $this->orderRepository =
            $orderRepository ??
            new OrderRepository($this->pdo);

        $this->notificationService =
            $notificationService ??
            new NotificationService(
                new NotificationRepository($this->pdo)
            );
    }

    public function findAttemptById(
        int $id
    ): ?PaymentAttempt {
        return $this->attemptRepository
            ->findById($id);
    }

    public function findAttemptsByOrder(
        int $orderId
    ): array {
        return $this->attemptRepository
            ->findAllByOrder($orderId);
    }

    public function createAttempt(
        int $orderId,
        ?int $userId,
        string $gateway,
        ?string $gatewayTransactionId,
        ?string $authority,
        string $amount,
        string $currency = 'IRR',
        string $status = 'initiated',
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?string $ipAddress = null
    ): PaymentAttempt {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        $status = trim($status);

        if ($status !== 'initiated') {
            throw new InvalidArgumentException(
                'Payment attempt must be created with initiated status.'
            );
        }

        return $this->attemptRepository->create(
            $orderId,
            $userId,
            $gateway,
            $gatewayTransactionId,
            $authority,
            $amount,
            $currency,
            $status,
            $errorCode,
            $errorMessage,
            $ipAddress
        );
    }

    public function findAllPayments(): array
    {
        return $this->paymentRepository
            ->findAll();
    }

    public function findAllAttempts(): array
    {
        return $this->attemptRepository
            ->findAll();
    }

    public function findPaymentById(
        int $id
    ): ?Payment {
        return $this->paymentRepository
            ->findById($id);
    }

    public function findPaymentsByOrder(
        int $orderId
    ): array {
        return $this->paymentRepository
            ->findAllByOrder($orderId);
    }

    public function startAttempt(
        int $orderId,
        ?int $userId,
        string $gateway,
        string $amount,
        string $currency = 'IRR',
        ?string $ipAddress = null
    ): PaymentAttempt {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        $gateway = trim($gateway);

        if ($gateway === '') {
            throw new InvalidArgumentException(
                'Payment gateway is required.'
            );
        }

        $currency = strtoupper(
            trim($currency)
        );

        if (
            $currency === '' ||
            !preg_match(
                '/^[A-Z]{3}$/',
                $currency
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid payment currency.'
            );
        }

        $amount = trim($amount);

        if (
            $amount === '' ||
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $amount
            ) ||
            (float)$amount <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid payment amount.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $order =
                $this->orderRepository
                ->findByIdForUpdate(
                    $orderId
                );

            if ($order === null) {
                throw new RuntimeException(
                    'Order not found.'
                );
            }

            $latest =
                $this->attemptRepository
                ->findLatestByOrder(
                    $orderId
                );

            if (
                $latest !== null &&
                (
                    $latest->status === 'initiated' ||
                    $latest->status === 'pending'
                )
            ) {
                throw new RuntimeException(
                    'An active payment attempt already exists.'
                );
            }

            $attempt =
                $this->attemptRepository->create(
                    $orderId,
                    $userId,
                    $gateway,
                    null,
                    null,
                    $amount,
                    $currency,
                    'initiated',
                    null,
                    null,
                    $ipAddress
                );

            $this->pdo->commit();

            return $attempt;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function completeAttempt(
        int $attemptId,
        string $transactionId,
        string $expectedCurrentStatus = 'pending'
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $transactionId =
            trim($transactionId);

        if ($transactionId === '') {
            throw new InvalidArgumentException(
                'Transaction id is required.'
            );
        }

        if ($expectedCurrentStatus !== 'pending') {
            throw new InvalidArgumentException(
                'Payment attempt can only be completed from pending status.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $attempt =
                $this->attemptRepository
                ->findByIdForUpdate(
                    $attemptId
                );

            if ($attempt === null) {
                throw new RuntimeException(
                    'Payment attempt not found.'
                );
            }

            if ($attempt->status === 'success') {
                if (
                    trim(
                        (string)
                        $attempt->gatewayTransactionId
                    ) === $transactionId
                ) {
                    $this->pdo->commit();

                    return $attempt;
                }

                throw new RuntimeException(
                    'Payment attempt is already completed with another transaction id.'
                );
            }

            if ($attempt->status !== 'pending') {
                throw new RuntimeException(
                    'Payment attempt is not in the expected state.'
                );
            }

            $updated =
                $this->attemptRepository
                ->updateGatewayData(
                    $attemptId,
                    $transactionId,
                    $attempt->authority,
                    'pending'
                );

            if ($updated === null) {
                throw new RuntimeException(
                    'Payment attempt gateway data could not be updated.'
                );
            }

            $completed =
                $this->attemptRepository
                ->markSuccess(
                    $attemptId,
                    'pending'
                );

            if ($completed === null) {
                throw new RuntimeException(
                    'Payment attempt could not be completed.'
                );
            }

            $this->pdo->commit();

            return $completed;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
    public function failAttempt(
        int $attemptId,
        string $errorCode,
        string $errorMessage
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $errorCode =
            trim($errorCode);

        $errorMessage =
            trim($errorMessage);

        if ($errorCode === '') {
            throw new InvalidArgumentException(
                'Payment error code is required.'
            );
        }

        if ($errorMessage === '') {
            throw new InvalidArgumentException(
                'Payment error message is required.'
            );
        }

        if (strlen($errorCode) > 100) {
            throw new InvalidArgumentException(
                'Payment error code is too long.'
            );
        }

        if (strlen($errorMessage) > 500) {
            throw new InvalidArgumentException(
                'Payment error message is too long.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $attempt =
                $this->attemptRepository
                ->findByIdForUpdate(
                    $attemptId
                );

            if ($attempt === null) {
                throw new RuntimeException(
                    'Payment attempt not found.'
                );
            }

            if ($attempt->status === 'failed') {
                if (
                    $attempt->errorCode === $errorCode &&
                    $attempt->errorMessage === $errorMessage
                ) {
                    $this->pdo->commit();

                    return $attempt;
                }

                throw new RuntimeException(
                    'Payment attempt is already failed with another error.'
                );
            }

            if ($attempt->status !== 'pending') {
                throw new RuntimeException(
                    'Only pending payment attempts can be failed.'
                );
            }

            $result =
                $this->attemptRepository->markFailed(
                    $attemptId,
                    $errorCode,
                    $errorMessage,
                    'pending'
                );

            if ($result === null) {
                throw new RuntimeException(
                    'Payment attempt could not be failed.'
                );
            }

            $userId =
                $result->userId;

            if ($userId === null) {
                $order =
                    $this->orderRepository
                    ->findById(
                        $result->orderId
                    );

                $userId =
                    $order?->userId;
            }

            if ($userId !== null) {
                $this->notificationService->createForUser(
                    $userId,
                    NotificationService::TYPE_PAYMENT_FAILED,
                    'پرداخت ناموفق',
                    'پرداخت سفارش شما ناموفق بود.',
                    [
                        'order_id' =>
                        $result->orderId,
                        'attempt_id' =>
                        $result->id,
                        'gateway' =>
                        $result->gateway,
                        'amount' =>
                        $result->amount,
                        'currency' =>
                        $result->currency,
                        'error_code' =>
                        $result->errorCode,
                    ]
                );
            }

            $this->pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function cancelAttempt(
        int $attemptId
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $attempt =
            $this->attemptRepository->findById(
                $attemptId
            );

        if ($attempt === null) {
            throw new RuntimeException(
                'Payment attempt not found.'
            );
        }

        if ($attempt->status === 'cancelled') {
            return $attempt;
        }

        if ($attempt->status !== 'pending') {
            throw new RuntimeException(
                'Only pending payment attempts can be cancelled.'
            );
        }

        $result =
            $this->attemptRepository->markCancelled(
                $attemptId,
                'pending'
            );

        if ($result === null) {
            throw new RuntimeException(
                'Payment attempt could not be cancelled.'
            );
        }

        return $result;
    }

    public function expireAttempt(
        int $attemptId
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $attempt =
            $this->attemptRepository->findById(
                $attemptId
            );

        if ($attempt === null) {
            throw new RuntimeException(
                'Payment attempt not found.'
            );
        }

        if ($attempt->status === 'expired') {
            return $attempt;
        }

        if ($attempt->status !== 'pending') {
            throw new RuntimeException(
                'Only pending payment attempts can be expired.'
            );
        }

        $result =
            $this->attemptRepository->markExpired(
                $attemptId,
                'pending'
            );

        if ($result === null) {
            throw new RuntimeException(
                'Payment attempt could not be expired.'
            );
        }

        return $result;
    }

    public function markAttemptPending(
        int $attemptId
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $attempt =
            $this->attemptRepository->findById(
                $attemptId
            );

        if ($attempt === null) {
            throw new RuntimeException(
                'Payment attempt not found.'
            );
        }

        if ($attempt->status === 'pending') {
            return $attempt;
        }

        if ($attempt->status !== 'initiated') {
            throw new RuntimeException(
                'Payment attempt cannot be moved to pending from its current state.'
            );
        }

        $result =
            $this->attemptRepository->markPending(
                $attemptId
            );

        if ($result === null) {
            throw new RuntimeException(
                'Payment attempt could not be moved to pending.'
            );
        }

        return $result;
    }

    public function findPaymentByAttemptId(
        int $paymentAttemptId
    ): ?Payment {
        return $this->paymentRepository
            ->findByAttemptId(
                $paymentAttemptId
            );
    }



    public function createSuccessfulPaymentFromAttempt(
        int $attemptId
    ): Payment {
        if ($attemptId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment attempt id.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $attempt =
                $this->attemptRepository
                ->findByIdForUpdate(
                    $attemptId
                );

            if ($attempt === null) {
                throw new RuntimeException(
                    'Payment attempt not found.'
                );
            }

            if ($attempt->status !== 'success') {
                throw new RuntimeException(
                    'Only successful payment attempts can be finalized.'
                );
            }

            $existing =
                $this->paymentRepository
                ->findByAttemptId(
                    $attemptId
                );

            if ($existing !== null) {
                $this->pdo->commit();

                return $existing;
            }

            if ($attempt->orderId <= 0) {
                throw new RuntimeException(
                    'Payment attempt has no valid order.'
                );
            }

            $transactionId =
                trim(
                    (string)$attempt->gatewayTransactionId
                );

            if ($transactionId === '') {
                throw new RuntimeException(
                    'Successful payment attempt has no gateway transaction id.'
                );
            }

            $existingTransaction =
                $this->paymentRepository
                ->findByTransactionId(
                    $transactionId
                );

            if ($existingTransaction !== null) {
                throw new RuntimeException(
                    'Payment transaction already exists.'
                );
            }

            $payment =
                $this->paymentRepository
                ->createFromSuccessfulAttempt(
                    $attempt
                );

            $userId =
                $payment->userId;

            if ($userId === null) {
                $order =
                    $this->orderRepository
                    ->findById(
                        $payment->orderId
                    );

                $userId =
                    $order?->userId;
            }

            if ($userId !== null) {
                $this->notificationService->createForUser(
                    $userId,
                    NotificationService::TYPE_PAYMENT_SUCCESS,
                    'پرداخت موفق',
                    'پرداخت سفارش شما با موفقیت انجام شد.',
                    [
                        'payment_id' =>
                        $payment->id,
                        'attempt_id' =>
                        $attempt->id,
                        'order_id' =>
                        $payment->orderId,
                        'amount' =>
                        $payment->amount,
                        'currency' =>
                        $payment->currency,
                        'gateway' =>
                        $payment->gateway,
                        'transaction_id' =>
                        $payment->transactionId,
                    ]
                );
            }

            $this->pdo->commit();

            return $payment;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function findPendingAttemptsByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->attemptRepository
            ->findPendingByOrder($orderId);
    }

    public function findLatestAttemptByOrder(
        int $orderId
    ): ?PaymentAttempt {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->attemptRepository
            ->findLatestByOrder($orderId);
    }

    public function refundPayment(
        int $paymentId,
        ?string $refundedAt = null
    ): Payment {
        if ($paymentId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment id.'
            );
        }

        if ($refundedAt !== null) {
            $refundedAt = trim($refundedAt);

            if ($refundedAt === '') {
                throw new InvalidArgumentException(
                    'Refunded at cannot be empty.'
                );
            }

            $date = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $refundedAt
            );

            $errors = DateTime::getLastErrors();

            if (
                $date === false ||
                (
                    $errors !== false &&
                    (
                        $errors['warning_count'] > 0 ||
                        $errors['error_count'] > 0
                    )
                ) ||
                $date->format('Y-m-d H:i:s') !== $refundedAt
            ) {
                throw new InvalidArgumentException(
                    'Invalid refunded at.'
                );
            }
        }

        $payment =
            $this->paymentRepository
            ->findById(
                $paymentId
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        if ($payment->status === 'refunded') {
            return $payment;
        }

        if ($payment->status === 'partially_refunded') {
            throw new RuntimeException(
                'Partially refunded payment cannot be fully refunded.'
            );
        }

        if ($payment->status !== 'successful') {
            throw new RuntimeException(
                'Only successful payments can be refunded.'
            );
        }

        $result =
            $this->paymentRepository
            ->markRefunded(
                $paymentId,
                $refundedAt,
                'successful'
            );

        if ($result === null) {
            throw new RuntimeException(
                'Payment could not be refunded.'
            );
        }

        return $result;
    }

    public function partiallyRefundPayment(
        int $paymentId,
        ?string $refundedAt = null
    ): Payment {
        if ($paymentId <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment id.'
            );
        }

        if ($refundedAt !== null) {
            $refundedAt = trim($refundedAt);

            if ($refundedAt === '') {
                throw new InvalidArgumentException(
                    'Refunded at cannot be empty.'
                );
            }

            $date = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $refundedAt
            );

            $errors = DateTime::getLastErrors();

            if (
                $date === false ||
                (
                    $errors !== false &&
                    (
                        $errors['warning_count'] > 0 ||
                        $errors['error_count'] > 0
                    )
                ) ||
                $date->format('Y-m-d H:i:s') !== $refundedAt
            ) {
                throw new InvalidArgumentException(
                    'Invalid refunded at.'
                );
            }
        }

        $payment =
            $this->paymentRepository
            ->findById(
                $paymentId
            );

        if ($payment === null) {
            throw new RuntimeException(
                'Payment not found.'
            );
        }

        if ($payment->status === 'partially_refunded') {
            return $payment;
        }

        if ($payment->status === 'refunded') {
            throw new RuntimeException(
                'Refunded payment cannot be partially refunded.'
            );
        }

        if ($payment->status !== 'successful') {
            throw new RuntimeException(
                'Only successful payments can be partially refunded.'
            );
        }

        $result =
            $this->paymentRepository
            ->markPartiallyRefunded(
                $paymentId,
                $refundedAt,
                'successful'
            );

        if ($result === null) {
            throw new RuntimeException(
                'Payment could not be partially refunded.'
            );
        }

        return $result;
    }

    public function findRefundedPaymentsByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->paymentRepository
            ->findRefundedByOrder(
                $orderId
            );
    }

    public function findPartiallyRefundedPaymentsByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->paymentRepository
            ->findPartiallyRefundedByOrder(
                $orderId
            );
    }

    public function findLatestRefundedPaymentByOrder(
        int $orderId
    ): ?Payment {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->paymentRepository
            ->findLatestRefundedByOrder(
                $orderId
            );
    }

    public function findLatestPartiallyRefundedPaymentByOrder(
        int $orderId
    ): ?Payment {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        return $this->paymentRepository
            ->findLatestPartiallyRefundedByOrder(
                $orderId
            );
    }
}
