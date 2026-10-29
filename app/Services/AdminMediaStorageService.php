<?php

declare(strict_types=1);

/**
 * Secure private image storage independent of the database.
 *
 * Requires NIVAD_PRIVATE_STORAGE_PATH to point outside the web document root.
 * Returns a storage key and verified metadata; persistence of that metadata
 * into the application's database is a separate, currently gated step.
 */
final class AdminMediaStorageService
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const DEFAULT_MAX_BYTES = 5242880;
    private const MAX_DIMENSION = 12000;
    private const MAX_PIXELS = 50000000;

    /**
     * @return array{storage_key:string, original_name:string, mime_type:string, file_size_bytes:int, sha256:string, width:int, height:int}
     */
    public function storeUploadedFile(array $upload): array
    {
        $error = $upload['error'] ?? null;
        if (!is_int($error) && !is_numeric($error)) {
            throw new InvalidArgumentException('Invalid upload error code.');
        }
        if ((int) $error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage((int) $error));
        }

        $temporaryPath = $upload['tmp_name'] ?? null;
        if (!is_string($temporaryPath) || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new InvalidArgumentException('The file is not a valid HTTP upload.');
        }

        $originalName = $this->safeOriginalName(
            is_string($upload['name'] ?? null) ? $upload['name'] : ''
        );

        $metadata = $this->inspectFile($temporaryPath, $originalName);
        $storageRoot = $this->privateStorageRoot();
        $relativeDirectory = gmdate('Y/m');
        $targetDirectory = $storageRoot . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0700, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Private media storage directory could not be created.');
        }

        $extension = self::MIME_EXTENSIONS[$metadata['mime_type']];
        $filename = bin2hex(random_bytes(24)) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($temporaryPath, $targetPath)) {
            throw new RuntimeException('Uploaded media could not be moved into private storage.');
        }

        @chmod($targetPath, 0600);

        $metadata['storage_key'] = $relativeDirectory . '/' . $filename;
        $metadata['file_size_bytes'] = (int) filesize($targetPath);
        $metadata['sha256'] = hash_file('sha256', $targetPath) ?: '';

        if ($metadata['sha256'] === '' || $metadata['file_size_bytes'] < 1) {
            @unlink($targetPath);
            throw new RuntimeException('Stored media integrity verification failed.');
        }

        return $metadata;
    }

    /**
     * Validate a local file without moving it; useful for tests and preflight.
     */
    public function inspectFile(string $path, string $originalName): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException('Media file is not readable.');
        }

        $size = filesize($path);
        if (!is_int($size) || $size < 1) {
            throw new InvalidArgumentException('Media file is empty or its size is unavailable.');
        }

        $maxBytes = $this->maxBytes();
        if ($size > $maxBytes) {
            throw new InvalidArgumentException('Media file exceeds the configured size limit.');
        }

        if (!class_exists('finfo')) {
            throw new RuntimeException('The fileinfo extension is required for media validation.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);
        if (!is_string($mimeType) || !isset(self::MIME_EXTENSIONS[$mimeType])) {
            throw new InvalidArgumentException('Unsupported media MIME type.');
        }

        $image = @getimagesize($path);
        if (
            !is_array($image) ||
            !isset($image[0], $image[1], $image['mime']) ||
            $image['mime'] !== $mimeType
        ) {
            throw new InvalidArgumentException('Image content does not match the detected MIME type.');
        }

        $width = (int) $image[0];
        $height = (int) $image[1];
        if (
            $width < 1 ||
            $height < 1 ||
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION ||
            ($width * $height) > self::MAX_PIXELS
        ) {
            throw new InvalidArgumentException('Image dimensions exceed the allowed limits.');
        }

        return [
            'storage_key' => '',
            'original_name' => $this->safeOriginalName($originalName),
            'mime_type' => $mimeType,
            'file_size_bytes' => $size,
            'sha256' => hash_file('sha256', $path) ?: '',
            'width' => $width,
            'height' => $height,
        ];
    }

    private function privateStorageRoot(): string
    {
        $configured = trim((string) (getenv('NIVAD_PRIVATE_STORAGE_PATH') ?: ''));
        if ($configured === '') {
            throw new RuntimeException('NIVAD_PRIVATE_STORAGE_PATH must be configured outside the web root.');
        }

        $isAbsolute = str_starts_with($configured, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $configured) === 1;
        if (!$isAbsolute) {
            throw new RuntimeException('Private media storage path must be absolute.');
        }

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $documentRootReal = is_string($documentRoot) && $documentRoot !== ''
            ? realpath($documentRoot)
            : false;

        if (is_string($documentRoot) && $documentRoot !== '' && $documentRootReal === false) {
            throw new RuntimeException('Document root could not be verified; refusing to initialize media storage.');
        }

        $normalized = $this->normalizePath($configured);
        if ($documentRootReal !== false) {
            $documentRootNormalized = rtrim($this->normalizePath($documentRootReal), '/');
            if (
                $normalized === $documentRootNormalized ||
                str_starts_with($normalized, $documentRootNormalized . '/')
            ) {
                throw new RuntimeException('Private media storage must be outside the web document root.');
            }
        }

        if (!is_dir($normalized) && !@mkdir($normalized, 0700, true) && !is_dir($normalized)) {
            throw new RuntimeException('Private media storage root could not be created.');
        }

        $realRoot = realpath($normalized);
        if ($realRoot === false) {
            throw new RuntimeException('Private media storage root is unavailable.');
        }

        $realRootNormalized = $this->normalizePath($realRoot);
        if ($documentRootReal !== false) {
            $documentRootNormalized = rtrim($this->normalizePath($documentRootReal), '/');
            if (
                $realRootNormalized === $documentRootNormalized ||
                str_starts_with($realRootNormalized, $documentRootNormalized . '/')
            ) {
                throw new RuntimeException('Resolved media storage path is inside the web root.');
            }
        }

        return $realRoot;
    }

    private function maxBytes(): int
    {
        $configured = getenv('NIVAD_MEDIA_MAX_BYTES');
        if ($configured === false || !ctype_digit((string) $configured)) {
            return self::DEFAULT_MAX_BYTES;
        }

        return max(1024, min(52428800, (int) $configured));
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\\\', '/', $name));
        $name = str_replace("\\0", '', $name);
        $name = preg_replace('/[\\x00-\\x1F\\x7F]+/u', '', $name) ?? '';
        $name = trim($name);

        if ($name === '') {
            return 'upload';
        }

        return mb_substr($name, 0, 255);
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = rtrim($path, '/');

        return PHP_OS_FAMILY === 'Windows'
            ? strtolower($path)
            : $path;
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded file is too large.',
            UPLOAD_ERR_PARTIAL => 'Uploaded file is incomplete.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload temporary directory is unavailable.',
            UPLOAD_ERR_CANT_WRITE => 'Uploaded file could not be written.',
            UPLOAD_ERR_EXTENSION => 'Upload was blocked by a server extension.',
            default => 'Unknown upload error.',
        };
    }
}
