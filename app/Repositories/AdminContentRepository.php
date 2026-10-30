<?php

declare(strict_types=1);

final class AdminContentRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function pages(): array
    {
        return $this->db->query('SELECT id, page_key, title, route_path, sort_order, status FROM admin_cms_pages WHERE deleted_at IS NULL ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sections(string $pageKey, bool $includeDeleted = false): array
    {
        $sql = 'SELECT s.*, p.page_key FROM admin_cms_sections s INNER JOIN admin_cms_pages p ON p.id = s.page_id WHERE p.page_key = :page_key';
        if (!$includeDeleted) $sql .= ' AND s.deleted_at IS NULL';
        $sql .= ' ORDER BY s.sort_order, s.id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['page_key' => $pageKey]);
        return array_map([$this, 'decodeSection'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function section(int $id, bool $includeDeleted = false): ?array
    {
        $sql = 'SELECT s.*, p.page_key FROM admin_cms_sections s INNER JOIN admin_cms_pages p ON p.id = s.page_id WHERE s.id = :id';
        if (!$includeDeleted) $sql .= ' AND s.deleted_at IS NULL';
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decodeSection($row) : null;
    }

    public function createSection(string $pageKey, string $sectionKey, string $type, string $title, array $content, array $style, int $adminId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_cms_sections (page_id, section_key, section_type, title, sort_order, status, is_visible, content_json, style_json, created_by_admin_id, updated_by_admin_id)
             SELECT id, :section_key, :section_type, :title,
                    COALESCE((SELECT MAX(sort_order) + 10 FROM admin_cms_sections WHERE page_id = admin_cms_pages.id AND deleted_at IS NULL), 10),
                    "draft", 1, :content_json, :style_json, :created_by, :updated_by
             FROM admin_cms_pages WHERE page_key = :page_key AND deleted_at IS NULL'
        );
        $stmt->execute([
            'section_key' => $sectionKey, 'section_type' => $type, 'title' => $title,
            'content_json' => $this->json($content), 'style_json' => $this->json($style),
            'created_by' => $adminId, 'updated_by' => $adminId, 'page_key' => $pageKey,
        ]);
        if ($stmt->rowCount() !== 1) throw new RuntimeException('صفحه مقصد برای ایجاد سکشن پیدا نشد.');
        return (int)$this->db->lastInsertId();
    }

    public function updateSection(int $id, string $title, array $content, array $style, bool $visible, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_cms_sections SET title = :title, content_json = :content_json, style_json = :style_json,
             is_visible = :visible, status = "draft", updated_by_admin_id = :admin_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['title' => $title, 'content_json' => $this->json($content), 'style_json' => $this->json($style), 'visible' => $visible ? 1 : 0, 'admin_id' => $adminId, 'id' => $id]);
        return $stmt->rowCount() > 0 || $this->section($id) !== null;
    }

    public function publishSection(int $id, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_cms_sections SET published_content_json = content_json, published_style_json = style_json,
             published_is_visible = is_visible, published_sort_order = sort_order,
             status = "published", published_at = CURRENT_TIMESTAMP, scheduled_at = NULL,
             updated_by_admin_id = :admin_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['admin_id' => $adminId, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function scheduleSection(int $id, string $scheduledAt, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_cms_sections SET status = "scheduled", scheduled_at = :scheduled_at,
             updated_by_admin_id = :admin_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['scheduled_at' => $scheduledAt, 'admin_id' => $adminId, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function publishDueSections(): int
    {
        $stmt = $this->db->prepare(
            'UPDATE admin_cms_sections
             SET published_content_json = content_json,
                 published_style_json = style_json,
                 published_is_visible = is_visible,
                 published_sort_order = sort_order,
                 status = "published",
                 published_at = UTC_TIMESTAMP(),
                 scheduled_at = NULL,
                 updated_at = UTC_TIMESTAMP()
             WHERE status = "scheduled"
               AND scheduled_at IS NOT NULL
               AND scheduled_at <= UTC_TIMESTAMP()
               AND deleted_at IS NULL'
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function dueSections(): array
    {
        return $this->db->query(
            'SELECT id FROM admin_cms_sections WHERE status = "scheduled" AND scheduled_at IS NOT NULL
             AND scheduled_at <= UTC_TIMESTAMP() AND deleted_at IS NULL ORDER BY scheduled_at, id LIMIT 100'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function softDeleteSection(int $id, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_cms_sections SET deleted_at = CURRENT_TIMESTAMP, status = "archived", updated_by_admin_id = :admin_id WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['admin_id' => $adminId, 'id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function restoreSection(int $id, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_cms_sections SET deleted_at = NULL, status = "draft", updated_by_admin_id = :admin_id WHERE id = :id AND deleted_at IS NOT NULL');
        $stmt->execute(['admin_id' => $adminId, 'id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function reorder(string $pageKey, array $orderedIds, int $adminId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE admin_cms_sections s INNER JOIN admin_cms_pages p ON p.id = s.page_id SET s.sort_order = :sort_order, s.updated_by_admin_id = :admin_id WHERE s.id = :id AND p.page_key = :page_key AND s.deleted_at IS NULL');
            foreach ($orderedIds as $index => $id) {
                $stmt->execute(['sort_order' => ($index + 1) * 10, 'admin_id' => $adminId, 'id' => (int)$id, 'page_key' => $pageKey]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function revisions(int $sectionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, version_number, status, snapshot_json, created_by_admin_id, created_at
             FROM admin_content_revisions WHERE entity_type = "cms_section" AND entity_id = :entity_id
             ORDER BY version_number DESC LIMIT 100'
        );
        $stmt->execute(['entity_id' => (string)$sectionId]);
        return array_map(static function (array $row): array {
            $row['snapshot'] = json_decode((string)$row['snapshot_json'], true) ?: [];
            unset($row['snapshot_json']);
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function saveRevision(int $sectionId, array $snapshot, int $adminId): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT COALESCE(MAX(version_number), 0) + 1 FROM admin_content_revisions WHERE entity_type = "cms_section" AND entity_id = :entity_id FOR UPDATE');
            $stmt->execute(['entity_id' => (string)$sectionId]);
            $version = max(1, (int)$stmt->fetchColumn());
            $insert = $this->db->prepare(
                'INSERT INTO admin_content_revisions (entity_type, entity_id, version_number, status, snapshot_json, created_by_admin_id)
                 VALUES ("cms_section", :entity_id, :version, "draft", :snapshot, :admin_id)'
            );
            $insert->execute(['entity_id' => (string)$sectionId, 'version' => $version, 'snapshot' => $this->json($snapshot), 'admin_id' => $adminId]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function restoreRevision(int $sectionId, int $revisionId, int $adminId): bool
    {
        $stmt = $this->db->prepare('SELECT snapshot_json FROM admin_content_revisions WHERE id = :revision_id AND entity_type = "cms_section" AND entity_id = :entity_id LIMIT 1');
        $stmt->execute(['revision_id' => $revisionId, 'entity_id' => (string)$sectionId]);
        $json = $stmt->fetchColumn();
        if (!is_string($json)) return false;
        $snapshot = json_decode($json, true);
        if (!is_array($snapshot)) throw new RuntimeException('نسخه ذخیره‌شده قابل بازیابی نیست.');
        $this->db->beginTransaction();
        try {
            $update = $this->db->prepare('UPDATE admin_cms_sections SET title = :title, content_json = :content, style_json = :style, is_visible = :visible, sort_order = :sort_order, status = "draft", updated_by_admin_id = :admin_id WHERE id = :id AND deleted_at IS NULL');
            $update->execute(['title' => (string)($snapshot['title'] ?? ''), 'content' => $this->json((array)($snapshot['content'] ?? [])), 'style' => $this->json((array)($snapshot['style'] ?? [])), 'visible' => !empty($snapshot['is_visible']) ? 1 : 0, 'sort_order' => (int)($snapshot['sort_order'] ?? 10), 'admin_id' => $adminId, 'id' => $sectionId]);
            $this->db->commit();
            return $update->rowCount() > 0;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function media(int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->db->query('SELECT id, original_name, mime_type, file_size_bytes, sha256, alt_text, status, uploaded_by_admin_id, created_at FROM admin_media_assets WHERE deleted_at IS NULL ORDER BY id DESC LIMIT ' . $limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mediaById(int $id, bool $activeOnly = true): ?array
    {
        $sql = 'SELECT * FROM admin_media_assets WHERE id = :id';
        if ($activeOnly) $sql .= ' AND deleted_at IS NULL AND status = "active"';
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createMedia(array $meta, ?string $alt, int $adminId): int
    {
        $stmt = $this->db->prepare('INSERT INTO admin_media_assets (storage_key, original_name, mime_type, file_size_bytes, sha256, alt_text, status, uploaded_by_admin_id) VALUES (:storage_key, :original_name, :mime_type, :size, :sha, :alt, "active", :admin_id)');
        $stmt->execute(['storage_key' => $meta['storage_key'], 'original_name' => $meta['original_name'], 'mime_type' => $meta['mime_type'], 'size' => $meta['file_size_bytes'], 'sha' => $meta['sha256'], 'alt' => $alt, 'admin_id' => $adminId]);
        return (int)$this->db->lastInsertId();
    }

    public function updateMedia(int $id, ?string $alt, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_media_assets SET alt_text = :alt WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['alt' => $alt, 'id' => $id]);
        return $stmt->rowCount() > 0 || $this->mediaById($id) !== null;
    }

    public function deleteMedia(int $id, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_media_assets SET status = "deleted", deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function isMediaReferenced(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM admin_cms_sections WHERE deleted_at IS NULL
             AND (JSON_SEARCH(content_json, "one", :id) IS NOT NULL OR JSON_SEARCH(published_content_json, "one", :id) IS NOT NULL) LIMIT 1'
        );
        $stmt->execute(['id' => (string)$id]);
        if ($stmt->fetchColumn() !== false) return true;
        $history = $this->db->prepare('SELECT 1 FROM admin_content_revisions WHERE entity_type = "cms_section" AND JSON_SEARCH(snapshot_json, "one", :id) IS NOT NULL LIMIT 1');
        $history->execute(['id' => (string)$id]);
        return $history->fetchColumn() !== false;
    }

    public function publishedMedia(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT m.* FROM admin_media_assets m
             WHERE m.id = :id AND m.status = "active" AND m.deleted_at IS NULL
             AND EXISTS (
                SELECT 1 FROM admin_cms_sections s
                WHERE s.deleted_at IS NULL AND COALESCE(s.published_is_visible, 1) = 1
                AND s.published_content_json IS NOT NULL
                AND JSON_SEARCH(s.published_content_json, "one", :media_id) IS NOT NULL
             ) LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'media_id' => (string)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function decodeSection(array $row): array
    {
        $row['has_published_version'] = $row['published_content_json'] !== null;
        $row['content'] = json_decode((string)$row['content_json'], true) ?: [];
        $row['style'] = json_decode((string)$row['style_json'], true) ?: [];
        $row['published_content'] = json_decode((string)($row['published_content_json'] ?? ''), true) ?: [];
        $row['published_style'] = json_decode((string)($row['published_style_json'] ?? ''), true) ?: [];
        $row['published_is_visible'] = $row['published_is_visible'] === null ? null : (bool)$row['published_is_visible'];
        unset($row['content_json'], $row['style_json'], $row['published_content_json'], $row['published_style_json']);
        return $row;
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
