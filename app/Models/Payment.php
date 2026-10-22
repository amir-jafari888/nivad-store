<?php

declare(strict_types=1);

final class Payment
{
    public int $id;
    public int $orderId;
    public ?int $paymentAttemptId;
    public ?int $userId;
    public string $gateway;
    public string $transactionId;
    public string $amount;
    public string $currency;
    public string $status;
    public string $paidAt;
    public ?string $refundedAt;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $orderId,
        ?int $paymentAttemptId,
        ?int $userId,
        string $gateway,
        string $transactionId,
        string $amount,
        string $currency,
        string $status,
        string $paidAt,
        ?string $refundedAt,
        ?string $createdAt,
        ?string $updatedAt
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->paymentAttemptId =
            $paymentAttemptId;
        $this->userId = $userId;
        $this->gateway = $gateway;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->paidAt = $paidAt;
        $this->refundedAt = $refundedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(
        array $data
    ): self {
        return new self(
            (int)$data['id'],
            (int)$data['order_id'],
            $data['payment_attempt_id'] !== null
                ? (int)$data['payment_attempt_id']
                : null,
            $data['user_id'] !== null
                ? (int)$data['user_id']
                : null,
            (string)$data['gateway'],
            (string)$data['transaction_id'],
            (string)$data['amount'],
            (string)$data['currency'],
            (string)$data['status'],
            (string)$data['paid_at'],
            $data['refunded_at'] !== null
                ? (string)$data['refunded_at']
                : null,
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null
        );
    }
}
