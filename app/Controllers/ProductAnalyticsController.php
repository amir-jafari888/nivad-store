<?php

declare(strict_types=1);

final class ProductAnalyticsController extends Controller
{
    public function store(): never
    {
        $input = Request::isJson() ? Request::json() : $_POST;
        try {
            $id = (new AdminCapabilityService())->recordProductEvent($input);
            $this->success(['event_id' => $id], 'رویداد محصول ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
