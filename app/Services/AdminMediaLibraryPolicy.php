<?php

/** Non-DB media-library lifecycle validation. */
final class AdminMediaLibraryPolicy
{
    public static function validateMetadata(array $input): array
    {
        $name = trim((string)($input['name'] ?? ''));
        $mime = trim((string)($input['mime_type'] ?? ''));
        $size = (int)($input['size'] ?? 0);
        $hash = strtolower(trim((string)($input['sha256'] ?? '')));

        if ($name === '' || mb_strlen($name) > 255 || $size < 1 || $size > 20 * 1024 * 1024) {
            throw new InvalidArgumentException('Media metadata is invalid.');
        }
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMime, true)) {
            throw new InvalidArgumentException('Unsupported media MIME type.');
        }
        if (!preg_match('/^[a-f0-9]{64}$/', $hash)) {
            throw new InvalidArgumentException('Media SHA-256 is invalid.');
        }
        return ['name' => $name, 'mime_type' => $mime, 'size' => $size, 'sha256' => $hash];
    }

    public static function assertLifecycleAction(string $action): void
    {
        if (!in_array($action, ['attach', 'detach', 'archive', 'restore', 'delete'], true)) {
            throw new InvalidArgumentException('Unsupported media lifecycle action.');
        }
    }
}
