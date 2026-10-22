<?php

declare(strict_types=1);

final class SmartBundle
{
    public int $primaryProductId;
    public string $primaryProductName;
    public array $items;
    public string $originalTotal;
    public string $bundleTotal;
    public string $savingAmount;
    public string $savingPercentage;
    public string $currency;

    public function __construct(
        int $primaryProductId,
        string $primaryProductName,
        array $items,
        string $originalTotal,
        string $bundleTotal,
        string $savingAmount,
        string $savingPercentage,
        string $currency
    ) {
        $this->primaryProductId = $primaryProductId;
        $this->primaryProductName = $primaryProductName;
        $this->items = $items;
        $this->originalTotal = $originalTotal;
        $this->bundleTotal = $bundleTotal;
        $this->savingAmount = $savingAmount;
        $this->savingPercentage = $savingPercentage;
        $this->currency = $currency;
    }
}
