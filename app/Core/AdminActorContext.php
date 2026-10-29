<?php

declare(strict_types=1);

/**
 * Prevents request data from selecting an arbitrary admin actor ID.
 * System/background events should use a null actor explicitly; interactive
 * operations must derive the actor from the authenticated server-side session.
 */
final class AdminActorContext
{
    private \Closure $currentAdminIdProvider;

    public function __construct(?callable $currentAdminIdProvider = null)
    {
        $this->currentAdminIdProvider = $currentAdminIdProvider !== null
            ? \Closure::fromCallable($currentAdminIdProvider)
            : static fn(): mixed => AdminAuth::id();
    }

    public function currentAdminId(): ?int
    {
        $value = ($this->currentAdminIdProvider)();

        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return null;
    }

    public function requireCurrentAdmin(?int $claimedAdminId = null): int
    {
        $currentAdminId = $this->currentAdminId();

        if ($currentAdminId === null) {
            throw new AdminAuthenticationException(
                'An authenticated admin session is required for this operation.'
            );
        }

        if ($claimedAdminId !== null) {
            if ($claimedAdminId < 1) {
                throw new InvalidArgumentException('Invalid admin actor ID.');
            }

            if ($claimedAdminId !== $currentAdminId) {
                throw new AuthorizationException(
                    'The supplied admin actor does not match the authenticated identity.'
                );
            }
        }

        return $currentAdminId;
    }
}
