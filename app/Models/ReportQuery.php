<?php

declare(strict_types=1);

final class ReportQuery
{
    public ?string $fromDate;
    public ?string $toDate;
    public ?string $currency;
    public ?string $status;
    public array $statuses;
    public string $groupBy;

    public function __construct(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?string $currency = null,
        ?string $status = null,
        array $statuses = [],
        string $groupBy = ReportContract::GROUP_DAY
    ) {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->currency = $currency;
        $this->status = $status;
        $this->statuses = array_values($statuses);
        $this->groupBy = $groupBy;
    }

    public function hasDateRange(): bool
    {
        return $this->fromDate !== null
            || $this->toDate !== null;
    }

    public function hasCurrencyFilter(): bool
    {
        return $this->currency !== null
            && $this->currency !== '';
    }

    public function hasStatusFilter(): bool
    {
        return $this->status !== null
            && $this->status !== '';
    }

    public function hasStatusesFilter(): bool
    {
        return $this->statuses !== [];
    }

    public function startDateTime(): ?string
    {
        if ($this->fromDate === null) {
            return null;
        }

        return $this->fromDate . ' 00:00:00';
    }

    public function endDateTimeExclusive(): ?string
    {
        if ($this->toDate === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            ReportContract::PERIOD_DATE_FORMAT,
            $this->toDate
        );

        if ($date === false) {
            return null;
        }

        return $date
            ->setTime(0, 0, 0)
            ->modify('+1 day')
            ->format(ReportContract::DATETIME_FORMAT);
    }
}
