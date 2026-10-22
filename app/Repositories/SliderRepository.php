<?php

declare(strict_types=1);

final class SliderRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findById(int $sliderId): ?Slider
    {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $sliderId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateSlider($row)
            : null;
    }

    public function findBySlug(string $slug): ?Slider
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateSlider($row)
            : null;
    }

    public function findActiveById(int $sliderId): ?Slider
    {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
               AND (
                    starts_at IS NULL
                    OR starts_at <= NOW()
               )
               AND (
                    ends_at IS NULL
                    OR ends_at >= NOW()
               )
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $sliderId,
            'status' => Slider::STATUS_ACTIVE,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateSlider($row)
            : null;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE status = ' .
                $this->db->quote(Slider::STATUS_ACTIVE) . '
               AND deleted_at IS NULL
               AND (
                    starts_at IS NULL
                    OR starts_at <= NOW()
               )
               AND (
                    ends_at IS NULL
                    OR ends_at >= NOW()
               )
             ORDER BY sort_order ASC, id ASC'
        );

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): Slider =>
            $this->hydrateSlider($row),
            $rows
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): Slider =>
            $this->hydrateSlider($row),
            $rows
        );
    }

    public function findDeletedById(
        int $sliderId
    ): ?Slider {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM sliders
             WHERE id = :id
               AND deleted_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $sliderId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateSlider($row)
            : null;
    }

    public function create(
        string $name,
        string $slug,
        ?string $description = null,
        string $status = Slider::STATUS_ACTIVE,
        int $sortOrder = 0,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): ?Slider {
        $stmt = $this->db->prepare(
            'INSERT INTO sliders (
                name,
                slug,
                description,
                status,
                sort_order,
                starts_at,
                ends_at
             ) VALUES (
                :name,
                :slug,
                :description,
                :status,
                :sort_order,
                :starts_at,
                :ends_at
             )'
        );

        $success = $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        if (!$success) {
            return null;
        }

        return $this->findById(
            (int) $this->db->lastInsertId()
        );
    }

    public function update(
        int $sliderId,
        string $name,
        string $slug,
        ?string $description = null,
        string $status = Slider::STATUS_ACTIVE,
        int $sortOrder = 0,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): ?Slider {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE sliders
             SET
                name = :name,
                slug = :slug,
                description = :description,
                status = :status,
                sort_order = :sort_order,
                starts_at = :starts_at,
                ends_at = :ends_at
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $success = $stmt->execute([
            'id' => $sliderId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        if (!$success) {
            return null;
        }

        return $this->findById($sliderId);
    }

    public function delete(int $sliderId): bool
    {
        if ($sliderId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE sliders
             SET deleted_at = NOW()
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        return $stmt->execute([
            'id' => $sliderId,
        ]);
    }

    public function restore(int $sliderId): ?Slider
    {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE sliders
             SET deleted_at = NULL
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $success = $stmt->execute([
            'id' => $sliderId,
        ]);

        if (!$success) {
            return null;
        }

        return $this->findById($sliderId);
    }

    public function updateStatus(
        int $sliderId,
        string $status
    ): ?Slider {
        if ($sliderId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE sliders
             SET status = :status
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $success = $stmt->execute([
            'id' => $sliderId,
            'status' => $status,
        ]);

        if (!$success) {
            return null;
        }

        return $this->findById($sliderId);
    }

    public function findSlidesBySliderId(
        int $sliderId
    ): array {
        if ($sliderId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                slider_id,
                title,
                subtitle,
                image_path,
                mobile_image_path,
                link_url,
                link_target,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM slides
             WHERE slider_id = :slider_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'slider_id' => $sliderId,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): Slide =>
            $this->hydrateSlide($row),
            $rows
        );
    }

    public function findActiveSlidesBySliderId(
        int $sliderId
    ): array {
        if ($sliderId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                slider_id,
                title,
                subtitle,
                image_path,
                mobile_image_path,
                link_url,
                link_target,
                status,
                sort_order,
                starts_at,
                ends_at,
                created_at,
                updated_at,
                deleted_at
             FROM slides
             WHERE slider_id = :slider_id
               AND status = :status
               AND deleted_at IS NULL
               AND (
                    starts_at IS NULL
                    OR starts_at <= NOW()
               )
               AND (
                    ends_at IS NULL
                    OR ends_at >= NOW()
               )
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'slider_id' => $sliderId,
            'status' => Slide::STATUS_ACTIVE,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): Slide =>
            $this->hydrateSlide($row),
            $rows
        );
    }

    private function hydrateSlider(array $data): Slider
    {
        return new Slider(
            (int) $data['id'],
            (string) $data['name'],
            (string) $data['slug'],
            $data['description'] !== null
                ? (string) $data['description']
                : null,
            (string) $data['status'],
            (int) $data['sort_order'],
            $data['starts_at'] !== null
                ? (string) $data['starts_at']
                : null,
            $data['ends_at'] !== null
                ? (string) $data['ends_at']
                : null,
            $data['created_at'] !== null
                ? (string) $data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string) $data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string) $data['deleted_at']
                : null
        );
    }

    private function hydrateSlide(array $data): Slide
    {
        return new Slide(
            (int) $data['id'],
            (int) $data['slider_id'],
            $data['title'] !== null
                ? (string) $data['title']
                : null,
            $data['subtitle'] !== null
                ? (string) $data['subtitle']
                : null,
            (string) $data['image_path'],
            $data['mobile_image_path'] !== null
                ? (string) $data['mobile_image_path']
                : null,
            $data['link_url'] !== null
                ? (string) $data['link_url']
                : null,
            (string) $data['link_target'],
            (string) $data['status'],
            (int) $data['sort_order'],
            $data['starts_at'] !== null
                ? (string) $data['starts_at']
                : null,
            $data['ends_at'] !== null
                ? (string) $data['ends_at']
                : null,
            $data['created_at'] !== null
                ? (string) $data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string) $data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string) $data['deleted_at']
                : null
        );
    }
}
