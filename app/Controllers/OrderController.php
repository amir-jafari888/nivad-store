<?php

declare(strict_types=1);

final class OrderController extends Controller
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

    private function ownedOrder(
        int $orderId,
        int $userId
    ): Order {
        $service = new OrderService();

        $order = $service->findById(
            $orderId
        );

        if ($order === null) {
            $this->error(
                'سفارش موردنظر پیدا نشد.',
                404
            );
        }

        if ($order->userId !== $userId) {
            $this->error(
                'دسترسی به این سفارش مجاز نیست.',
                403
            );
        }

        return $order;
    }

    public function store(): never
    {
        $userId =
            $this->currentUserId();

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $addressId =
            array_key_exists(
                'addressId',
                $data
            ) &&
            $data['addressId'] !== null &&
            $data['addressId'] !== ''
            ? (int)$data['addressId']
            : null;

        $shippingMethodId =
            array_key_exists(
                'shippingMethodId',
                $data
            ) &&
            $data['shippingMethodId'] !== null &&
            $data['shippingMethodId'] !== ''
            ? (int)$data['shippingMethodId']
            : null;

        $customerNote =
            array_key_exists(
                'customerNote',
                $data
            ) &&
            $data['customerNote'] !== null
            ? trim(
                (string)$data['customerNote']
            )
            : null;

        $shippingMethodId =
            array_key_exists(
                'shippingMethodId',
                $data
            ) &&
            $data['shippingMethodId'] !== null &&
            $data['shippingMethodId'] !== ''
            ? (int)$data['shippingMethodId']
            : null;

        $coupon = null;

        $couponId =
            array_key_exists(
                'couponId',
                $data
            ) &&
            $data['couponId'] !== null &&
            $data['couponId'] !== ''
            ? (int)$data['couponId']
            : null;

        if ($couponId !== null) {
            if ($couponId <= 0) {
                $this->error(
                    'شناسه کوپن نامعتبر است.',
                    422
                );
            }

            $coupon =
                (new CouponService())
                ->findById($couponId);

            if ($coupon === null) {
                $this->error(
                    'کوپن انتخاب‌شده پیدا نشد.',
                    422
                );
            }
        }

        try {
            $service =
                new OrderService();

            $order =
                $service->finalizeCheckout(
                    $userId,
                    $addressId,
                    $customerNote,
                    $coupon,
                    $shippingMethodId
                );

            (new AdminNotificationEventDispatcher())->dispatch('commerce.order.created', 'سفارش جدید ثبت شد', 'یک سفارش جدید در فروشگاه ثبت شده است.', 'order', is_array($order) ? ($order['id'] ?? null) : (is_object($order) ? ($order->id ?? null) : null));
            $this->success(
                $order,
                'سفارش با موفقیت ثبت شد.'
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

    public function index(): never
    {
        $userId = $this->currentUserId();

        $service = new OrderService();

        $orders = $service->findByUser(
            $userId
        );

        $this->success([
            'orders' => $orders,
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $orderId = (int)($parameters['id'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه سفارش نامعتبر است.',
                422
            );
        }

        $this->ownedOrder(
            $orderId,
            $userId
        );

        $service = new OrderService();

        $details = $service->details(
            $orderId
        );

        if ($details === null) {
            $this->error(
                'جزئیات سفارش پیدا نشد.',
                404
            );
        }

        $this->success($details);
    }

    public function adminIndex(): never
    {
        $service = new OrderService();

        $status = null;

        if (Request::isJson()) {
            $data = Request::json();

            $status = isset($data['status'])
                ? trim((string)$data['status'])
                : null;
        } elseif (isset($_GET['status'])) {
            $status = trim((string)$_GET['status']);
        }

        $orders =
            $status !== null && $status !== ''
            ? $service->findByStatus($status)
            : $service->findByStatus('pending');

        $this->success([
            'orders' => $orders,
            'status' =>
            $status !== null && $status !== ''
                ? $status
                : 'pending',
        ]);
    }

    public function adminShow(
        array $parameters = []
    ): never {
        $orderId =
            (int)($parameters['id'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه سفارش نامعتبر است.',
                422
            );
        }

        $service = new OrderService();

        $details =
            $service->details($orderId);

        if ($details === null) {
            $this->error(
                'سفارش موردنظر پیدا نشد.',
                404
            );
        }

        $this->success($details);
    }

    public function adminStatus(
        array $parameters = []
    ): never {
        $orderId =
            (int)($parameters['id'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه سفارش نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $toStatus =
            trim(
                (string)($data['status'] ?? '')
            );

        if ($toStatus === '') {
            $this->error(
                'وضعیت جدید سفارش الزامی است.',
                422
            );
        }

        $adminUserId =
            AdminAuth::id();

        if ($adminUserId === null) {
            $this->error(
                'نشست مدیر معتبر نیست.',
                401
            );
        }

        $note =
            array_key_exists('note', $data)
            ? trim((string)$data['note'])
            : null;

        try {
            $order =
                (new OrderService())->changeStatus(
                    $orderId,
                    $toStatus,
                    $adminUserId,
                    $note
                );
        } catch (InvalidArgumentException $exception) {
            $this->error(
                $exception->getMessage(),
                422
            );
        } catch (RuntimeException $exception) {
            $this->error(
                $exception->getMessage(),
                409
            );
        }

        (new AdminNotificationEventDispatcher())->dispatch('commerce.order.status_changed', 'وضعیت سفارش تغییر کرد', 'وضعیت یک سفارش توسط مدیر تغییر کرد.', 'order', $orderId, ['status' => $toStatus], $adminUserId);
        $this->success(
            $order,
            'وضعیت سفارش با موفقیت تغییر کرد.'
        );
    }
}
