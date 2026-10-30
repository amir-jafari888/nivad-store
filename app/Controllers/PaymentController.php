<?php

declare(strict_types=1);

final class PaymentController extends Controller
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

    private function ownedAttempt(
        int $attemptId,
        int $userId
    ): PaymentAttempt {
        if ($attemptId <= 0) {
            $this->error(
                'شناسه تلاش پرداخت نامعتبر است.',
                422
            );
        }

        $service = new PaymentService();

        $attempt = $service->findAttemptById(
            $attemptId
        );

        if ($attempt === null) {
            $this->error(
                'تلاش پرداخت موردنظر پیدا نشد.',
                404
            );
        }

        if (
            $attempt->userId !== null &&
            $attempt->userId !== $userId
        ) {
            $this->error(
                'دسترسی به این تلاش پرداخت مجاز نیست.',
                403
            );
        }

        $orderRepository = new OrderRepository();

        $order = $orderRepository->findById(
            $attempt->orderId
        );

        if ($order === null) {
            $this->error(
                'سفارش مربوط به این پرداخت پیدا نشد.',
                404
            );
        }

        if ($order->userId !== $userId) {
            $this->error(
                'دسترسی به سفارش این پرداخت مجاز نیست.',
                403
            );
        }

        return $attempt;
    }

    public function show(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $attemptId =
            (int)($parameters['id'] ?? 0);

        $attempt =
            $this->ownedAttempt(
                $attemptId,
                $userId
            );

        $this->success(
            $attempt,
            'اطلاعات تلاش پرداخت دریافت شد.'
        );
    }

    public function byOrder(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $orderId =
            (int)($parameters['orderId'] ?? 0);

        if ($orderId <= 0) {
            $this->error(
                'شناسه سفارش نامعتبر است.',
                422
            );
        }

        $orderRepository = new OrderRepository();

        $order = $orderRepository->findById(
            $orderId
        );

        if (
            $order === null ||
            $order->userId !== $userId
        ) {
            $this->error(
                'دسترسی به این سفارش مجاز نیست.',
                403
            );
        }

        $orderRepository = new OrderRepository();

        $order = $orderRepository->findById(
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

        $service =
            new PaymentService();

        $attempts =
            $service->findAttemptsByOrder(
                $orderId
            );

        foreach ($attempts as $attempt) {
            if (
                $attempt->userId !== null &&
                $attempt->userId !== $userId
            ) {
                $this->error(
                    'دسترسی به تلاش‌های پرداخت این سفارش مجاز نیست.',
                    403
                );
            }
        }

        $this->success(
            [
                'order_id' => $orderId,
                'attempts' => $attempts,
            ],
            'تلاش‌های پرداخت سفارش دریافت شد.'
        );
    }

    public function start(): never
    {
        $userId =
            $this->currentUserId();

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $orderId =
            isset($data['orderId'])
            ? (int)$data['orderId']
            : 0;

        $gateway =
            isset($data['gateway'])
            ? trim((string)$data['gateway'])
            : '';

        $amount =
            isset($data['amount'])
            ? trim((string)$data['amount'])
            : '';

        $currency =
            isset($data['currency'])
            ? trim((string)$data['currency'])
            : 'IRR';

        try {
            $service =
                new PaymentService();

            $attempt =
                $service->startAttempt(
                    $orderId,
                    $userId,
                    $gateway,
                    $amount,
                    $currency,
                    Request::ip()
                );

            $this->success(
                $attempt,
                'تلاش پرداخت با موفقیت ایجاد شد.'
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

    public function complete(
        array $parameters = []
    ): never {
        $this->error(
            'تکمیل پرداخت تا زمان پیکربندی تأیید معتبر درگاه غیرفعال است.',
            503
        );
    }

    public function fail(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $attemptId =
            (int)($parameters['id'] ?? 0);

        $this->ownedAttempt(
            $attemptId,
            $userId
        );

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $errorCode =
            isset($data['errorCode'])
            ? trim((string)$data['errorCode'])
            : '';

        $errorMessage =
            isset($data['errorMessage'])
            ? trim((string)$data['errorMessage'])
            : '';

        try {
            $service =
                new PaymentService();

            $attempt =
                $service->failAttempt(
                    $attemptId,
                    $errorCode,
                    $errorMessage
                );

            (new AdminNotificationEventDispatcher())->dispatch('commerce.payment.failed', 'پرداخت ناموفق', 'یک تلاش پرداخت ناموفق ثبت شد.', 'payment_attempt', $attemptId, ['error_code' => $errorCode]);
            $this->success(
                $attempt,
                'پرداخت ناموفق ثبت شد.'
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
}
