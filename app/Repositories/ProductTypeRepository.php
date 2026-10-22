<?php

declare(strict_types=1);
final class ProductTypeRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(int $id): ?ProductType
    {
        if ($id < 1) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT id,name,slug,description,status,sort_order,created_at,updated_at,deleted_at FROM product_types WHERE id=:id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $this->hydrate($row) : null;
    }

    public function findBySlug(string $slug): ?ProductType
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        $stmt = $this->db->prepare('SELECT id,name,slug,description,status,sort_order,created_at,updated_at,deleted_at FROM product_types WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $this->hydrate($row) : null;
    }

    public function exists(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $stmt = $this->db->prepare('SELECT id FROM product_types WHERE id=:id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() !== false;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT id,name,slug,description,status,sort_order,created_at,updated_at,deleted_at FROM product_types WHERE deleted_at IS NULL ORDER BY id DESC');
        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }
        return $items;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query("SELECT id,name,slug,description,status,sort_order,created_at,updated_at,deleted_at FROM product_types WHERE deleted_at IS NULL AND status='active' ORDER BY sort_order ASC,id DESC");
        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrate($row);
        }
        return $items;
    }

    public function create(string $name, string $slug, ?string $description, string $status, int $sortOrder): ProductType
    {
        $stmt = $this->db->prepare('INSERT INTO product_types(name,slug,description,status,sort_order) VALUES(:name,:slug,:description,:status,:sort_order)');
        $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'status' => $status, 'sort_order' => $sortOrder]);
        $item = $this->findById((int)$this->db->lastInsertId());
        if (!$item instanceof ProductType) {
            throw new RuntimeException('Product type could not be loaded after creation.');
        }
        return $item;
    }

    public function update(int $id, string $name, string $slug, ?string $description, string $status, int $sortOrder): ?ProductType
    {
        if ($id < 1) {
            return null;
        }
        $stmt = $this->db->prepare('UPDATE product_types SET name=:name,slug=:slug,description=:description,status=:status,sort_order=:sort_order,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id, 'name' => $name, 'slug' => $slug, 'description' => $description, 'status' => $status, 'sort_order' => $sortOrder]);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE product_types SET deleted_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE product_types SET deleted_at=NULL WHERE id=:id AND deleted_at IS NOT NULL');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function setStatus(int $id, string $status): ?ProductType
    {
        if ($id < 1) {
            return null;
        }
        $stmt = $this->db->prepare('UPDATE product_types SET status=:status,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id, 'status' => $status]);
        return $this->findById($id);
    }

    private function hydrate(array $data): ProductType
    {
        return new ProductType(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            $data['description'] !== null ? (string)$data['description'] : null,
            (string)$data['status'],
            (int)$data['sort_order'],
            $data['created_at'] !== null ? (string)$data['created_at'] : null,
            $data['updated_at'] !== null ? (string)$data['updated_at'] : null,
            $data['deleted_at'] !== null ? (string)$data['deleted_at'] : null
        );
    }
}
