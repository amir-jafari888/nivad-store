<?php

declare(strict_types=1);

final class SmartBundleController extends Controller
{
    private SmartBundleService $service;

    public function __construct(?SmartBundleService $service = null)
    {
        $this->service = $service ?? new SmartBundleService();
    }

    public function index(): never
    {
        $productId = Request::query('product_id');
        $productId = is_numeric($productId)
            ? (int)$productId
            : null;

        $bundle = $this->service->findBundle($productId);

        if ($bundle === null) {
            $this->success([
                'bundle' => null,
                'count' => 0,
            ], 'در حال حاضر ست هوشمندی برای نمایش وجود ندارد.');
        }

        $this->success([
            'bundle' => [
                'primary_product_id' => $bundle->primaryProductId,
                'primary_product_name' => $bundle->primaryProductName,
                'items' => $bundle->items,
                'original_total' => $bundle->originalTotal,
                'bundle_total' => $bundle->bundleTotal,
                'saving_amount' => $bundle->savingAmount,
                'saving_percentage' => $bundle->savingPercentage,
                'currency' => $bundle->currency,
            ],
            'count' => count($bundle->items),
        ], 'ست پیشنهادی هوشمند دریافت شد.');
    }
}
