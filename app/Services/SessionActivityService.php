<?php

declare(strict_types=1);

final class SessionActivityService
{
    private UserSessionService $sessions;

    public function __construct(
        ?UserSessionService $sessions = null
    ) {
        $this->sessions =
            $sessions ??
            new UserSessionService();
    }

    public function touch(): bool
    {
        Session::start();

        $sessionId =
            Session::id();

        if ($sessionId === '') {
            return false;
        }

        return $this->sessions
            ->touchCurrent(
                $sessionId
            );
    }
}
