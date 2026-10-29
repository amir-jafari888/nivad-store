<?php

declare(strict_types=1);

/**
 * Pure support-ticket lifecycle and message validation. Persistence of tickets
 * and conversations remains a DB/schema gate.
 */
final class AdminSupportTicketPolicy
{
    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    private const TRANSITIONS = [
        'open' => ['in_progress', 'waiting_customer', 'resolved', 'closed'],
        'in_progress' => ['open', 'waiting_customer', 'resolved', 'closed'],
        'waiting_customer' => ['in_progress', 'resolved', 'closed'],
        'resolved' => ['open', 'closed'],
        'closed' => ['open'],
    ];

    public function validateCreation(
        string $subject,
        string $description,
        string $priority,
        ?int $customerId,
        ?int $assignedAdminId,
        ?int $createdByAdminId
    ): array {
        $subject = trim($subject);
        $description = trim($description);
        $priority = strtolower(trim($priority));

        if ($subject === '' || mb_strlen($subject) > 255) {
            throw new InvalidArgumentException('Ticket subject must contain 1–255 characters.');
        }

        if ($description === '' || mb_strlen($description) > 20000) {
            throw new InvalidArgumentException('Ticket description must contain 1–20000 characters.');
        }

        if (!in_array($priority, self::PRIORITIES, true)) {
            throw new InvalidArgumentException('Unsupported support ticket priority.');
        }

        foreach ([
            'customer_id' => $customerId,
            'assigned_admin_id' => $assignedAdminId,
            'created_by_admin_id' => $createdByAdminId,
        ] as $field => $id) {
            if ($id !== null && $id < 1) {
                throw new InvalidArgumentException('Invalid ' . $field . '.');
            }
        }

        return [
            'subject' => $subject,
            'description' => $description,
            'priority' => $priority,
            'customer_id' => $customerId,
            'assigned_admin_id' => $assignedAdminId,
            'created_by_admin_id' => $createdByAdminId,
            'status' => 'open',
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        return isset(self::TRANSITIONS[$from])
            && in_array($to, self::TRANSITIONS[$from], true);
    }

    public function assertTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new InvalidArgumentException('Support ticket state transition is not allowed.');
        }
    }

    public function validateMessage(
        string $message,
        string $visibility,
        ?int $authorAdminId,
        ?int $authorCustomerId
    ): array {
        $message = trim($message);
        $visibility = strtolower(trim($visibility));

        if ($message === '' || mb_strlen($message) > 20000) {
            throw new InvalidArgumentException('Support message must contain 1–20000 characters.');
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $message) === 1) {
            throw new InvalidArgumentException('Support message contains unsupported control characters.');
        }

        if (!in_array($visibility, ['public', 'internal'], true)) {
            throw new InvalidArgumentException('Support message visibility must be public or internal.');
        }

        if (($authorAdminId === null) === ($authorCustomerId === null)) {
            throw new InvalidArgumentException('A support message must have exactly one author identity.');
        }

        if (
            ($authorAdminId !== null && $authorAdminId < 1) ||
            ($authorCustomerId !== null && $authorCustomerId < 1)
        ) {
            throw new InvalidArgumentException('Invalid support message author ID.');
        }

        if ($visibility === 'internal' && $authorAdminId === null) {
            throw new InvalidArgumentException('Only an admin may create an internal support message.');
        }

        return [
            'message' => $message,
            'visibility' => $visibility,
            'author_admin_id' => $authorAdminId,
            'author_customer_id' => $authorCustomerId,
        ];
    }
}
