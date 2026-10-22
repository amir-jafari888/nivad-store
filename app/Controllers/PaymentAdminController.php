<?php

declare(strict_types=1);

final class PaymentAdminController extends Controller
{
    private PaymentService $service;

    public function __construct(
        ?PaymentService $service = null
    ) {
        $this->service =
            $service ??
            new PaymentService();
    }

    public function index(): never
    {
        $this->success([
            'payments' =>
            $this->service
                ->findAllPayments(),
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پرداخت نامعتبر است.',
                422
            );
        }

        $payment =
            $this->service
            ->findPaymentById($id);

        if ($payment === null) {
            $this->error(
                'پرداخت پیدا نشد.',
                404
            );
        }

        $this->success($payment);
    }

    public function attempts(): never
    {
        $this->success([
            'attempts' =>
            $this->service
                ->findAllAttempts(),
        ]);
    }

    public function attemptShow(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه تلاش پرداخت نامعتبر است.',
                422
            );
        }

        $attempt =
            $this->service
            ->findAttemptById($id);

        if ($attempt === null) {
            $this->error(
                'تلاش پرداخت پیدا نشد.',
                404
            );
        }

        $this->success($attempt);
    }
}
