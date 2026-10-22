<?php

declare(strict_types=1);

final class CouponController extends Controller
{
    private CouponService $service;

    public function __construct(
        ?CouponService $service = null
    ) {
        $this->service =
            $service ??
            new CouponService();
    }

    public function validateCustomer(): never
    {
        $userId =
            Auth::id();

        if ($userId === null) {
            $this->error(
                'برای استفاده از کوپن باید وارد حساب شوید.',
                401
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $code =
            trim(
                (string)(
                    $data['code'] ?? ''
                )
            );

        $subtotal =
            (string)(
                $data['subtotal'] ?? '0'
            );

        if ($code === '') {
            $this->error(
                'کد تخفیف الزامی است.',
                422
            );
        }

        $coupon =
            $this->service
            ->findActiveByCode(
                $code
            );

        if ($coupon === null) {
            $this->error(
                'کد تخفیف معتبر نیست.',
                422
            );
        }

        $usageService =
            new CouponUsageService();

        if (
            !$usageService->canUse(
                $coupon,
                $userId
            )
        ) {
            $this->error(
                'این کوپن برای حساب شما قابل استفاده نیست.',
                422
            );
        }

        try {
            $discount =
                $this->service
                ->calculateDiscount(
                    $coupon,
                    $subtotal
                );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }

        $this->success(
            [
                'coupon' => $coupon,
                'discount' => $discount,
            ],
            'کد تخفیف با موفقیت اعمال شد.'
        );
    }

    /*
     * 19.144
     * List
     */
    public function index(): never
    {
        $coupons =
            $this->service->findAll();

        $this->success(
            $coupons,
            'لیست کوپن‌ها دریافت شد.'
        );
    }

    /*
     * 19.144
     * Show
     */
    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه کوپن نامعتبر است.',
                422
            );
        }

        $coupon =
            $this->service->findById($id);

        if ($coupon === null) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $coupon,
            'اطلاعات کوپن دریافت شد.'
        );
    }

    /*
     * 19.145
     * Create
     */
    public function store(): never
    {
        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['code'] =
            trim(
                (string)($data['code'] ?? '')
            );

        $data['title'] =
            array_key_exists(
                'title',
                $data
            )
            ? trim(
                (string)$data['title']
            )
            : null;

        $data['description'] =
            array_key_exists(
                'description',
                $data
            )
            ? trim(
                (string)$data['description']
            )
            : null;

        $data['discount_type'] =
            trim(
                (string)(
                    $data['discount_type'] ?? ''
                )
            );

        $data['discount_value'] =
            (string)(
                $data['discount_value'] ?? ''
            );

        $data['minimum_order_amount'] =
            array_key_exists(
                'minimum_order_amount',
                $data
            ) &&
            $data['minimum_order_amount'] !== ''
            ? (string)$data['minimum_order_amount']
            : null;

        $data['maximum_discount_amount'] =
            array_key_exists(
                'maximum_discount_amount',
                $data
            ) &&
            $data['maximum_discount_amount'] !== ''
            ? (string)$data['maximum_discount_amount']
            : null;

        $data['usage_limit'] =
            array_key_exists(
                'usage_limit',
                $data
            ) &&
            $data['usage_limit'] !== ''
            ? (int)$data['usage_limit']
            : null;

        $data['usage_limit_per_user'] =
            array_key_exists(
                'usage_limit_per_user',
                $data
            ) &&
            $data['usage_limit_per_user'] !== ''
            ? (int)$data['usage_limit_per_user']
            : null;

        $data['starts_at'] =
            array_key_exists(
                'starts_at',
                $data
            ) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $data['expires_at'] =
            array_key_exists(
                'expires_at',
                $data
            ) &&
            $data['expires_at'] !== ''
            ? trim(
                (string)$data['expires_at']
            )
            : null;

        $data['status'] =
            trim(
                (string)(
                    $data['status'] ??
                    Coupon::STATUS_ACTIVE
                )
            );

        $errors =
            (new CouponValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات کوپن نامعتبر است.',
                422,
                $errors
            );
        }

        if (
            $this->service->findByCode(
                $data['code']
            ) !== null
        ) {
            $this->error(
                'کد کوپن قبلاً استفاده شده است.',
                422,
                [
                    'code' =>
                    'کد کوپن تکراری است.'
                ]
            );
        }

        $coupon =
            $this->service->create(
                $data['code'],
                $data['title'],
                $data['description'],
                $data['discount_type'],
                $data['discount_value'],
                $data['minimum_order_amount'],
                $data['maximum_discount_amount'],
                $data['usage_limit'],
                $data['usage_limit_per_user'],
                $data['starts_at'],
                $data['expires_at'],
                $data['status']
            );

        if ($coupon === null) {
            $this->error(
                'ایجاد کوپن انجام نشد.',
                422
            );
        }

        $this->success(
            $coupon,
            'کوپن با موفقیت ایجاد شد.',
            201
        );
    }

    /*
     * 19.146
     * Update
     */
    public function update(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه کوپن نامعتبر است.',
                422
            );
        }

        if (
            $this->service->findById($id) === null
        ) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $data['code'] =
            trim(
                (string)($data['code'] ?? '')
            );

        $data['title'] =
            array_key_exists(
                'title',
                $data
            )
            ? trim(
                (string)$data['title']
            )
            : null;

        $data['description'] =
            array_key_exists(
                'description',
                $data
            )
            ? trim(
                (string)$data['description']
            )
            : null;

        $data['discount_type'] =
            trim(
                (string)(
                    $data['discount_type'] ?? ''
                )
            );

        $data['discount_value'] =
            (string)(
                $data['discount_value'] ?? ''
            );

        $data['minimum_order_amount'] =
            array_key_exists(
                'minimum_order_amount',
                $data
            ) &&
            $data['minimum_order_amount'] !== ''
            ? (string)$data['minimum_order_amount']
            : null;

        $data['maximum_discount_amount'] =
            array_key_exists(
                'maximum_discount_amount',
                $data
            ) &&
            $data['maximum_discount_amount'] !== ''
            ? (string)$data['maximum_discount_amount']
            : null;

        $data['usage_limit'] =
            array_key_exists(
                'usage_limit',
                $data
            ) &&
            $data['usage_limit'] !== ''
            ? (int)$data['usage_limit']
            : null;

        $data['usage_limit_per_user'] =
            array_key_exists(
                'usage_limit_per_user',
                $data
            ) &&
            $data['usage_limit_per_user'] !== ''
            ? (int)$data['usage_limit_per_user']
            : null;

        $data['starts_at'] =
            array_key_exists(
                'starts_at',
                $data
            ) &&
            $data['starts_at'] !== ''
            ? trim(
                (string)$data['starts_at']
            )
            : null;

        $data['expires_at'] =
            array_key_exists(
                'expires_at',
                $data
            ) &&
            $data['expires_at'] !== ''
            ? trim(
                (string)$data['expires_at']
            )
            : null;

        $data['status'] =
            trim(
                (string)(
                    $data['status'] ??
                    Coupon::STATUS_ACTIVE
                )
            );

        $errors =
            (new CouponValidator())
            ->validate($data);

        if ($errors !== []) {
            $this->error(
                'اطلاعات کوپن نامعتبر است.',
                422,
                $errors
            );
        }

        $existing =
            $this->service->findByCode(
                $data['code']
            );

        if (
            $existing !== null &&
            $existing->id !== $id
        ) {
            $this->error(
                'کد کوپن قبلاً استفاده شده است.',
                422,
                [
                    'code' =>
                    'کد کوپن تکراری است.'
                ]
            );
        }

        $coupon =
            $this->service->update(
                $id,
                $data['code'],
                $data['title'],
                $data['description'],
                $data['discount_type'],
                $data['discount_value'],
                $data['minimum_order_amount'],
                $data['maximum_discount_amount'],
                $data['usage_limit'],
                $data['usage_limit_per_user'],
                $data['starts_at'],
                $data['expires_at'],
                $data['status']
            );

        if ($coupon === null) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $coupon,
            'کوپن با موفقیت بروزرسانی شد.'
        );
    }

    /*
     * 19.147
     * Delete
     */
    public function destroy(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه کوپن نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->delete($id)
        ) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'کوپن با موفقیت حذف شد.'
        );
    }

    /*
     * 19.147
     * Restore
     */
    public function restore(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه کوپن نامعتبر است.',
                422
            );
        }

        if (
            !$this->service->restore($id)
        ) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            null,
            'کوپن با موفقیت بازیابی شد.'
        );
    }

    /*
     * 19.148
     * Status
     */
    public function status(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه کوپن نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $status =
            trim(
                (string)(
                    $data['status'] ?? ''
                )
            );

        if (
            !in_array(
                $status,
                [
                    Coupon::STATUS_ACTIVE,
                    Coupon::STATUS_INACTIVE,
                    Coupon::STATUS_EXPIRED,
                ],
                true
            )
        ) {
            $this->error(
                'وضعیت کوپن نامعتبر است.',
                422,
                [
                    'status' =>
                    'وضعیت باید active، inactive یا expired باشد.'
                ]
            );
        }

        $coupon =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($coupon === null) {
            $this->error(
                'کوپن موردنظر پیدا نشد.',
                404
            );
        }

        $this->success(
            $coupon,
            'وضعیت کوپن بروزرسانی شد.'
        );
    }
}
