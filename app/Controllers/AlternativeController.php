<?php

declare(strict_types=1);

final class AlternativeController extends Controller
{
    private AlternativeService $alternatives;

    public function __construct(
        ?AlternativeService $alternatives = null
    ) {
        $this->alternatives =
            $alternatives ??
            new AlternativeService();
    }

    public function index(): never
    {
        $raw =
            Request::query(
                'product_id'
            );

        if (
            !is_scalar($raw) ||
            !ctype_digit((string)$raw) ||
            (int)$raw < 1
        ) {
            $this->error(
                'شناسه محصول ارسال نشده است.',
                422
            );
        }

        $this->success(
            $this->alternatives->find(
                (int)$raw
            ),
            'جایگزین‌های هوشمند محصول دریافت شد.'
        );
    }
}
