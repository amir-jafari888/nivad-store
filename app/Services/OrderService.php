<?php

declare(strict_types=1);

final class OrderService
{
    private OrderRepository $repository;
    private OrderItemRepository $itemRepository;
    private OrderValidator $validator;
    private OrderItemValidator $itemValidator;
    private OrderStatusHistoryService $statusHistoryService;
    private OrderStatusHistoryValidator $statusHistoryValidator;
    private PDO $pdo;

    private CheckoutService $checkoutService;
    private InventoryReservationService $reservationService;
    private CartService $cartService;
    private InventoryService $inventoryService;

    private CouponService $couponService;
    private CouponUsageService $couponUsageService;
    private NotificationService $notificationService;

    private ShippingService $shippingService;

    public function __construct(
        ?OrderRepository $repository = null,
        ?OrderItemRepository $itemRepository = null,
        ?OrderValidator $validator = null,
        ?OrderItemValidator $itemValidator = null,
        ?PDO $pdo = null,
        ?OrderStatusHistoryService $statusHistoryService = null,
        ?OrderStatusHistoryValidator $statusHistoryValidator = null,
        ?CheckoutService $checkoutService = null,
        ?InventoryReservationService $reservationService = null,
        ?CartService $cartService = null,
        ?InventoryService $inventoryService = null,
        ?CouponService $couponService = null,
        ?CouponUsageService $couponUsageService = null,
        ?NotificationService $notificationService = null,
        ?ShippingService $shippingService = null
    ) {
        $this->pdo =
            $pdo
            ?? Database::connection();

        $this->repository =
            $repository
            ?? new OrderRepository($this->pdo);

        $this->itemRepository =
            $itemRepository
            ?? new OrderItemRepository($this->pdo);

        $this->validator =
            $validator
            ?? new OrderValidator();

        $this->itemValidator =
            $itemValidator
            ?? new OrderItemValidator();

        $this->statusHistoryService =
            $statusHistoryService
            ?? new OrderStatusHistoryService();

        $this->statusHistoryValidator =
            $statusHistoryValidator
            ?? new OrderStatusHistoryValidator();

        $this->checkoutService =
            $checkoutService
            ?? new CheckoutService();

        $this->reservationService =
            $reservationService
            ?? new InventoryReservationService();

        $this->cartService =
            $cartService
            ?? new CartService();

        $this->inventoryService =
            $inventoryService
            ?? new InventoryService();

        $this->couponService =
            $couponService
            ?? new CouponService();

        $this->couponUsageService =
            $couponUsageService
            ?? new CouponUsageService(
                null,
                $this->couponService
            );

        $this->notificationService =
            $notificationService
            ?? new NotificationService(
                new NotificationRepository($this->pdo)
            );

        $this->shippingService =
            $shippingService
            ?? new ShippingService(
                new ShippingRepository($this->pdo)
            );
    }

    public function findById(
        int $id
    ): ?Order {
        return $this->repository->findById(
            $id
        );
    }

    public function findByOrderNumber(
        string $orderNumber
    ): ?Order {
        return $this->repository->findByOrderNumber(
            $orderNumber
        );
    }

    public function findItems(
        int $orderId
    ): array {
        return $this->itemRepository->findAllByOrder(
            $orderId
        );
    }

    public function create(
        int $userId,
        string $orderNumber,
        string $status,
        string $currency,
        string $subtotal,
        string $discountAmount,
        string $shippingAmount,
        string $taxAmount,
        string $totalAmount,
        string $recipientName,
        string $recipientPhone,
        string $province,
        string $city,
        string $address,
        string $postalCode,
        array $items,
        ?string $buildingNumber = null,
        ?string $unit = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $customerNote = null,
        ?string $placedAt = null
    ): Order {
        $errors = $this->validator->validate(
            $userId,
            $orderNumber,
            $status,
            $currency,
            $subtotal,
            $discountAmount,
            $shippingAmount,
            $taxAmount,
            $totalAmount,
            $recipientName,
            $recipientPhone,
            $province,
            $city,
            $address,
            $postalCode,
            $buildingNumber,
            $unit,
            $latitude,
            $longitude,
            $customerNote
        );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                'اطلاعات سفارش نامعتبر است.'
            );
        }

        if ($items === []) {
            throw new InvalidArgumentException(
                'سفارش باید حداقل یک آیتم داشته باشد.'
            );
        }

        if (
            $this->repository->findByOrderNumber(
                $orderNumber
            ) !== null
        ) {
            throw new InvalidArgumentException(
                'شماره سفارش قبلاً استفاده شده است.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $order = $this->repository->create(
                $userId,
                $orderNumber,
                $status,
                $currency,
                $subtotal,
                $discountAmount,
                $shippingAmount,
                $taxAmount,
                $totalAmount,
                $recipientName,
                $recipientPhone,
                $province,
                $city,
                $address,
                $postalCode,
                $buildingNumber,
                $unit,
                $latitude,
                $longitude,
                $customerNote,
                $placedAt
            );

            foreach ($items as $item) {
                $itemErrors =
                    $this->itemValidator->validate(
                        $order->id,
                        $item['productId'] ?? null,
                        $item['variantId'] ?? null,
                        (string)($item['productName'] ?? ''),
                        (string)($item['productSku'] ?? ''),
                        $item['variantSku'] ?? null,
                        $item['variantTitle'] ?? null,
                        (int)($item['quantity'] ?? 0),
                        (string)($item['unitPrice'] ?? ''),
                        (string)($item['discountAmount'] ?? '0.0000'),
                        (string)($item['taxAmount'] ?? '0.0000'),
                        (string)($item['totalAmount'] ?? '')
                    );

                if ($itemErrors !== []) {
                    throw new InvalidArgumentException(
                        'اطلاعات یکی از آیتم‌های سفارش نامعتبر است.'
                    );
                }

                $this->itemRepository->create(
                    $order->id,
                    $item['productId'] ?? null,
                    $item['variantId'] ?? null,
                    (string)$item['productName'],
                    (string)$item['productSku'],
                    $item['variantSku'] ?? null,
                    $item['variantTitle'] ?? null,
                    (int)$item['quantity'],
                    (string)$item['unitPrice'],
                    (string)($item['discountAmount'] ?? '0.0000'),
                    (string)($item['taxAmount'] ?? '0.0000'),
                    (string)$item['totalAmount']
                );
            }

            $this->pdo->commit();

            return $order;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function finalizeCheckout(
        int $userId,
        ?int $addressId = null,
        ?string $customerNote = null,
        ?Coupon $coupon = null,
        ?int $shippingMethodId = null
    ): Order {
        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'شناسه کاربر نامعتبر است.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $checkout =
                $this->checkoutService->review(
                    $userId,
                    $addressId
                );

            /** @var Cart $cart */
            $cart = $checkout['cart'];

            if ($cart->userId !== $userId) {
                throw new RuntimeException(
                    'سبد خرید متعلق به این کاربر نیست.'
                );
            }

            if ($cart->status !== 'active') {
                throw new RuntimeException(
                    'سبد خرید برای Finalization قابل استفاده نیست.'
                );
            }

            $items =
                $checkout['items'];

            $currency =
                $checkout['currency'];

            $subtotal =
                $checkout['subtotal'];

            if (
                $shippingMethodId === null ||
                $shippingMethodId <= 0
            ) {
                throw new InvalidArgumentException(
                    'انتخاب روش ارسال الزامی است.'
                );
            }

            $shippingSelection =
                $this->checkoutService
                ->resolveShippingSelection(
                    $userId,
                    (int)$checkout['address']['id'],
                    $shippingMethodId
                );

            $shippingAmount =
                (string)$shippingSelection['cost'];

            $zero =
                '0.0000';

            $discountAmount =
                $zero;

            $finalAmount =
                $subtotal;

            if ($coupon !== null) {
                if ($coupon->id === null || $coupon->id < 1) {
                    throw new InvalidArgumentException(
                        'شناسه کوپن نامعتبر است.'
                    );
                }

                $couponCalculation =
                    $this->couponService->calculateDiscount(
                        $coupon,
                        $subtotal
                    );

                $discountAmount =
                    $couponCalculation['discount_amount'];

                $finalAmount =
                    $couponCalculation['final_amount'];

                if (
                    !$this->couponUsageService->canUse(
                        $coupon,
                        $userId
                    )
                ) {
                    throw new RuntimeException(
                        'امکان استفاده از این کوپن وجود ندارد.'
                    );
                }
            }

            $placedAt =
                (new DateTimeImmutable())
                ->format('Y-m-d H:i:s');

            $address =
                $checkout['address'];

            $orderNumber =
                $this->generateOrderNumber();

            $order =
                $this->repository->create(
                    $userId,
                    $orderNumber,
                    'pending',
                    $currency,
                    $subtotal,
                    $discountAmount,
                    $shippingAmount,
                    $finalAmount,
                    $address['recipient_name'],
                    $address['phone'],
                    $address['province'],
                    $address['city'],
                    $address['address'],
                    $address['postal_code'],
                    $address['building_number'],
                    $address['unit'],
                    $address['latitude'],
                    $address['longitude'],
                    $customerNote,
                    $placedAt
                );

            $finalAmount =
                $this->addDecimal(
                    $subtotal,
                    $shippingAmount
                );

            foreach ($items as $item) {
                if (!$item instanceof CartItem) {
                    throw new RuntimeException(
                        'داده یکی از آیتم‌های Cart نامعتبر است.'
                    );
                }

                $snapshot =
                    $this->repository
                    ->findOrderItemSnapshotByVariant(
                        $item->variantId
                    );

                if ($snapshot === null) {
                    throw new RuntimeException(
                        'Snapshot محصول یکی از آیتم‌های سفارش پیدا نشد.'
                    );
                }

                $lineTotal =
                    $this->multiplyDecimal(
                        $item->unitPrice,
                        $item->quantity
                    );

                $this->itemRepository->create(
                    $order->id,
                    $snapshot['productId'],
                    $snapshot['variantId'],
                    $snapshot['productName'],
                    $snapshot['productSku'],
                    $snapshot['variantSku'],
                    $snapshot['variantTitle'],
                    $item->quantity,
                    $item->unitPrice,
                    $zero,
                    $zero,
                    $lineTotal
                );
            }

            $activeReservations =
                $this->reservationService
                ->findAllActiveByCart(
                    $cart->id
                );

            $reservationsByVariant = [];

            foreach ($activeReservations as $reservation) {
                if ($reservation->orderId !== null) {
                    continue;
                }

                $reservationsByVariant[$reservation->variantId][] = $reservation;
            }

            foreach ($items as $item) {
                $variantReservations =
                    $reservationsByVariant[$item->variantId] ?? [];

                $reservedForItem = 0;

                foreach ($variantReservations as $reservation) {
                    $reservedForItem +=
                        $reservation->quantity;
                }

                if (
                    $reservedForItem >
                    $item->quantity
                ) {
                    throw new RuntimeException(
                        'مقدار Reservation فعال برای یکی از آیتم‌های Cart بیشتر از مقدار سفارش است.'
                    );
                }

                foreach ($variantReservations as $reservation) {
                    $attached =
                        $this->reservationService
                        ->attachToOrder(
                            $reservation->id,
                            $order->id
                        );

                    if (
                        $attached === null ||
                        $attached->orderId !== $order->id
                    ) {
                        throw new RuntimeException(
                            'اتصال Reservation به سفارش انجام نشد.'
                        );
                    }
                }

                $remainingQuantity =
                    $item->quantity -
                    $reservedForItem;

                if ($remainingQuantity <= 0) {
                    continue;
                }

                if (
                    !$this->inventoryService
                        ->reserveQuantity(
                            $item->variantId,
                            $remainingQuantity
                        )
                ) {
                    throw new RuntimeException(
                        'موجودی کافی برای رزرو نهایی سفارش وجود ندارد.'
                    );
                }

                $created =
                    $this->reservationService
                    ->create(
                        $item->variantId,
                        $cart->id,
                        $order->id,
                        $remainingQuantity,
                        'active',
                        null
                    );

                if (
                    $created === null ||
                    $created->orderId !== $order->id
                ) {
                    throw new RuntimeException(
                        'Reservation جدید به سفارش متصل نشد.'
                    );
                }
            }

            $this->statusHistoryService->create(
                $order->id,
                null,
                'pending',
                null,
                null
            );

            if ($coupon !== null) {
                $couponUsage =
                    $this->couponUsageService
                    ->createForOrder(
                        $coupon,
                        $userId,
                        $order->id,
                        $discountAmount,
                        $currency
                    );

                if ($couponUsage === null) {
                    throw new RuntimeException(
                        'ثبت استفاده از کوپن برای سفارش انجام نشد.'
                    );
                }

                if ($couponUsage->orderId !== $order->id) {
                    throw new RuntimeException(
                        'Coupon Usage به سفارش صحیح متصل نشد.'
                    );
                }

                if ($couponUsage->couponId !== $coupon->id) {
                    throw new RuntimeException(
                        'Coupon Usage به کوپن صحیح متصل نشد.'
                    );
                }

                if ($couponUsage->userId !== $userId) {
                    throw new RuntimeException(
                        'Coupon Usage به کاربر صحیح متصل نشد.'
                    );
                }
            }

            $finalAmount =
                $this->addDecimal(
                    $couponCalculation['final_amount'],
                    $shippingAmount
                );

            $convertedCart =
                $this->cartService
                ->updateStatus(
                    $cart->id,
                    'converted'
                );

            if (
                $convertedCart === null ||
                $convertedCart->status !== 'converted'
            ) {
                throw new RuntimeException(
                    'وضعیت Cart به converted تغییر نکرد.'
                );
            }

            $this->pdo->commit();

            return $order;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function generateOrderNumber(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $orderNumber =
                bin2hex(
                    random_bytes(20)
                );

            if (
                $this->repository
                ->findByOrderNumber(
                    $orderNumber
                ) === null
            ) {
                return $orderNumber;
            }
        }

        throw new RuntimeException(
            'تولید شماره سفارش یکتا انجام نشد.'
        );
    }

    private function multiplyDecimal(
        string $value,
        int $quantity
    ): string {
        if ($quantity <= 0) {
            return '0.0000';
        }

        $value =
            trim($value);

        if (
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                'مقدار Unit Price برای Snapshot سفارش نامعتبر است.'
            );
        }

        [
            $integer,
            $fraction
        ] =
            array_pad(
                explode(
                    '.',
                    $value,
                    2
                ),
                2,
                ''
            );

        $fraction =
            str_pad(
                $fraction,
                4,
                '0'
            );

        $digits =
            ltrim(
                $integer . $fraction,
                '0'
            );

        if ($digits === '') {
            $digits = '0';
        }

        $result = '0';

        for (
            $i = 0;
            $i < $quantity;
            $i++
        ) {
            $result =
                $this->addIntegerStrings(
                    $result,
                    $digits
                );
        }

        if (strlen($result) <= 4) {
            $result =
                str_repeat(
                    '0',
                    5 - strlen($result)
                ) .
                $result;
        }

        $integerPart =
            ltrim(
                substr(
                    $result,
                    0,
                    -4
                ),
                '0'
            );

        $fractionPart =
            substr(
                $result,
                -4
            );

        if ($integerPart === '') {
            $integerPart = '0';
        }

        return
            $integerPart .
            '.' .
            $fractionPart;
    }

    private function addIntegerStrings(
        string $left,
        string $right
    ): string {
        $left =
            strrev($left);

        $right =
            strrev($right);

        $length =
            max(
                strlen($left),
                strlen($right)
            );

        $carry = 0;

        $result = '';

        for (
            $i = 0;
            $i < $length;
            $i++
        ) {
            $a =
                $i < strlen($left)
                ? (int)$left[$i]
                : 0;

            $b =
                $i < strlen($right)
                ? (int)$right[$i]
                : 0;

            $sum =
                $a +
                $b +
                $carry;

            $result .=
                (string)(
                    $sum % 10
                );

            $carry =
                intdiv(
                    $sum,
                    10
                );
        }

        if ($carry > 0) {
            $result .=
                (string)$carry;
        }

        return strrev($result);
    }

    public function changeStatus(
        int $orderId,
        string $toStatus,
        ?int $changedByUserId = null,
        ?string $note = null
    ): Order {
        $order =
            $this->repository->findById(
                $orderId
            );

        if ($order === null) {
            throw new RuntimeException(
                'سفارش پیدا نشد.'
            );
        }

        $fromStatus =
            $order->status;

        if ($fromStatus === $toStatus) {
            throw new InvalidArgumentException(
                'وضعیت سفارش تغییری نکرده است.'
            );
        }

        $errors =
            $this->statusHistoryValidator->validate(
                $orderId,
                $fromStatus,
                $toStatus,
                $changedByUserId,
                $note
            );

        if ($errors !== []) {
            throw new InvalidArgumentException(
                'اطلاعات تغییر وضعیت سفارش نامعتبر است.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            if ($toStatus === 'cancelled') {
                $this->releaseOrderReservations(
                    $orderId,
                    $changedByUserId,
                    $note
                );
            }

            $updatedOrder =
                $this->repository->updateStatus(
                    $orderId,
                    $toStatus,
                    $fromStatus
                );

            if ($updatedOrder === null) {
                throw new RuntimeException(
                    'وضعیت سفارش تغییر نکرد.'
                );
            }

            $this->statusHistoryService->create(
                $orderId,
                $fromStatus,
                $toStatus,
                $changedByUserId,
                $note
            );

            $this->notificationService->createForUser(
                $updatedOrder->userId,
                NotificationService::TYPE_ORDER_STATUS_CHANGED,
                'تغییر وضعیت سفارش',
                'وضعیت سفارش شما تغییر کرد.',
                [
                    'order_id' => $updatedOrder->id,
                    'order_number' => $updatedOrder->orderNumber,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                ]
            );

            if (!$this->pdo->inTransaction()) {
                throw new RuntimeException(
                    'Order status transaction was closed unexpectedly.'
                );
            }

            $this->pdo->commit();

            return $updatedOrder;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function releaseOrderReservations(
        int $orderId,
        ?int $changedByUserId,
        ?string $note
    ): void {
        $reservations =
            $this->reservationService->findAllByOrder(
                $orderId
            );

        foreach ($reservations as $reservation) {
            if ($reservation->status !== 'active') {
                continue;
            }

            $released =
                $this->reservationService->releaseActive(
                    $reservation->id
                );

            if ($released === null) {
                $current =
                    $this->reservationService->findById(
                        $reservation->id
                    );

                if (
                    $current === null ||
                    $current->status !== 'released'
                ) {
                    throw new RuntimeException(
                        'رزرو موجودی سفارش قابل آزادسازی نیست.'
                    );
                }

                continue;
            }

            $releasedQuantity =
                $this->inventoryService->releaseQuantity(
                    $released->variantId,
                    $released->quantity
                );

            if (!$releasedQuantity) {
                throw new RuntimeException(
                    'موجودی رزروشده قابل آزادسازی نیست.'
                );
            }

            (new InventoryMovementService())->create(
                $released->variantId,
                'release',
                $released->quantity,
                'order',
                $orderId,
                $note,
                $changedByUserId
            );
        }
    }

    public function findByUser(
        int $userId
    ): array {
        return $this->repository->findAllByUser(
            $userId
        );
    }

    public function findByStatus(
        string $status
    ): array {
        return $this->repository->findAllByStatus(
            $status
        );
    }

    public function details(
        int $orderId
    ): ?array {
        $order =
            $this->repository->findById(
                $orderId
            );

        if ($order === null) {
            return null;
        }

        return [
            'order' => $order,
            'items' =>
            $this->itemRepository->findAllByOrder(
                $orderId
            ),
            'status_history' =>
            $this->statusHistoryService->findAllByOrder(
                $orderId
            ),
        ];
    }

    private function addDecimal(
        string $left,
        string $right
    ): string {
        $left = trim($left);
        $right = trim($right);

        if (
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $left
            ) ||
            !preg_match(
                '/^\d+(?:\.\d{1,4})?$/',
                $right
            )
        ) {
            throw new InvalidArgumentException(
                'مقدار مبلغ برای محاسبه نامعتبر است.'
            );
        }

        [$leftInteger, $leftFraction] =
            array_pad(
                explode('.', $left, 2),
                2,
                '0'
            );

        [$rightInteger, $rightFraction] =
            array_pad(
                explode('.', $right, 2),
                2,
                '0'
            );

        $leftFraction =
            str_pad(
                $leftFraction,
                4,
                '0'
            );

        $rightFraction =
            str_pad(
                $rightFraction,
                4,
                '0'
            );

        $fraction =
            (int)$leftFraction +
            (int)$rightFraction;

        $carry =
            intdiv(
                $fraction,
                10000
            );

        $fraction %= 10000;

        $integer =
            (int)$leftInteger +
            (int)$rightInteger +
            $carry;

        return
            $integer .
            '.' .
            str_pad(
                (string)$fraction,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}
