<?php

declare(strict_types=1);

final class Order
{
    public int $id;
    public int $userId;
    public string $orderNumber;
    public string $status;
    public string $currency;
    public string $subtotal;
    public string $discountAmount;
    public string $shippingAmount;
    public string $taxAmount;
    public string $totalAmount;

    public string $recipientName;
    public string $recipientPhone;
    public string $province;
    public string $city;
    public string $address;
    public string $postalCode;
    public ?string $buildingNumber;
    public ?string $unit;
    public ?string $latitude;
    public ?string $longitude;

    public ?string $customerNote;

    public ?string $placedAt;
    public ?string $confirmedAt;
    public ?string $shippedAt;
    public ?string $deliveredAt;
    public ?string $cancelledAt;

    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        int $id,
        int $userId,
        string $orderNumber,
        string $status,
        string $currency,
        string $subtotal,
        string $discountAmount,
        string $shippingAmount,
        string $taxAmount,
        string $totalAmount,
        string $recipientName,
        string $recipientPhone,
        string $province,
        string $city,
        string $address,
        string $postalCode,
        ?string $buildingNumber = null,
        ?string $unit = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $customerNote = null,
        ?string $placedAt = null,
        ?string $confirmedAt = null,
        ?string $shippedAt = null,
        ?string $deliveredAt = null,
        ?string $cancelledAt = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->orderNumber = $orderNumber;
        $this->status = $status;
        $this->currency = $currency;

        $this->subtotal = $subtotal;
        $this->discountAmount = $discountAmount;
        $this->shippingAmount = $shippingAmount;
        $this->taxAmount = $taxAmount;
        $this->totalAmount = $totalAmount;

        $this->recipientName = $recipientName;
        $this->recipientPhone = $recipientPhone;
        $this->province = $province;
        $this->city = $city;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->buildingNumber = $buildingNumber;
        $this->unit = $unit;
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        $this->customerNote = $customerNote;

        $this->placedAt = $placedAt;
        $this->confirmedAt = $confirmedAt;
        $this->shippedAt = $shippedAt;
        $this->deliveredAt = $deliveredAt;
        $this->cancelledAt = $cancelledAt;

        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['user_id'],
            (string)$data['order_number'],
            (string)$data['status'],
            (string)$data['currency'],
            (string)$data['subtotal'],
            (string)$data['discount_amount'],
            (string)$data['shipping_amount'],
            (string)$data['tax_amount'],
            (string)$data['total_amount'],
            (string)$data['recipient_name'],
            (string)$data['recipient_phone'],
            (string)$data['province'],
            (string)$data['city'],
            (string)$data['address'],
            (string)$data['postal_code'],
            isset($data['building_number'])
                ? (string)$data['building_number']
                : null,
            isset($data['unit'])
                ? (string)$data['unit']
                : null,
            isset($data['latitude'])
                ? (string)$data['latitude']
                : null,
            isset($data['longitude'])
                ? (string)$data['longitude']
                : null,
            isset($data['customer_note'])
                ? (string)$data['customer_note']
                : null,
            isset($data['placed_at'])
                ? (string)$data['placed_at']
                : null,
            isset($data['confirmed_at'])
                ? (string)$data['confirmed_at']
                : null,
            isset($data['shipped_at'])
                ? (string)$data['shipped_at']
                : null,
            isset($data['delivered_at'])
                ? (string)$data['delivered_at']
                : null,
            isset($data['cancelled_at'])
                ? (string)$data['cancelled_at']
                : null,
            isset($data['created_at'])
                ? (string)$data['created_at']
                : null,
            isset($data['updated_at'])
                ? (string)$data['updated_at']
                : null
        );
    }
}
