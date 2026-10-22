<?php

declare(strict_types=1);

final class UserAddressController extends Controller
{
    private function currentUserId(): int
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        return $userId;
    }

    private function data(): array
    {
        return Request::isJson()
            ? Request::json()
            : $_POST;
    }

    private function addressData(
        array $data
    ): array {
        return [
            'title' =>
            $data['title'] ?? '',
            'recipient_name' =>
            $data['recipient_name'] ?? '',
            'phone' =>
            $data['phone'] ?? '',
            'province' =>
            $data['province'] ?? '',
            'city' =>
            $data['city'] ?? '',
            'address' =>
            $data['address'] ?? '',
            'postal_code' =>
            $data['postal_code'] ?? '',
            'building_number' =>
            $data['building_number'] ?? null,
            'unit' =>
            $data['unit'] ?? null,
            'latitude' =>
            $data['latitude'] ?? null,
            'longitude' =>
            $data['longitude'] ?? null,
            'is_default' =>
            !empty($data['is_default']),
        ];
    }

    private function serialize(
        UserAddress $address
    ): array {
        return [
            'id' => $address->id,
            'user_id' => $address->userId,
            'title' => $address->title,
            'recipient_name' =>
            $address->recipientName,
            'phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'address' => $address->address,
            'postal_code' =>
            $address->postalCode,
            'building_number' =>
            $address->buildingNumber,
            'unit' => $address->unit,
            'latitude' =>
            $address->latitude,
            'longitude' =>
            $address->longitude,
            'is_default' =>
            $address->isDefault,
            'created_at' =>
            $address->createdAt,
            'updated_at' =>
            $address->updatedAt,
        ];
    }

    public function index(): never
    {
        $userId =
            $this->currentUserId();

        $addresses =
            (new UserAddressService())
            ->findAllByUser($userId);

        $this->success([
            'addresses' =>
            array_map(
                fn(UserAddress $address): array =>
                $this->serialize($address),
                $addresses
            ),
        ]);
    }

    public function store(): never
    {
        $userId =
            $this->currentUserId();

        $data =
            $this->addressData(
                $this->data()
            );

        $errors =
            (new UserAddressValidator())
            ->validateForAccount($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات آدرس نامعتبر است.',
                422,
                $errors
            );
        }

        try {
            $address =
                (new UserAddressService())
                ->create(
                    $userId,
                    $data
                );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }

        $this->success(
            [
                'address' =>
                $this->serialize($address),
            ],
            'آدرس با موفقیت ثبت شد.'
        );
    }

    public function update(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه آدرس نامعتبر است.',
                422
            );
        }

        $data =
            $this->addressData(
                $this->data()
            );

        $errors =
            (new UserAddressValidator())
            ->validateForAccount($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات آدرس نامعتبر است.',
                422,
                $errors
            );
        }

        $address =
            (new UserAddressService())
            ->update(
                $id,
                $userId,
                $data
            );

        if ($address === null) {
            $this->error(
                'آدرس پیدا نشد.',
                404
            );
        }

        $this->success(
            [
                'address' =>
                $this->serialize($address),
            ],
            'آدرس با موفقیت بروزرسانی شد.'
        );
    }

    public function destroy(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه آدرس نامعتبر است.',
                422
            );
        }

        $deleted =
            (new UserAddressService())
            ->delete(
                $id,
                $userId
            );

        if (!$deleted) {
            $this->error(
                'آدرس پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'آدرس با موفقیت حذف شد.'
        );
    }

    public function setDefault(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه آدرس نامعتبر است.',
                422
            );
        }

        $address =
            (new UserAddressService())
            ->setDefault(
                $id,
                $userId
            );

        if ($address === null) {
            $this->error(
                'آدرس پیدا نشد.',
                404
            );
        }

        $this->success(
            [
                'address' =>
                $this->serialize($address),
            ],
            'آدرس پیش‌فرض تغییر کرد.'
        );
    }
}
