<?php

declare(strict_types=1);

final class Refund
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public int $id;
    public int $returnId;
    public int $orderId;
    public ?int $paymentId;
    public string $refundNumber;
    public string $amount;
    public string $currency;
    public string $status;
    public ?string $gateway;
    public ?string $gatewayRefundId;
    public ?string $reason;
    public string $requestedAt;
    public ?string $processedAt;
    public ?string $completedAt;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        int $id,
        int $returnId,
        int $orderId,
        ?int $paymentId,
        string $refundNumber,
        string $amount,
        string $currency,
        string $status,
        ?string $gateway,
        ?string $gatewayRefundId,
        ?string $reason,
        string $requestedAt,
        ?string $processedAt,
        ?string $completedAt,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->returnId = $returnId;
        $this->orderId = $orderId;
        $this->paymentId = $paymentId;
        $this->refundNumber = $refundNumber;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->gateway = $gateway;
        $this->gatewayRefundId = $gatewayRefundId;
        $this->reason = $reason;
        $this->requestedAt = $requestedAt;
        $this->processedAt = $processedAt;
        $this->completedAt = $completedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['return_id'],
            (int)$data['order_id'],
            $data['payment_id'] !== null
                ? (int)$data['payment_id']
                : null,
            (string)$data['refund_number'],
            (string)$data['amount'],
            (string)$data['currency'],
            (string)$data['status'],
            $data['gateway'] !== null
                ? (string)$data['gateway']
                : null,
            $data['gateway_refund_id'] !== null
                ? (string)$data['gateway_refund_id']
                : null,
            $data['reason'] !== null
                ? (string)$data['reason']
                : null,
            (string)$data['requested_at'],
            $data['processed_at'] !== null
                ? (string)$data['processed_at']
                : null,
            $data['completed_at'] !== null
                ? (string)$data['completed_at']
                : null,
            (string)$data['created_at'],
            (string)$data['updated_at']
        );
    }
}
