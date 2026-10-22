<?php

declare(strict_types=1);

final class ProductQuestionController extends Controller
{
    private ProductQuestionService $service;

    public function __construct(
        ?ProductQuestionService $service = null
    ) {
        $this->service =
            $service ??
            new ProductQuestionService();
    }

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

    private function currentAdminUserId(): int
    {
        $adminUserId = AdminAuth::id();

        if ($adminUserId === null) {
            $this->error(
                'دسترسی مدیر موردنیاز است.',
                401
            );
        }

        return $adminUserId;
    }

    public function index(int $productId): never
    {
        $questions = $this->service->findPublicByProductId($productId);

        $items = array_map(
            static function (ProductQuestion $question): array {
                return [
                    'id' => $question->id,
                    'product_id' => $question->productId,
                    'question' => $question->question,
                    'created_at' => $question->createdAt,
                ];
            },
            $questions
        );

        Response::json([
            'items' => $items,
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پرسش نامعتبر است.',
                422
            );
        }

        $question =
            $this->service->findById($id);

        if ($question === null) {
            $this->error(
                'پرسش موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'question' => $question,
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

        $productId =
            (int)($data['product_id'] ?? 0);

        $question =
            trim(
                (string)($data['question'] ?? '')
            );

        if ($productId <= 0) {
            $this->error(
                'شناسه محصول نامعتبر است.',
                422
            );
        }

        if ($question === '') {
            $this->error(
                'متن پرسش الزامی است.',
                422
            );
        }

        try {
            $created =
                $this->service->create(
                    $productId,
                    $userId,
                    $question
                );

            if ($created === null) {
                $this->error(
                    'ایجاد پرسش انجام نشد.',
                    500
                );
            }

            $this->success(
                $created,
                'پرسش با موفقیت ثبت شد.'
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
        $this->currentAdminUserId();

        $this->success([
            'items' =>
            $this->service->findAll(),
        ]);
    }

    public function adminShow(
        array $parameters = []
    ): never {
        $this->currentAdminUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پرسش نامعتبر است.',
                422
            );
        }

        $question =
            $this->service->findById($id);

        if ($question === null) {
            $this->error(
                'پرسش موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'question' => $question,
        ]);
    }

    public function adminStatus(
        array $parameters = []
    ): never {
        $this->currentAdminUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پرسش نامعتبر است.',
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

        if ($status === '') {
            $this->error(
                'وضعیت پرسش الزامی است.',
                422
            );
        }

        try {
            $question =
                $this->service->moderate(
                    $id,
                    $status
                );

            if ($question === null) {
                $this->error(
                    'پرسش موردنظر پیدا نشد.',
                    404
                );
            }

            $this->success(
                $question,
                'وضعیت پرسش با موفقیت تغییر کرد.'
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

    public function adminNote(
        array $parameters = []
    ): never {
        $this->currentAdminUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پرسش نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $adminNote =
            isset($data['admin_note']) &&
            $data['admin_note'] !== ''
            ? trim((string)$data['admin_note'])
            : null;

        try {
            $question =
                $this->service->updateAdminNote(
                    $id,
                    $adminNote
                );

            if ($question === null) {
                $this->error(
                    'پرسش موردنظر پیدا نشد.',
                    404
                );
            }

            $this->success(
                $question,
                'یادداشت مدیر با موفقیت به‌روزرسانی شد.'
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
