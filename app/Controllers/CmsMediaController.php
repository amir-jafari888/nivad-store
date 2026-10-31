<?php

declare(strict_types=1);

final class CmsMediaController extends Controller
{
    public function show(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        $asset = (new AdminContentService())->mediaFile($id);
        $this->stream($asset, $id);
    }

    public function adminShow(array $parameters = []): never
    {
        $id = (int)($parameters['id'] ?? 0);
        $asset = (new AdminContentRepository())->mediaById($id);
        $this->stream($asset, $id);
    }

    private function stream(?array $asset, int $id): never
    {
        if (!$asset) {
            http_response_code(404);
            exit;
        }
        $root = trim((string)(getenv('NIVAD_PRIVATE_STORAGE_PATH') ?: ''));
        $path = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string)$asset['storage_key']);
        $realRoot = realpath($root);
        $realPath = realpath($path);
        if ($realRoot === false || $realPath === false || !str_starts_with($realPath, rtrim($realRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $asset['mime_type']);
        header('Content-Length: ' . (string)filesize($realPath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=3600');
        header('Content-Disposition: inline; filename="media-' . $id . '"');
        readfile($realPath);
        exit;
    }
}
