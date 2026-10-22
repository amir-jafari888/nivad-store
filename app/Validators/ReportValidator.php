<?php

declare(strict_types=1);

final class ReportValidator implements ValidatorInterface
{
    public function validate(array $data): array
    {
        $errors = [];

        $fromDate = $this->nullableString($data['from_date'] ?? null);
        $toDate = $this->nullableString($data['to_date'] ?? null);
        $currency = $this->nullableString($data['currency'] ?? null);
        $status = $this->nullableString($data['status'] ?? null);

        $groupBy = trim(
            (string)(
                $data['group_by']
                ?? ReportContract::GROUP_DAY
            )
        );

        if (
            $fromDate !== null &&
            !$this->isValidDate($fromDate)
        ) {
            $errors['from_date'] =
                'تاریخ شروع گزارش نامعتبر است.';
        }

        if (
            $toDate !== null &&
            !$this->isValidDate($toDate)
        ) {
            $errors['to_date'] =
                'تاریخ پایان گزارش نامعتبر است.';
        }

        if (
            $fromDate !== null &&
            $toDate !== null &&
            $this->isValidDate($fromDate) &&
            $this->isValidDate($toDate) &&
            $toDate < $fromDate
        ) {
            $errors['to_date'] =
                'تاریخ پایان گزارش نمی‌تواند قبل از تاریخ شروع باشد.';
        }

        if (
            $currency !== null &&
            !preg_match(
                '/^[A-Z]{3}$/',
                strtoupper($currency)
            )
        ) {
            $errors['currency'] =
                'واحد پول گزارش نامعتبر است.';
        }

        if (
            $status !== null &&
            trim($status) === ''
        ) {
            $errors['status'] =
                'وضعیت گزارش نامعتبر است.';
        }

        if (
            !ReportContract::isGroupBy($groupBy)
        ) {
            $errors['group_by'] =
                'نوع گروه‌بندی گزارش نامعتبر است.';
        }

        if (
            array_key_exists('statuses', $data) &&
            !is_array($data['statuses'])
        ) {
            $errors['statuses'] =
                'فیلتر وضعیت‌ها باید آرایه باشد.';
        } elseif (
            isset($data['statuses'])
        ) {
            foreach (
                $data['statuses'] as $index => $value
            ) {
                if (
                    !is_string($value) ||
                    trim($value) === ''
                ) {
                    $errors['statuses.' . $index] =
                        'وضعیت گزارش نامعتبر است.';
                }
            }
        }

        return $errors;
    }

    public function isValidDataSource(
        string $source
    ): bool {
        return ReportContract::isDataSource(
            trim($source)
        );
    }

    public function isValidMetric(
        string $metric
    ): bool {
        return ReportContract::isMetric(
            trim($metric)
        );
    }

    public function normalizeCurrency(
        ?string $currency
    ): ?string {
        if ($currency === null) {
            return null;
        }

        $currency = strtoupper(
            trim($currency)
        );

        return $currency === ''
            ? null
            : $currency;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            (string)$value
        );

        return $value === ''
            ? null
            : $value;
    }

    private function isValidDate(
        string $value
    ): bool {
        $date =
            DateTime::createFromFormat(
                ReportContract::PERIOD_DATE_FORMAT,
                $value
            );

        return
            $date !== false &&
            $date->format(
                ReportContract::PERIOD_DATE_FORMAT
            ) === $value;
    }
}
