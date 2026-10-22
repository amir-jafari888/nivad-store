<?php

declare(strict_types=1);

final class AuthService
{
    private UserRepository $users;
    private LoginAttemptService $loginAttempts;
    private EmailVerificationTokenService $emailVerificationTokens;
    private PDO $db;
    private ?string $lastVerificationToken = null;
    private AuthenticationAuditService $audit;

    public function __construct(
        ?UserRepository $users = null,
        ?LoginAttemptService $loginAttempts = null,
        ?EmailVerificationTokenService $emailVerificationTokens = null,
        ?PDO $db = null
    ) {
        $this->db = $db ?? Database::connection();

        $this->users =
            $users ??
            new UserRepository($this->db);

        $this->loginAttempts =
            $loginAttempts ??
            new LoginAttemptService();

        $this->emailVerificationTokens =
            $emailVerificationTokens ??
            new EmailVerificationTokenService();

        $this->audit =
            new AuthenticationAuditService();
    }

    public function authenticate(
        string $identifier,
        string $password
    ): array {
        $identifier = trim($identifier);

        if (
            $identifier === '' ||
            $password === ''
        ) {
            throw new AuthenticationException();
        }

        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        if (
            $this->loginAttempts->isRateLimited(
                $identifier,
                $ipAddress
            )
        ) {
            $user = $this->users
                ->findByIdentifier($identifier);

            $userId =
                is_array($user)
                ? (int)$user['id']
                : null;

            $this->loginAttempts
                ->recordRateLimited(
                    $userId,
                    $identifier,
                    $ipAddress,
                    $userAgent
                );

            throw new AuthenticationException(
                'تعداد تلاش‌های ورود بیش از حد مجاز است.'
            );
        }

        $user =
            $this->users->findByIdentifier(
                $identifier
            );

        if ($user === null) {
            $this->loginAttempts
                ->recordFailure(
                    null,
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'invalid_credentials'
                );

            throw new AuthenticationException();
        }

        if (
            ($user['status'] ?? null) === 'pending' &&
            empty($user['email_verified_at'])
        ) {
            $this->loginAttempts
                ->recordFailure(
                    (int)$user['id'],
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'email_not_verified'
                );

            throw new AuthenticationException(
                'ایمیل حساب شما تأیید نشده است.'
            );
        }

        if (!$this->users->isActive($user)) {
            $this->loginAttempts
                ->recordFailure(
                    (int)$user['id'],
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'inactive_account'
                );

            throw new AuthenticationException(
                'حساب کاربری فعال نیست.'
            );
        }

        if (
            !Password::verify(
                $password,
                (string)$user['password_hash']
            )
        ) {
            $this->loginAttempts
                ->recordFailure(
                    (int)$user['id'],
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'invalid_credentials'
                );

            throw new AuthenticationException();
        }

        if (
            Password::needsRehash(
                (string)$user['password_hash']
            )
        ) {
            $newHash =
                Password::hash($password);

            $this->users->updatePassword(
                (int)$user['id'],
                $newHash
            );
        }

        Auth::login(
            (int)$user['id']
        );

        $this->users->updateLastLogin(
            (int)$user['id']
        );

        $this->loginAttempts
            ->recordSuccess(
                (int)$user['id'],
                $identifier,
                $ipAddress,
                $userAgent
            );

        $this->audit->login(
            (int)$user['id'],
            $identifier
        );

        return $user;
    }

    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $password
    ): array {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = mb_strtolower(trim($email));
        $phone = trim($phone);

        if (
            $firstName === '' ||
            $lastName === '' ||
            $email === '' ||
            $phone === '' ||
            $password === ''
        ) {
            throw new ValidationException(
                [
                    'register' =>
                    'اطلاعات ثبت‌نام کامل نیست.',
                ]
            );
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new ValidationException(
                [
                    'email' =>
                    'ایمیل نامعتبر است.',
                ]
            );
        }

        if (
            !preg_match(
                '/^09\d{9}$/',
                $phone
            )
        ) {
            throw new ValidationException(
                [
                    'phone' =>
                    'شماره موبایل نامعتبر است.',
                ]
            );
        }

        if (mb_strlen($password) < 8) {
            throw new ValidationException(
                [
                    'password' =>
                    'رمز عبور باید حداقل ۸ کاراکتر باشد.',
                ]
            );
        }

        if (
            $this->users->findByIdentifier($email)
            !== null
        ) {
            throw new ValidationException(
                [
                    'email' =>
                    'این ایمیل قبلاً ثبت شده است.',
                ]
            );
        }

        if (
            $this->users->findByIdentifier($phone)
            !== null
        ) {
            throw new ValidationException(
                [
                    'phone' =>
                    'این شماره موبایل قبلاً ثبت شده است.',
                ]
            );
        }

        $passwordHash =
            Password::hash($password);

        $this->lastVerificationToken = null;

        $this->db->beginTransaction();

        try {
            $userId =
                $this->users->create(
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $passwordHash
                );

            $this->lastVerificationToken =
                $this->emailVerificationTokens
                ->generate($userId);

            $user =
                $this->users->findById($userId);

            if ($user === null) {
                throw new RuntimeException(
                    'Registered user could not be loaded.'
                );
            }

            $this->db->commit();

            return $user;
        } catch (PDOException $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->lastVerificationToken = null;

            if (
                (string)$exception->getCode() === '23000'
            ) {
                throw new ValidationException(
                    [
                        'register' =>
                        'اطلاعات ثبت‌نام قبلاً استفاده شده است.',
                    ]
                );
            }

            throw $exception;
        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->lastVerificationToken = null;

            throw $exception;
        }
    }

    public function lastVerificationToken(): ?string
    {
        return $this->lastVerificationToken;
    }
}
