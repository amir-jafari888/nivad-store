<?php

declare(strict_types=1);

final class UploadValidator implements ValidatorInterface
{
    private array $config;

    public function __construct()
    {
        $securityConfig =
            require __DIR__ . '/../../config/security.php';

        $this->config =
            $securityConfig['upload'] ?? [];
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (!isset($data['file'])) {
            return [
                'file' => 'File is required.',
            ];
        }

        if (!is_array($data['file'])) {
            return [
                'file' => 'Invalid upload data.',
            ];
        }

        $file = $data['file'];

        $error = $file['error'] ?? null;

        if (
            !is_int($error) &&
            !is_numeric($error)
        ) {
            return [
                'file' => 'Invalid upload error code.',
            ];
        }

        $error = (int) $error;

        if ($error !== UPLOAD_ERR_OK) {
            return [
                'file' =>
                $this->uploadErrorMessage($error),
            ];
        }

        $tmpName =
            $file['tmp_name'] ?? null;

        if (
            !is_string($tmpName) ||
            $tmpName === '' ||
            !is_uploaded_file($tmpName)
        ) {
            return [
                'file' => 'Invalid uploaded file.',
            ];
        }

        $size =
            $file['size'] ?? null;

        if (
            !is_numeric($size) ||
            (int) $size <= 0
        ) {
            return [
                'file' => 'Invalid file size.',
            ];
        }

        $size = (int) $size;

        $maxSize =
            (int) (
                $this->config['max_size']
                ?? (5 * 1024 * 1024)
            );

        if ($size > $maxSize) {
            return [
                'file' =>
                'File size exceeds the allowed limit.',
            ];
        }

        $originalName =
            isset($file['name']) &&
            is_string($file['name'])
            ? $file['name']
            : '';

        $extension =
            strtolower(
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                )
            );

        $allowedExtensions =
            $this->config['allowed_extensions']
            ?? [];

        $allowedExtensions =
            array_map(
                'strtolower',
                array_map(
                    'strval',
                    $allowedExtensions
                )
            );

        if (
            $extension === '' ||
            !in_array(
                $extension,
                $allowedExtensions,
                true
            )
        ) {
            return [
                'file' =>
                'File extension is not allowed.',
            ];
        }

        $mimeType =
            $this->detectMimeType($tmpName);

        if ($mimeType === null) {
            return [
                'file' =>
                'Unable to determine file MIME type.',
            ];
        }

        if (
            !$this->isAllowedMimeType(
                $mimeType
            )
        ) {
            return [
                'file' =>
                'File MIME type is not allowed.',
            ];
        }

        if (
            !$this->extensionMatchesMime(
                $extension,
                $mimeType
            )
        ) {
            return [
                'file' =>
                'File extension does not match MIME type.',
            ];
        }

        if (
            !$this->validateImageContent(
                $tmpName,
                $mimeType
            )
        ) {
            return [
                'file' =>
                'Invalid image file content.',
            ];
        }

        return [];
    }

    private function detectMimeType(
        string $path
    ): ?string {
        if (!class_exists('finfo')) {
            return null;
        }

        $finfo =
            new finfo(FILEINFO_MIME_TYPE);

        $mimeType =
            $finfo->file($path);

        return is_string($mimeType)
            ? $mimeType
            : null;
    }

    private function isAllowedMimeType(
        string $mimeType
    ): bool {
        return in_array(
            $mimeType,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            true
        );
    }

    private function extensionMatchesMime(
        string $extension,
        string $mimeType
    ): bool {
        return match ($mimeType) {
            'image/jpeg' =>
            in_array(
                $extension,
                ['jpg', 'jpeg'],
                true
            ),

            'image/png' =>
            $extension === 'png',

            'image/webp' =>
            $extension === 'webp',

            default => false,
        };
    }

    private function validateImageContent(
        string $path,
        string $mimeType
    ): bool {
        $imageInfo =
            @getimagesize($path);

        if (
            !is_array($imageInfo) ||
            !isset($imageInfo['mime'])
        ) {
            return false;
        }

        return $imageInfo['mime'] === $mimeType;
    }

    private function uploadErrorMessage(
        int $error
    ): string {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
            'Uploaded file is too large.',

            UPLOAD_ERR_PARTIAL =>
            'Uploaded file is incomplete.',

            UPLOAD_ERR_NO_FILE =>
            'No file was uploaded.',

            UPLOAD_ERR_NO_TMP_DIR =>
            'Upload temporary directory is unavailable.',

            UPLOAD_ERR_CANT_WRITE =>
            'Uploaded file could not be written.',

            UPLOAD_ERR_EXTENSION =>
            'Upload was blocked by a server extension.',

            default =>
            'Unknown upload error.',
        };
    }
}
