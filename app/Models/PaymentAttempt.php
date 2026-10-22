<?php

declare(strict_types=1);

final class PaymentAttempt
{
    public int $id;
    public int $orderId;
    public ?int $userId;
    public string $gateway;
    public ?string $gatewayTransactionId;
    public ?string $authority;
    public string $amount;
    public string $currency;
    public string $status;
    public ?string $errorCode;
    public ?string $errorMessage;
    public ?string $ipAddress;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $completedAt;

    public function __construct(
        int $id,
        int $orderId,
        ?int $userId,
        string $gateway,
        ?string $gatewayTransactionId,
        ?string $authority,
        string $amount,
        string $currency,
        string $status,
        ?string $errorCode,
        ?string $errorMessage,
        ?string $ipAddress,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $completedAt
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->gateway = $gateway;
        $this->gatewayTransactionId =
            $gatewayTransactionId;
        $this->authority = $authority;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->ipAddress = $ipAddress;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->completedAt = $completedAt;
    }

    public static function fromArray(
        array $data
    ): self {
        return new self(
            (int)$data['id'],
            (int)$data['order_id'],
            $data['user_id'] !== null
                ? (int)$data['user_id']
                : null,
            (string)$data['gateway'],
            $data['gateway_transaction_id'] !== null
                ? (string)$data['gateway_transaction_id']
                : null,
            $data['authority'] !== null
                ? (string)$data['authority']
                : null,
            (string)$data['amount'],
            (string)$data['currency'],
            (string)$data['status'],
            $data['error_code'] !== null
                ? (string)$data['error_code']
                : null,
            $data['error_message'] !== null
                ? (string)$data['error_message']
                : null,
            $data['ip_address'] !== null
                ? (string)$data['ip_address']
                : null,
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null,
            $data['completed_at'] !== null
                ? (string)$data['completed_at']
                : null
        );
    }
}
