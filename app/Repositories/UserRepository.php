<?php

declare(strict_types=1);

final class UserRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db = $db ?? Database::connection();
    }

    public function findById(
        int $userId
    ): ?array {
        if ($userId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                first_name,
                last_name,
                email,
                phone,
                password_hash,
                status,
                email_verified_at,
                phone_verified_at,
                last_login_at,
                created_at,
                updated_at,
                deleted_at
             FROM users
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $userId,
        ]);

        $user = $stmt->fetch();

        return $user !== false
            ? $user
            : null;
    }

    public function findByIdentifier(
        string $identifier
    ): ?array {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                first_name,
                last_name,
                email,
                phone,
                password_hash,
                status,
                email_verified_at,
                phone_verified_at,
                last_login_at,
                created_at,
                updated_at,
                deleted_at
             FROM users
             WHERE deleted_at IS NULL
               AND (
                    email = :identifier_email
                    OR phone = :identifier_phone
               )
             LIMIT 1'
        );

        $stmt->execute([
            'identifier_email' => $identifier,
            'identifier_phone' => $identifier,
        ]);

        $user = $stmt->fetch();

        return $user !== false
            ? $user
            : null;
    }

    public function existsById(
        int $userId
    ): bool {
        return $this->findById($userId) !== null;
    }

    public function isActive(
        array $user
    ): bool {
        return
            isset($user['status']) &&
            $user['status'] === 'active' &&
            empty($user['deleted_at']);
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $passwordHash
    ): int {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = mb_strtolower(trim($email));
        $phone = trim($phone);
        $passwordHash = trim($passwordHash);

        if (
            $firstName === '' ||
            $lastName === '' ||
            $email === '' ||
            $phone === '' ||
            $passwordHash === ''
        ) {
            throw new InvalidArgumentException(
                'Invalid user data.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (
            first_name,
            last_name,
            email,
            phone,
            password_hash,
            status
        ) VALUES (
            :first_name,
            :last_name,
            :email,
            :phone,
            :password_hash,
            :status
        )'
        );

        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $passwordHash,
            'status' => 'pending',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(
        int $userId,
        string $firstName,
        string $lastName
    ): ?array {
        if ($userId < 1) {
            return null;
        }

        $firstName = trim($firstName);
        $lastName = trim($lastName);

        if (
            $firstName === '' ||
            $lastName === ''
        ) {
            throw new InvalidArgumentException(
                'نام و نام خانوادگی الزامی است.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE users
         SET
            first_name = :first_name,
            last_name = :last_name,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'id' => $userId,
        ]);

        return $this->findById($userId);
    }

    public function updatePassword(
        int $userId,
        string $passwordHash
    ): bool {
        if ($userId < 1 || trim($passwordHash) === '') {
            throw new InvalidArgumentException(
                'Invalid password update data.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE users
         SET password_hash = :password_hash
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function isEmailVerified(array $user): bool
    {
        return !empty($user['email_verified_at']);
    }

    public function markEmailVerified(int $userId): bool
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Invalid user ID.');
        }

        $stmt = $this->db->prepare(
            'UPDATE users
         SET
            email_verified_at = CURRENT_TIMESTAMP,
            status = CASE
                WHEN status = "pending"
                THEN "active"
                ELSE status
            END
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function getVerificationStatus(int $userId): ?array
    {
        if ($userId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            status,
            email,
            email_verified_at,
            phone,
            phone_verified_at
         FROM users
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $userId,
        ]);

        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function updateLastLogin(int $userId): bool
    {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE users
         SET last_login_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function findAll(): array
    {
        $stmt =
            $this->db->query(
                'SELECT
                id,
                first_name,
                last_name,
                email,
                phone,
                status,
                email_verified_at,
                phone_verified_at,
                last_login_at,
                created_at,
                updated_at,
                deleted_at
             FROM users
             WHERE deleted_at IS NULL
             ORDER BY id DESC'
            );

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function updateStatus(
        int $userId,
        string $status
    ): ?array {
        if (
            $userId < 1 ||
            !in_array(
                $status,
                [
                    'active',
                    'inactive',
                    'blocked',
                    'pending',
                ],
                true
            )
        ) {
            return null;
        }

        $stmt =
            $this->db->prepare(
                'UPDATE users
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
            );

        $stmt->execute([
            'id' => $userId,
            'status' => $status,
        ]);

        return $this->findById($userId);
    }
}
