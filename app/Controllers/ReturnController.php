<?php

declare(strict_types=1);

final class ReturnController extends Controller
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

    public function index(): never
    {
        $userId =
            $this->currentUserId();

        $service =
            new ReturnService();

        $returns =
            $service->findByUserId(
                $userId
            );

        $this->success([
            'returns' => $returns,
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $userId =
            $this->currentUserId();

        $returnId =
            (int)($parameters['id'] ?? 0);

        if ($returnId <= 0) {
            $this->error(
                'شناسه مرجوعی نامعتبر است.',
                422
            );
        }

        $service =
            new ReturnService();

        $return =
            $service->findById(
                $returnId
            );

        if ($return === null) {
            $this->error(
                'درخواست مرجوعی پیدا نشد.',
                404
            );
        }

        if ($return->userId !== $userId) {
            $this->error(
                'دسترسی به این درخواست مجاز نیست.',
                403
            );
        }

        $items =
            $service->findItems(
                $returnId
            );

        $history =
            $service->findStatusHistory(
                $returnId
            );

        $this->success([
            'return' => $return,
            'items' => $items,
            'history' => $history,
        ]);
    }

    public function store(): never
    {
        $userId =
            $this->currentUserId();

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $orderId =
            (int)($data['orderId'] ?? 0);

        $returnNumber =
            trim(
                (string)($data['returnNumber'] ?? '')
            );

        $items =
            $data['items'] ?? [];

        $reason =
            isset($data['reason'])
            ? trim((string)$data['reason'])
            : null;

        $customerNote =
            isset($data['customerNote'])
            ? trim((string)$data['customerNote'])
            : null;

        try {
            $service =
                new ReturnService();

            $return =
                $service->createReturn(
                    $userId,
                    $orderId,
                    $returnNumber,
                    is_array($items)
                        ? $items
                        : [],
                    $reason,
                    $customerNote
                );

            $this->success(
                $return,
                'درخواست مرجوعی با موفقیت ثبت شد.'
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

    public function adminIndex(): never
    {
        $service =
            new ReturnService();

        $orderId =
            (int)($_GET['orderId'] ?? 0);

        if ($orderId > 0) {
            $returns =
                $service->findByOrderId(
                    $orderId
                );

            $this->success([
                'returns' => $returns,
            ]);
        }

        $returns =
            $service->findAll();

        $this->success([
            'returns' => $returns,
        ]);
    }

    public function adminShow(
        array $parameters = []
    ): never {
        $returnId =
            (int)($parameters['id'] ?? 0);

        if ($returnId <= 0) {
            $this->error(
                'شناسه مرجوعی نامعتبر است.',
                422
            );
        }

        $repository =
           new ReturnService();

        $return =
            $repository->findById(
                $returnId
            );

        if ($return === null) {
            $this->error(
                'درخواست مرجوعی پیدا نشد.',
                404
            );
        }

        $this->success([
            'return' => $return,
            'items' =>
            $repository->findItems(
                $returnId
            ),
            'history' =>
            $repository->findStatusHistory(
                $returnId
            ),
        ]);
    }

    public function adminStatus(
        array $parameters = []
    ): never {
        $returnId =
            (int)($parameters['id'] ?? 0);

        if ($returnId <= 0) {
            $this->error(
                'شناسه مرجوعی نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)($data['status'] ?? '')
            );

        $adminId =
            Auth::id();

        try {
            $service =
                new ReturnService();

            $return =
                $service->changeStatus(
                    $returnId,
                    $status,
                    $adminId,
                    isset($data['note'])
                        ? trim(
                            (string)$data['note']
                        )
                        : null
                );

            $this->success(
                $return,
                'وضعیت مرجوعی با موفقیت تغییر کرد.'
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

    public function adminRefunds(
        array $parameters = []
    ): never {
        $returnId =
            (int)($parameters['id'] ?? 0);

        if ($returnId <= 0) {
            $this->error(
                'شناسه مرجوعی نامعتبر است.',
                422
            );
        }

        $service =
            new RefundService();

        $refunds =
            $service->findByReturnId(
                $returnId
            );

        $this->success([
            'refunds' => $refunds,
        ]);
    }

    public function adminCreateRefund(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $returnId =
            (int)($data['returnId'] ?? 0);

        $refundNumber =
            trim(
                (string)($data['refundNumber'] ?? '')
            );

        $amount =
            trim(
                (string)($data['amount'] ?? '')
            );

        $currency =
            trim(
                (string)($data['currency'] ?? 'IRR')
            );

        $paymentId =
            isset($data['paymentId'])
            && $data['paymentId'] !== ''
            ? (int)$data['paymentId']
            : null;

        try {
            $service =
                new RefundService();

            $refund =
                $service->createRefund(
                    $returnId,
                    $refundNumber,
                    $amount,
                    $currency,
                    $paymentId,
                    isset($data['reason'])
                        ? trim(
                            (string)$data['reason']
                        )
                        : null,
                    isset($data['gateway'])
                        ? trim(
                            (string)$data['gateway']
                        )
                        : null
                );

            $this->success(
                $refund,
                'بازپرداخت با موفقیت ایجاد شد.'
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

    public function adminRefundStatus(
        array $parameters = []
    ): never {
        $refundId =
            (int)($parameters['id'] ?? 0);

        if ($refundId <= 0) {
            $this->error(
                'شناسه بازپرداخت نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)($data['status'] ?? '')
            );

        try {
            $service =
                new RefundService();

            $refund =
                $service->changeStatus(
                    $refundId,
                    $status
                );

            $this->success(
                $refund,
                'وضعیت بازپرداخت تغییر کرد.'
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
