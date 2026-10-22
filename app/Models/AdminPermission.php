<?php

declare(strict_types=1);

final class AdminPermission
{
    public int $id;
    public string $name;
    public ?string $description;

    public function __construct(
        int $id,
        string $name,
        ?string $description
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }
}
