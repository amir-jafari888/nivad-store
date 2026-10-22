<?php

declare(strict_types=1);

final class ShippingController extends Controller
{

    public function checkoutMethods(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        $addressId =
            (int)(
                Request::query('address_id')
                ?? 0
            );

        if ($addressId <= 0) {
            $this->error(
                'شناسه آدرس نامعتبر است.',
                422
            );
        }

        try {
            $checkout =
                (new CheckoutService())
                ->review(
                    $userId,
                    $addressId
                );

            $this->success(
                $checkout['shipping'],
                'روش‌های ارسال دریافت شد.'
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        } catch (
            RuntimeException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                409
            );
        }
    }

    public function show(
        array $parameters = []
    ): never {
        $shipmentId =
            (int)($parameters['id'] ?? 0);

        if ($shipmentId <= 0) {
            $this->error(
                'شناسه مرسوله نامعتبر است.',
                422
            );
        }

        $service =
            new ShippingService();

        $shipment =
            $service->findShipmentById(
                $shipmentId
            );

        if ($shipment === null) {
            $this->error(
                'مرسوله موردنظر پیدا نشد.',
                404
            );
        }

        $items =
            $service->findShipmentItems(
                $shipmentId
            );

        $this->success([
            'shipment' => $shipment,
            'items' => $items,
        ]);
    }

    public function byOrder(
        array $parameters = []
    ): never {
        $orderId =
            (int)($parameters['orderId'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه سفارش نامعتبر است.',
                422
            );
        }

        $service =
            new ShippingService();

        $shipments =
            $service->findShipmentsByOrderId(
                $orderId
            );

        $this->success([
            'shipments' => $shipments,
        ]);
    }
}
