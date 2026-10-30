<?php

declare(strict_types=1);

final class AdminContentService
{
    private AdminContentRepository $repository;

    public function __construct(?AdminContentRepository $repository = null)
    {
        $this->repository = $repository ?? new AdminContentRepository();
    }

    public function pages(): array
    {
        return $this->repository->pages();
    }

    public function sections(string $pageKey, bool $includeDeleted = false): array
    {
        AdminContentRegistry::page($pageKey);
        return $this->repository->sections($pageKey, $includeDeleted);
    }

    public function create(string $pageKey, array $input, int $adminId): int
    {
        AdminContentRegistry::page($pageKey);
        $type = AdminContentRegistry::sectionType((string)($input['section_type'] ?? 'custom'));
        $title = $this->text((string)($input['title'] ?? ''), 160, 'عنوان سکشن');
        $content = AdminContentRegistry::validateContent((array)($input['content'] ?? []));
        $this->validateMediaReferences($content);
        $style = AdminContentRegistry::validateStyle((array)($input['style'] ?? []));
        $key = strtolower(trim((string)($input['section_key'] ?? '')));
        if ($key === '') $key = $type . '-' . bin2hex(random_bytes(5));
        if (!preg_match('/^[a-z][a-z0-9_-]{1,79}$/', $key)) throw new InvalidArgumentException('کلید سکشن معتبر نیست.');
        $id = $this->repository->createSection($pageKey, $key, $type, $title, $content, $style, $adminId);
        $this->repository->saveRevision($id, ['title' => $title, 'content' => $content, 'style' => $style, 'is_visible' => true], $adminId);
        return $id;
    }

    public function update(int $id, array $input, int $adminId): bool
    {
        $before = $this->repository->section($id);
        if (!$before) return false;
        $title = $this->text((string)($input['title'] ?? $before['title']), 160, 'عنوان سکشن');
        $content = AdminContentRegistry::validateContent((array)($input['content'] ?? $before['content']));
        $this->validateMediaReferences($content);
        $style = AdminContentRegistry::validateStyle((array)($input['style'] ?? $before['style']));
        $visible = array_key_exists('is_visible', $input) ? (bool)$input['is_visible'] : (bool)$before['is_visible'];
        $updated = $this->repository->updateSection($id, $title, $content, $style, $visible, $adminId);
        if ($updated) $this->repository->saveRevision($id, ['title' => $title, 'content' => $content, 'style' => $style, 'is_visible' => $visible], $adminId);
        return $updated;
    }

    public function publish(int $id, int $adminId): bool
    {
        if (!$this->repository->section($id)) return false;
        return $this->repository->publishSection($id, $adminId);
    }

    public function schedule(int $id, string $dateTime, int $adminId): bool
    {
        try { $date = new DateTimeImmutable($dateTime, new DateTimeZone('UTC')); }
        catch (Throwable $exception) { throw new InvalidArgumentException('زمان انتشار معتبر نیست.'); }
        if ($date->getTimestamp() <= time()) throw new InvalidArgumentException('زمان انتشار باید در آینده باشد.');
        return $this->repository->scheduleSection($id, $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), $adminId);
    }

    public function delete(int $id, int $adminId): bool
    {
        $section = $this->repository->section($id);
        if (!$section) return false;
        $this->repository->saveRevision($id, ['title' => $section['title'], 'content' => $section['content'], 'style' => $section['style'], 'is_visible' => (bool)$section['is_visible']], $adminId);
        return $this->repository->softDeleteSection($id, $adminId);
    }

    public function restoreDeleted(int $id, int $adminId): bool
    {
        return $this->repository->restoreSection($id, $adminId);
    }

    public function reorder(string $pageKey, array $ids, int $adminId): void
    {
        AdminContentRegistry::page($pageKey);
        if (count($ids) > 100 || count(array_unique(array_map('intval', $ids))) !== count($ids)) throw new InvalidArgumentException('فهرست ترتیب سکشن‌ها نامعتبر است.');
        $this->repository->reorder($pageKey, array_map('intval', $ids), $adminId);
    }

    public function revisions(int $id): array
    {
        if (!$this->repository->section($id, true)) throw new InvalidArgumentException('سکشن پیدا نشد.');
        return $this->repository->revisions($id);
    }

    public function restoreRevision(int $id, int $revisionId, int $adminId): bool
    {
        if (!$this->repository->section($id)) return false;
        $before = $this->repository->section($id);
        $ok = $this->repository->restoreRevision($id, $revisionId, $adminId);
        if ($ok) $this->repository->saveRevision($id, ['title' => $before['title'], 'content' => $before['content'], 'style' => $before['style'], 'is_visible' => (bool)$before['is_visible']], $adminId);
        return $ok;
    }

    public function media(): array
    {
        return $this->repository->media();
    }

    public function uploadMedia(array $upload, ?string $alt, int $adminId): int
    {
        $storage = (new AdminMediaStorageService())->storeUploadedFile($upload);
        return $this->repository->createMedia($storage, $alt, $adminId);
    }

    public function updateMedia(int $id, ?string $alt, int $adminId): bool
    {
        if ($alt !== null && mb_strlen($alt) > 500) throw new InvalidArgumentException('متن جایگزین تصویر بیش از حد مجاز است.');
        return $this->repository->updateMedia($id, $alt, $adminId);
    }

    public function deleteMedia(int $id, int $adminId): bool
    {
        if ($this->repository->isMediaReferenced($id)) throw new InvalidArgumentException('این تصویر هنوز در محتوای پیش‌نویس یا منتشرشده استفاده می‌شود.');
        return $this->repository->deleteMedia($id, $adminId);
    }

    public function mediaFile(int $id): ?array
    {
        return $this->repository->publishedMedia($id);
    }

    public function publishDue(): int
    {
        return $this->repository->publishDueSections();
    }

    private function validateMediaReferences(array $content): void
    {
        $walk = function (array $node) use (&$walk): void {
            foreach ($node as $key => $value) {
                if ($key === 'image_id' && $value !== null && $value !== '') {
                    $id = filter_var($value, FILTER_VALIDATE_INT);
                    if ($id === false || $id < 1 || $this->repository->mediaById((int)$id) === null) {
                        throw new InvalidArgumentException('تصویر انتخاب‌شده در کتابخانه رسانه فعال نیست.');
                    }
                } elseif (is_array($value)) {
                    $walk($value);
                }
            }
        };
        $walk($content);
    }

    private function text(string $value, int $max, string $label): string
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > $max) throw new InvalidArgumentException($label . ' خالی یا بیش از حد مجاز است.');
        return $value;
    }
}
