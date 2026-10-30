<?php

declare(strict_types=1);

final class ReviewController extends Controller
{
    private ReviewService $service;

    public function __construct(
        ?ReviewService $service = null
    ) {
        $this->service =
            $service ??
            new ReviewService();
    }

    public function index(int $productId): never
    {
        $reviews = $this->service->findPublicByProductId($productId);
        $rating = $this->service->getProductRatingSummary($productId);

        $items = array_map(
            static function (Review $review): array {
                return [
                    'id' => $review->id,
                    'product_id' => $review->productId,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'body' => $review->body,
                    'is_verified_purchase' => $review->isVerifiedPurchase,
                    'created_at' => $review->createdAt,
                ];
            },
            $reviews
        );

        Response::json([
            'items' => $items,
            'rating' => $rating,
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $review =
            $this->service->findById($id);

        if ($review === null) {
            $this->error(
                'دیدگاه موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'review' => $review,
        ]);
    }

    public function store(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ثبت دیدگاه باید وارد حساب شوید.',
                401
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $review =
            $this->service->create(
                (int)($data['productId'] ?? 0),
                $userId,
                isset($data['orderItemId']) &&
                    $data['orderItemId'] !== ''
                    ? (int)$data['orderItemId']
                    : null,
                (int)($data['rating'] ?? 0),
                isset($data['title'])
                    ? trim((string)$data['title'])
                    : null,
                trim((string)($data['body'] ?? '')),
                Review::STATUS_PENDING,
                false,
                null
            );

        if ($review === null) {
            $this->error(
                'ثبت دیدگاه انجام نشد.',
                422
            );
        }

        (new AdminNotificationEventDispatcher())->dispatch('content.review.pending', 'دیدگاه جدید برای بررسی ثبت شد', 'یک دیدگاه جدید در انتظار بررسی مدیر است.', 'review', is_object($review) ? ($review->id ?? null) : (is_array($review) ? ($review['id'] ?? null) : null));
        $this->success(
            ['review' => $review],
            'دیدگاه با موفقیت ثبت شد.'
        );
    }

    public function mine(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای مشاهده دیدگاه‌ها باید وارد حساب شوید.',
                401
            );
        }

        $this->success([
            'items' =>
            $this->service->findByUserId($userId),
        ]);
    }

    public function adminIndex(): never
    {
        $this->success([
            'items' =>
            $this->service->findAll(),
        ]);
    }

    public function adminShow(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $review =
            $this->service->findById($id);

        if ($review === null) {
            $this->error(
                'دیدگاه موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'review' => $review,
        ]);
    }

    public function adminStatus(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
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

        if (
            ReviewValidator::validateStatus(
                $status
            ) !== []
        ) {
            $this->error(
                'وضعیت دیدگاه نامعتبر است.',
                422
            );
        }

        $review =
            $this->service->updateStatus(
                $id,
                $status
            );

        if ($review === null) {
            $this->error(
                'تغییر وضعیت دیدگاه انجام نشد.',
                404
            );
        }

        $this->success(
            ['review' => $review],
            'وضعیت دیدگاه تغییر کرد.'
        );
    }

    public function approve(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $adminNote =
            array_key_exists(
                'adminNote',
                $data
            )
            ? ($data['adminNote'] === null
                ? null
                : trim(
                    (string)$data['adminNote']
                ))
            : null;

        $review =
            $this->service->approve(
                $id,
                $adminNote
            );

        if ($review === null) {
            $this->error(
                'تأیید دیدگاه انجام نشد.',
                404
            );
        }

        $this->success(
            ['review' => $review],
            'دیدگاه تأیید شد.'
        );
    }

    public function reject(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $adminNote =
            array_key_exists(
                'adminNote',
                $data
            )
            ? ($data['adminNote'] === null
                ? null
                : trim(
                    (string)$data['adminNote']
                ))
            : null;

        $review =
            $this->service->reject(
                $id,
                $adminNote
            );

        if ($review === null) {
            $this->error(
                'رد دیدگاه انجام نشد.',
                404
            );
        }

        $this->success(
            ['review' => $review],
            'دیدگاه رد شد.'
        );
    }

    public function hide(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $adminNote =
            array_key_exists(
                'adminNote',
                $data
            )
            ? ($data['adminNote'] === null
                ? null
                : trim(
                    (string)$data['adminNote']
                ))
            : null;

        $review =
            $this->service->hide(
                $id,
                $adminNote
            );

        if ($review === null) {
            $this->error(
                'مخفی‌کردن دیدگاه انجام نشد.',
                404
            );
        }

        $this->success(
            ['review' => $review],
            'دیدگاه مخفی شد.'
        );
    }

    public function adminNote(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه دیدگاه نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $adminNote =
            array_key_exists(
                'adminNote',
                $data
            )
            ? ($data['adminNote'] === null
                ? null
                : trim(
                    (string)$data['adminNote']
                ))
            : null;

        if (
            ReviewValidator::validateAdminNote(
                $adminNote
            ) !== []
        ) {
            $this->error(
                'یادداشت مدیر نامعتبر است.',
                422
            );
        }

        $review =
            $this->service->updateAdminNote(
                $id,
                $adminNote
            );

        if ($review === null) {
            $this->error(
                'ثبت یادداشت مدیر انجام نشد.',
                404
            );
        }

        $this->success(
            ['review' => $review],
            'یادداشت مدیر ثبت شد.'
        );
    }
}
