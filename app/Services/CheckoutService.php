<?php

declare(strict_types=1);

final class CheckoutService
{
    private CartService $cartService;

    private CartItemService $cartItemService;

    private CheckoutValidator $validator;

    private CartItemValidator $cartItemValidator;

    private UserAddressService $addressService;

    private UserAddressValidator $addressValidator;

    private ProductVariantService $variantService;

    private InventoryService $inventoryService;

    private InventoryReservationService $reservationService;

    private ShippingService $shippingService;

    public function __construct(
        ?CartService $cartService = null,
        ?CartItemService $cartItemService = null,
        ?CheckoutValidator $validator = null,
        ?CartItemValidator $cartItemValidator = null,
        ?UserAddressService $addressService = null,
        ?UserAddressValidator $addressValidator = null,
        ?ProductVariantService $variantService = null,
        ?InventoryService $inventoryService = null,
        ?InventoryReservationService $reservationService = null,
        ?ShippingService $shippingService = null
    ) {
        $this->cartService =
            $cartService ??
            new CartService();

        $this->cartItemService =
            $cartItemService ??
            new CartItemService();

        $this->validator =
            $validator ??
            new CheckoutValidator();

        $this->cartItemValidator =
            $cartItemValidator ??
            new CartItemValidator();

        $this->addressService =
            $addressService ??
            new UserAddressService();

        $this->addressValidator =
            $addressValidator ??
            new UserAddressValidator();

        $this->variantService =
            $variantService ??
            new ProductVariantService();

        $this->inventoryService =
            $inventoryService ??
            new InventoryService();

        $this->reservationService =
            $reservationService ??
            new InventoryReservationService();

        $this->shippingService =
            $shippingService ??
            new ShippingService();
    }

    /**
     * @return array{
     *     cart: Cart,
     *     items: array<int, CartItem>,
     *     variants: array<int, array{
     *         id: int,
     *         product_id: int,
     *         sku: string,
     *         name: ?string,
     *         price: string,
     *         currency: string
     *     }>,
     *     stock: array<int, array{
     *         variant_id: int,
     *         quantity: int,
     *         reserved_quantity: int,
     *         cart_reserved_quantity: int,
     *         available_quantity: int,
     *         requested_quantity: int
     *     }>,
     *     currency: string,
     *     subtotal: string,
     *     itemCount: int,
     *     totalQuantity: int,
     *     address: array{
     *         id: int,
     *         title: string,
     *         recipient_name: string,
     *         phone: string,
     *         province: string,
     *         city: string,
     *         address: string,
     *         postal_code: string,
     *         building_number: ?string,
     *         unit: ?string,
     *         latitude: ?string,
     *         longitude: ?string
     *     }
     * }
     */

    
    
    public function review(
        int $userId,
        ?int $addressId = null
    ): array {
        $errors = $this->validator->validate([
            'userId' => $userId,
        ]);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                'اطلاعات Checkout نامعتبر است.'
            );
        }

        $cart = $this->cartService->findByUser(
            $userId
        );

        if ($cart === null) {
            throw new RuntimeException(
                'سبد خرید فعالی برای این کاربر پیدا نشد.'
            );
        }

        if ($cart->status !== 'active') {
            throw new RuntimeException(
                'سبد خرید برای Checkout قابل استفاده نیست.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 13.11 - 13.12
        | Cart expiration
        |--------------------------------------------------------------------------
        */

        if (
            $cart->expiresAt !== null &&
            trim($cart->expiresAt) !== ''
        ) {
            try {
                $expiresAt =
                    new DateTimeImmutable(
                        $cart->expiresAt
                    );
            } catch (Exception $e) {
                throw new RuntimeException(
                    'زمان انقضای سبد خرید نامعتبر است.'
                );
            }

            $now =
                new DateTimeImmutable();

            if ($expiresAt <= $now) {
                throw new RuntimeException(
                    'زمان سبد خرید به پایان رسیده است.'
                );
            }
        }

        $items =
            $this->cartItemService->findByCart(
                $cart->id
            );

        if ($items === []) {
            throw new RuntimeException(
                'سبد خرید خالی است.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 13.21 - 13.40
        | Address resolution and validation
        |--------------------------------------------------------------------------
        */

        $address = null;

        if ($addressId !== null) {
            if ($addressId <= 0) {
                throw new InvalidArgumentException(
                    'شناسه آدرس نامعتبر است.'
                );
            }

            $address =
                $this->addressService
                ->findByIdAndUser(
                    $addressId,
                    $userId
                );

            if ($address === null) {
                throw new RuntimeException(
                    'آدرس انتخاب‌شده متعلق به این کاربر نیست یا پیدا نشد.'
                );
            }
        } else {
            $address =
                $this->addressService
                ->findDefaultByUser(
                    $userId
                );
        }

        if ($address === null) {
            throw new RuntimeException(
                'برای ادامه Checkout باید یک آدرس معتبر انتخاب شود.'
            );
        }

        $addressErrors =
            $this->addressValidator
            ->validateForCheckout(
                $address
            );

        if ($addressErrors !== []) {
            throw new RuntimeException(
                'اطلاعات آدرس برای Checkout کامل نیست.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 13.41 - 13.60
        | Variant, price, currency and stock integrity
        |--------------------------------------------------------------------------
        */

        $cartCurrency =
            strtoupper(
                trim(
                    $cart->currency
                )
            );

        if (
            $cartCurrency === '' ||
            strlen($cartCurrency) !== 3 ||
            preg_match(
                '/^[A-Z]{3}$/',
                $cartCurrency
            ) !== 1
        ) {
            throw new RuntimeException(
                'واحد پول سبد خرید نامعتبر است.'
            );
        }

        $variants = [];

        $stock = [];

        $subtotal = '0.0000';

        $itemCount = 0;

        $totalQuantity = 0;

        foreach ($items as $item) {
            $itemErrors =
                $this->cartItemValidator->validate([
                    'cartId' => $item->cartId,
                    'variantId' => $item->variantId,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unitPrice,
                ]);

            if ($itemErrors !== []) {
                throw new RuntimeException(
                    'یکی از آیتم‌های سبد خرید اطلاعات نامعتبر دارد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13.42 - 13.44
            | Variant existence and active status
            |--------------------------------------------------------------------------
            */

            $variant =
                $this->variantService
                ->findById(
                    $item->variantId
                );

            if ($variant === null) {
                throw new RuntimeException(
                    'Variant یکی از آیتم‌های سبد خرید پیدا نشد.'
                );
            }

            if ($variant->status !== 'active') {
                throw new RuntimeException(
                    'یکی از محصولات سبد خرید دیگر برای فروش فعال نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13.45 - 13.46
            | Currency integrity
            |--------------------------------------------------------------------------
            */

            $variantCurrency =
                strtoupper(
                    trim(
                        $variant->currency
                    )
                );

            if (
                $variantCurrency === '' ||
                strlen($variantCurrency) !== 3 ||
                preg_match(
                    '/^[A-Z]{3}$/',
                    $variantCurrency
                ) !== 1
            ) {
                throw new RuntimeException(
                    'واحد پول یکی از Variantهای سبد خرید نامعتبر است.'
                );
            }

            if ($variantCurrency !== $cartCurrency) {
                throw new RuntimeException(
                    'واحد پول یکی از آیتم‌های سبد خرید با سبد خرید مطابقت ندارد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13.47 - 13.48
            | Unit price integrity
            |--------------------------------------------------------------------------
            */

            $cartUnitPrice =
                $this->normalizeDecimal(
                    (string)$item->unitPrice
                );

            $variantPrice =
                $this->normalizeDecimal(
                    (string)$variant->price
                );

            if ($cartUnitPrice !== $variantPrice) {
                throw new RuntimeException(
                    'قیمت یکی از آیتم‌های سبد خرید با قیمت فعلی محصول مطابقت ندارد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13.53 - 13.55
            | Inventory resolution and availability
            |--------------------------------------------------------------------------
            */

            $inventory =
                $this->inventoryService
                ->findByVariant(
                    $variant->id
                );

            if ($inventory === null) {
                throw new RuntimeException(
                    'موجودی یکی از Variantهای سبد خرید تعریف نشده است.'
                );
            }

            $availableQuantity =
                $inventory->quantity -
                $inventory->reservedQuantity;

            if ($availableQuantity < 0) {
                $availableQuantity = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | 13.56 - 13.57
            | Current cart active reservations
            |--------------------------------------------------------------------------
            */

            $cartReservedQuantity = 0;

            $reservations =
                $this->reservationService
                ->findAllByCart(
                    $cart->id
                );

            $now =
                new DateTimeImmutable();

            foreach ($reservations as $reservation) {
                if (
                    $reservation->variantId !==
                    $variant->id
                ) {
                    continue;
                }

                if (
                    $reservation->status !== 'active'
                ) {
                    continue;
                }

                if (
                    $reservation->expiresAt !== null &&
                    trim($reservation->expiresAt) !== ''
                ) {
                    try {
                        $reservationExpiresAt =
                            new DateTimeImmutable(
                                $reservation->expiresAt
                            );
                    } catch (Exception $e) {
                        continue;
                    }

                    if ($reservationExpiresAt <= $now) {
                        continue;
                    }
                }

                $cartReservedQuantity +=
                    $reservation->quantity;
            }

            /*
            |--------------------------------------------------------------------------
            | Current cart reservations already occupy part
            | of reserved_quantity, therefore return them
            | to this cart's effective availability.
            |--------------------------------------------------------------------------
            */

            $effectiveAvailableQuantity =
                $availableQuantity +
                $cartReservedQuantity;

            /*
            |--------------------------------------------------------------------------
            | 13.58
            | Requested quantity must be available
            |--------------------------------------------------------------------------
            */

            if (
                $item->quantity >
                $effectiveAvailableQuantity
            ) {
                throw new RuntimeException(
                    'موجودی یکی از آیتم‌های سبد خرید برای ادامه Checkout کافی نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13.59
            | Variant snapshot
            |--------------------------------------------------------------------------
            */

            $variants[] = [
                'id' => $variant->id,
                'product_id' => $variant->productId,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'price' => $variantPrice,
                'currency' => $variantCurrency,
            ];

            $stock[] = [
                'variant_id' => $variant->id,
                'quantity' => $inventory->quantity,
                'reserved_quantity' =>
                $inventory->reservedQuantity,
                'cart_reserved_quantity' =>
                $cartReservedQuantity,
                'available_quantity' =>
                $effectiveAvailableQuantity,
                'requested_quantity' =>
                $item->quantity,
            ];

            /*
            |--------------------------------------------------------------------------
            | Line subtotal
            |--------------------------------------------------------------------------
            */

            $lineTotal =
                $this->multiplyDecimal(
                    $cartUnitPrice,
                    $item->quantity
                );

            $subtotal =
                $this->addDecimal(
                    $subtotal,
                    $lineTotal
                );

            $itemCount++;

            $totalQuantity +=
                $item->quantity;
        }

        return [
            'cart' => $cart,
            'items' => $items,
            'variants' => $variants,
            'stock' => $stock,
            'currency' => $cartCurrency,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
            'totalQuantity' => $totalQuantity,
            'address' => $this->addressSnapshot(
                $address
            ),
        ];
    }

    public function resolveShippingSelection(
        int $userId,
        int $addressId,
        int $shippingMethodId
    ): array {
        $checkout = $this->review(
            $userId,
            $addressId
        );

        $methods = $checkout['shipping']['methods'];

        foreach ($methods as $method) {
            if ((int)$method['id'] === $shippingMethodId) {
                return $method;
            }
        }

        throw new RuntimeException(
            'روش ارسال انتخاب‌شده برای این آدرس و سفارش معتبر نیست.'
        );
    }

    private function shippingSnapshot(
        UserAddress $address,
        string $subtotal,
        string $currency
    ): array {
        $zone = $this->shippingService->findZoneByLocation(
            $address->province,
            $address->city
        );

        if ($zone === null) {
            return [
                'zone' => null,
                'methods' => [],
                'available' => false,
            ];
        }

        $methods = [];

        foreach (
            $this->shippingService->findActiveShippingMethods()
            as $method
        ) {
            $methodZone =
                $this->shippingService
                ->findShippingMethodZone(
                    $method->id,
                    $zone->id
                );

            if (
                $methodZone === null ||
                $methodZone->status !==
                ShippingMethodZone::STATUS_ACTIVE
            ) {
                continue;
            }

            $methodCurrency =
                strtoupper(
                    trim(
                        $methodZone->currency
                            ?: $method->currency
                    )
                );

            if ($methodCurrency !== $currency) {
                continue;
            }

            if (
                $methodZone->minOrderAmount !== null &&
                $methodZone->minOrderAmount !== '' &&
                $this->compareDecimal(
                    $subtotal,
                    $methodZone->minOrderAmount
                ) < 0
            ) {
                continue;
            }

            $cost =
                $this->normalizeDecimal(
                    $methodZone->cost
                );

            if (
                $methodZone->freeShippingThreshold !== null &&
                $methodZone->freeShippingThreshold !== '' &&
                $this->compareDecimal(
                    $subtotal,
                    $methodZone->freeShippingThreshold
                ) >= 0
            ) {
                $cost = '0.0000';
            }

            $methods[] = [
                'id' => $method->id,
                'name' => $method->name,
                'slug' => $method->slug,
                'description' => $method->description,
                'cost' => $cost,
                'currency' => $methodCurrency,
                'estimated_min_days' =>
                $methodZone->estimatedMinDays ??
                    $method->estimatedMinDays,
                'estimated_max_days' =>
                $methodZone->estimatedMaxDays ??
                    $method->estimatedMaxDays,
            ];
        }

        return [
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'slug' => $zone->slug,
            ],
            'methods' => $methods,
            'available' => $methods !== [],
        ];
    }

    private function compareDecimal(
        string $left,
        string $right
    ): int {
        $left =
            $this->normalizeDecimal($left);

        $right =
            $this->normalizeDecimal($right);

        [$leftInteger, $leftFraction] =
            explode('.', $left);

        [$rightInteger, $rightFraction] =
            explode('.', $right);

        $leftInteger =
            ltrim($leftInteger, '0') ?: '0';

        $rightInteger =
            ltrim($rightInteger, '0') ?: '0';

        if (
            strlen($leftInteger) !==
            strlen($rightInteger)
        ) {
            return
                strlen($leftInteger) <=>
                strlen($rightInteger);
        }

        if ($leftInteger !== $rightInteger) {
            return $leftInteger <=> $rightInteger;
        }

        return
            strcmp(
                $leftFraction,
                $rightFraction
            ) <=> 0;
    }

    private function addressSnapshot(
        UserAddress $address
    ): array {
        return [
            'id' => $address->id,
            'title' => $address->title,
            'recipient_name' => $address->recipientName,
            'phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'address' => $address->address,
            'postal_code' => $address->postalCode,
            'building_number' => $address->buildingNumber,
            'unit' => $address->unit,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        ];
    }

    private function multiplyDecimal(
        string $value,
        int $quantity
    ): string {
        if ($quantity <= 0) {
            return '0.0000';
        }

        $normalized =
            $this->normalizeDecimal($value);

        $negative =
            str_starts_with(
                $normalized,
                '-'
            );

        if ($negative) {
            throw new InvalidArgumentException(
                'مقدار Unit Price نمی‌تواند منفی باشد.'
            );
        }

        [$integer, $fraction] =
            explode(
                '.',
                $normalized
            );

        $digits =
            $integer . $fraction;

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

        $length =
            strlen($result);

        if ($length <= 4) {
            $result =
                str_repeat(
                    '0',
                    5 - $length
                ) .
                $result;
        }

        $integerPart =
            substr(
                $result,
                0,
                -4
            );

        $fractionPart =
            substr(
                $result,
                -4
            );

        $integerPart =
            ltrim(
                $integerPart,
                '0'
            );

        if ($integerPart === '') {
            $integerPart = '0';
        }

        return
            $integerPart .
            '.' .
            $fractionPart;
    }

    private function addDecimal(
        string $left,
        string $right
    ): string {
        $left =
            $this->normalizeDecimal(
                $left
            );

        $right =
            $this->normalizeDecimal(
                $right
            );

        if (
            str_starts_with($left, '-') ||
            str_starts_with($right, '-')
        ) {
            throw new InvalidArgumentException(
                'مقادیر منفی برای Checkout مجاز نیست.'
            );
        }

        [$leftInteger, $leftFraction] =
            explode(
                '.',
                $left
            );

        [$rightInteger, $rightFraction] =
            explode(
                '.',
                $right
            );

        $leftDigits =
            ltrim(
                $leftInteger,
                '0'
            ) .
            $leftFraction;

        $rightDigits =
            ltrim(
                $rightInteger,
                '0'
            ) .
            $rightFraction;

        if ($leftDigits === '') {
            $leftDigits = '0';
        }

        if ($rightDigits === '') {
            $rightDigits = '0';
        }

        $result =
            $this->addIntegerStrings(
                $leftDigits,
                $rightDigits
            );

        if (strlen($result) <= 4) {
            $result =
                str_repeat(
                    '0',
                    5 - strlen($result)
                ) .
                $result;
        }

        $integerPart =
            substr(
                $result,
                0,
                -4
            );

        $fractionPart =
            substr(
                $result,
                -4
            );

        $integerPart =
            ltrim(
                $integerPart,
                '0'
            );

        if ($integerPart === '') {
            $integerPart = '0';
        }

        return
            $integerPart .
            '.' .
            $fractionPart;
    }

    private function normalizeDecimal(
        string $value
    ): string {
        $value =
            trim($value);

        if ($value === '') {
            throw new InvalidArgumentException(
                'مقدار پولی نمی‌تواند خالی باشد.'
            );
        }

        if (
            !preg_match(
                '/^-?\d+(?:\.\d{1,4})?$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                'مقدار پولی نامعتبر است.'
            );
        }

        $negative =
            str_starts_with(
                $value,
                '-'
            );

        if ($negative) {
            $value =
                substr(
                    $value,
                    1
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

        $fraction =
            substr(
                $fraction,
                0,
                4
            );

        $integer =
            ltrim(
                $integer,
                '0'
            );

        if ($integer === '') {
            $integer = '0';
        }

        $result =
            $integer .
            '.' .
            $fraction;

        if (
            $negative &&
            $result !== '0.0000'
        ) {
            return '-' . $result;
        }

        return $result;
    }

    private function addIntegerStrings(
        string $left,
        string $right
    ): string {
        $left =
            ltrim(
                $left,
                '0'
            );

        $right =
            ltrim(
                $right,
                '0'
            );

        if ($left === '') {
            $left = '0';
        }

        if ($right === '') {
            $right = '0';
        }

        $i =
            strlen($left) - 1;

        $j =
            strlen($right) - 1;

        $carry = 0;

        $result = '';

        while (
            $i >= 0 ||
            $j >= 0 ||
            $carry > 0
        ) {
            $a =
                $i >= 0
                ? (int)$left[$i]
                : 0;

            $b =
                $j >= 0
                ? (int)$right[$j]
                : 0;

            $sum =
                $a +
                $b +
                $carry;

            $result =
                (string)($sum % 10) .
                $result;

            $carry =
                intdiv(
                    $sum,
                    10
                );

            $i--;
            $j--;
        }

        return
            ltrim(
                $result,
                '0'
            ) ?: '0';
    }
}
