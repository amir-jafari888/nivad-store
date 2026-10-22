<?php

declare(strict_types=1);

final class AdminRole
{
    public int $id;
    public string $name;
    public ?string $description;
    public bool $isActive;

    public function __construct(
        int $id,
        string $name,
        ?string $description,
        bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
    }
}
