<?php

declare(strict_types=1);

final class UserAddress
{
    public int $id;
    public int $userId;
    public string $title;
    public string $recipientName;
    public string $phone;
    public string $province;
    public string $city;
    public string $address;
    public string $postalCode;
    public ?string $buildingNumber;
    public ?string $unit;
    public ?string $latitude;
    public ?string $longitude;
    public bool $isDefault;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;

    public function __construct(
        int $id,
        int $userId,
        string $title,
        string $recipientName,
        string $phone,
        string $province,
        string $city,
        string $address,
        string $postalCode,
        ?string $buildingNumber = null,
        ?string $unit = null,
        ?string $latitude = null,
        ?string $longitude = null,
        bool $isDefault = false,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->recipientName = $recipientName;
        $this->phone = $phone;
        $this->province = $province;
        $this->city = $city;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->buildingNumber = $buildingNumber;
        $this->unit = $unit;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->isDefault = $isDefault;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }
}
