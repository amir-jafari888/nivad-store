<?php

declare(strict_types=1);

final class ComparisonController extends Controller
{
    private ComparisonService $comparison;

    public function __construct(
        ?ComparisonService $comparison = null
    ) {
        $this->comparison =
            $comparison ??
            new ComparisonService();
    }

    public function index(): never
    {
        $raw =
            Request::query(
                'product_ids',
                ''
            );

        $deviceId =
            Request::query(
                'device_id'
            );

        $ids =
            is_string($raw)
            ? preg_split(
                '/[\s,]+/',
                trim($raw),
                -1,
                PREG_SPLIT_NO_EMPTY
            )
            : [];

        $parsedDeviceId = null;

        if (
            is_scalar($deviceId) &&
            ctype_digit(
                (string)$deviceId
            )
        ) {
            $parsedDeviceId =
                (int)$deviceId;

            if ($parsedDeviceId < 1) {
                $parsedDeviceId = null;
            }
        }

        $this->success(
            $this->comparison->compare(
                $ids,
                $parsedDeviceId
            ),
            'اطلاعات مقایسه محصولات دریافت شد.'
        );
    }
}
