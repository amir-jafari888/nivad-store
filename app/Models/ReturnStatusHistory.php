<?php

declare(strict_types=1);

final class ReturnStatusHistory
{
    public int $id;
    public int $returnId;
    public ?string $fromStatus;
    public string $toStatus;
    public ?int $changedByAdminId;
    public ?string $note;
    public string $createdAt;

    public function __construct(
        int $id,
        int $returnId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByAdminId,
        ?string $note,
        string $createdAt
    ) {
        $this->id = $id;
        $this->returnId = $returnId;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
        $this->changedByAdminId = $changedByAdminId;
        $this->note = $note;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['return_id'],
            $data['from_status'] !== null
                ? (string)$data['from_status']
                : null,
            (string)$data['to_status'],
            $data['changed_by_admin_id'] !== null
                ? (int)$data['changed_by_admin_id']
                : null,
            $data['note'] !== null
                ? (string)$data['note']
                : null,
            (string)$data['created_at']
        );
    }
}
