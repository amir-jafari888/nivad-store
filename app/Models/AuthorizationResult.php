<?php

declare(strict_types=1);

final class AuthorizationResult
{
    public const REASON_ALLOWED = 'allowed';
    public const REASON_INVALID_ADMIN_USER = 'invalid_admin_user';
    public const REASON_INVALID_PERMISSION = 'invalid_permission';
    public const REASON_PERMISSION_NOT_FOUND = 'permission_not_found';
    public const REASON_PERMISSION_DENIED = 'permission_denied';

    private bool $allowed;
    private string $reason;

    public function __construct(
        bool $allowed,
        string $reason = ''
    ) {
        $this->allowed = $allowed;
        $this->reason = $reason;
    }

    public static function granted(): self
    {
        return new self(
            true,
            self::REASON_ALLOWED
        );
    }

    public static function rejected(
        string $reason
    ): self {
        return new self(
            false,
            $reason
        );
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    public function denied(): bool
    {
        return !$this->allowed;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
