<?php

declare(strict_types=1);

final class SliderValidator implements ValidatorInterface
{
    public function validate(
        array $data
    ): array {
        $errors = [];

        $name =
            trim(
                (string)(
                    $data['name']
                    ?? ''
                )
            );

        $slug =
            trim(
                (string)(
                    $data['slug']
                    ?? ''
                )
            );

        $description =
            isset($data['description'])
            ? trim(
                (string)$data['description']
            )
            : null;

        $status =
            trim(
                (string)(
                    $data['status']
                    ?? Slider::STATUS_ACTIVE
                )
            );

        $sortOrder =
            filter_var(
                $data['sort_order'] ?? 0,
                FILTER_VALIDATE_INT
            );

        $startsAt =
            isset($data['starts_at']) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $endsAt =
            isset($data['ends_at']) &&
            $data['ends_at'] !== ''
            ? trim(
                (string)$data['ends_at']
            )
            : null;

        if ($name === '') {
            $errors['name'] =
                'نام اسلایدر الزامی است.';
        }

        if ($slug === '') {
            $errors['slug'] =
                'نامک اسلایدر الزامی است.';
        } elseif (
            !preg_match(
                '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slug
            )
        ) {
            $errors['slug'] =
                'نامک اسلایدر نامعتبر است.';
        }

        if (
            $description !== null &&
            mb_strlen($description) > 500
        ) {
            $errors['description'] =
                'توضیحات اسلایدر نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.';
        }

        if (
            !in_array(
                $status,
                [
                    Slider::STATUS_ACTIVE,
                    Slider::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            $errors['status'] =
                'وضعیت اسلایدر نامعتبر است.';
        }

        if (
            $sortOrder === false ||
            $sortOrder < 0
        ) {
            $errors['sort_order'] =
                'ترتیب اسلایدر نامعتبر است.';
        }

        if (
            $startsAt !== null &&
            !$this->isValidDateTime($startsAt)
        ) {
            $errors['starts_at'] =
                'تاریخ شروع اسلایدر نامعتبر است.';
        }

        if (
            $endsAt !== null &&
            !$this->isValidDateTime($endsAt)
        ) {
            $errors['ends_at'] =
                'تاریخ پایان اسلایدر نامعتبر است.';
        }

        if (
            $startsAt !== null &&
            $endsAt !== null &&
            $this->isValidDateTime($startsAt) &&
            $this->isValidDateTime($endsAt) &&
            strtotime($endsAt) < strtotime($startsAt)
        ) {
            $errors['ends_at'] =
                'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.';
        }

        return $errors;
    }

    private function isValidDateTime(
        string $value
    ): bool {
        $date =
            DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $value
            );

        return
            $date !== false &&
            $date->format('Y-m-d H:i:s') === $value;
    }
}
