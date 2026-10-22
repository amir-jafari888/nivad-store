<?php

declare(strict_types=1);

final class ReturnRequest
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_INSPECTED = 'inspected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public int $id;
    public int $orderId;
    public int $userId;
    public string $returnNumber;
    public string $status;
    public ?string $reason;
    public ?string $customerNote;
    public ?string $adminNote;
    public string $requestedAt;
    public ?string $approvedAt;
    public ?string $receivedAt;
    public ?string $completedAt;
    public ?string $cancelledAt;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        int $id,
        int $orderId,
        int $userId,
        string $returnNumber,
        string $status,
        ?string $reason,
        ?string $customerNote,
        ?string $adminNote,
        string $requestedAt,
        ?string $approvedAt,
        ?string $receivedAt,
        ?string $completedAt,
        ?string $cancelledAt,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->returnNumber = $returnNumber;
        $this->status = $status;
        $this->reason = $reason;
        $this->customerNote = $customerNote;
        $this->adminNote = $adminNote;
        $this->requestedAt = $requestedAt;
        $this->approvedAt = $approvedAt;
        $this->receivedAt = $receivedAt;
        $this->completedAt = $completedAt;
        $this->cancelledAt = $cancelledAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['order_id'],
            (int)$data['user_id'],
            (string)$data['return_number'],
            (string)$data['status'],
            $data['reason'] !== null
                ? (string)$data['reason']
                : null,
            $data['customer_note'] !== null
                ? (string)$data['customer_note']
                : null,
            $data['admin_note'] !== null
                ? (string)$data['admin_note']
                : null,
            (string)$data['requested_at'],
            $data['approved_at'] !== null
                ? (string)$data['approved_at']
                : null,
            $data['received_at'] !== null
                ? (string)$data['received_at']
                : null,
            $data['completed_at'] !== null
                ? (string)$data['completed_at']
                : null,
            $data['cancelled_at'] !== null
                ? (string)$data['cancelled_at']
                : null,
            (string)$data['created_at'],
            (string)$data['updated_at']
        );
    }
}
