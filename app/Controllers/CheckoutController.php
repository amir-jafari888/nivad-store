<?php

declare(strict_types=1);

final class CheckoutController extends Controller
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

    public function review(): never
    {
        $userId =
            $this->currentUserId();

        $addressId =
            Request::query(
                'address_id'
            );

        $addressId =
            $addressId !== null &&
            $addressId !== ''
            ? (int)$addressId
            : null;

        try {
            $service =
                new CheckoutService();

            $checkout =
                $service->review(
                    $userId,
                    $addressId
                );

            $this->success(
                $checkout,
                'اطلاعات Checkout با موفقیت آماده شد.'
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
    }
}
