<?php

declare(strict_types=1);

final class ProductAnswerController extends Controller
{
    private ProductAnswerService $service;

    public function __construct(
        ?ProductAnswerService $service = null
    ) {
        $this->service =
            $service ??
            new ProductAnswerService();
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

    public function index(int $questionId): never
    {
        $answers = $this->service->findPublicByQuestionId($questionId);

        $items = array_map(
            static function (ProductAnswer $answer): array {
                return [
                    'id' => $answer->id,
                    'question_id' => $answer->questionId,
                    'answer' => $answer->answer,
                    'created_at' => $answer->createdAt,
                ];
            },
            $answers
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
                'شناسه پاسخ نامعتبر است.',
                422
            );
        }

        $answer =
            $this->service->findById($id);

        if ($answer === null) {
            $this->error(
                'پاسخ موردنظر پیدا نشد.',
                404
            );
        }

        $this->success([
            'answer' => $answer,
        ]);
    }

    public function adminStore(
        array $parameters = []
    ): never {
        $adminUserId =
            $this->currentAdminUserId();

        $questionId =
            (int)($parameters['questionId'] ?? 0);

        if ($questionId <= 0) {
            $this->error(
                'شناسه پرسش نامعتبر است.',
                422
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $answer =
            trim(
                (string)($data['answer'] ?? '')
            );

        if ($answer === '') {
            $this->error(
                'متن پاسخ الزامی است.',
                422
            );
        }

        try {
            $created =
                $this->service->create(
                    $questionId,
                    $adminUserId,
                    $answer
                );

            if ($created === null) {
                $this->error(
                    'ایجاد پاسخ انجام نشد.',
                    500
                );
            }

            $this->success(
                $created,
                'پاسخ با موفقیت ثبت شد.'
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

    public function adminStatus(
        array $parameters = []
    ): never {
        $this->currentAdminUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه پاسخ نامعتبر است.',
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
                'وضعیت پاسخ الزامی است.',
                422
            );
        }

        try {
            $answer =
                $this->service->moderate(
                    $id,
                    $status
                );

            if ($answer === null) {
                $this->error(
                    'پاسخ موردنظر پیدا نشد.',
                    404
                );
            }

            $this->success(
                $answer,
                'وضعیت پاسخ با موفقیت تغییر کرد.'
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
