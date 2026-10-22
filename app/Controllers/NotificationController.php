<?php

declare(strict_types=1);

final class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct(
        ?NotificationService $service = null
    ) {
        $this->service =
            $service ??
            new NotificationService();
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

    public function index(): never
    {
        $userId = $this->currentUserId();

        $items =
            $this->service->findByUserId(
                $userId
            );

        $this->success([
            'items' => $items,
        ]);
    }

    public function unread(): never
    {
        $userId = $this->currentUserId();

        $items =
            $this->service->findUnreadByUserId(
                $userId
            );

        $this->success([
            'items' => $items,
            'count' =>
            $this->service->countUnreadByUserId(
                $userId
            ),
        ]);
    }

    public function count(): never
    {
        $userId = $this->currentUserId();

        $this->success([
            'count' =>
            $this->service->countUnreadByUserId(
                $userId
            ),
        ]);
    }

    public function show(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اعلان نامعتبر است.',
                422
            );
        }

        $notification =
            $this->service->findById($id);

        if ($notification === null) {
            $this->error(
                'اعلان موردنظر پیدا نشد.',
                404
            );
        }

        if ($notification->userId !== $userId) {
            $this->error(
                'دسترسی به این اعلان مجاز نیست.',
                403
            );
        }

        $this->success([
            'notification' => $notification,
        ]);
    }

    public function markAsRead(
        array $parameters = []
    ): never {
        $userId = $this->currentUserId();

        $id =
            (int)($parameters['id'] ?? 0);

        if ($id <= 0) {
            $this->error(
                'شناسه اعلان نامعتبر است.',
                422
            );
        }

        $notification =
            $this->service->findById($id);

        if ($notification === null) {
            $this->error(
                'اعلان موردنظر پیدا نشد.',
                404
            );
        }

        if ($notification->userId !== $userId) {
            $this->error(
                'دسترسی به این اعلان مجاز نیست.',
                403
            );
        }

        $updated =
            $this->service->markAsRead($id);

        if ($updated === null) {
            $this->error(
                'به‌روزرسانی اعلان انجام نشد.',
                500
            );
        }

        $this->success(
            $updated,
            'اعلان به‌عنوان خوانده‌شده ثبت شد.'
        );
    }

    public function markAllAsRead(): never
    {
        $userId = $this->currentUserId();

        $count =
            $this->service->markAllAsRead(
                $userId
            );

        $this->success([
            'updated' => $count,
        ], 'اعلان‌ها به‌عنوان خوانده‌شده ثبت شدند.');
    }

    public function adminCreate(): never
    {
        $this->currentAdminUserId();

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $userId =
            (int)($data['user_id'] ?? 0);

        $type =
            trim(
                (string)($data['type'] ?? '')
            );

        $title =
            trim(
                (string)($data['title'] ?? '')
            );

        $message =
            trim(
                (string)($data['message'] ?? '')
            );

        $notificationData =
            $data['data'] ?? null;

        if ($userId <= 0) {
            $this->error(
                'شناسه کاربر نامعتبر است.',
                422
            );
        }

        if ($type === '') {
            $this->error(
                'نوع اعلان الزامی است.',
                422
            );
        }

        if ($title === '') {
            $this->error(
                'عنوان اعلان الزامی است.',
                422
            );
        }

        if ($message === '') {
            $this->error(
                'متن اعلان الزامی است.',
                422
            );
        }

        if (
            $notificationData !== null
            && !is_array($notificationData)
        ) {
            $this->error(
                'داده اعلان باید به‌صورت آرایه ارسال شود.',
                422
            );
        }

        try {
            $notification =
                $this->service->createForUser(
                    $userId,
                    $type,
                    $title,
                    $message,
                    $notificationData
                );

            $this->success(
                $notification,
                'اعلان با موفقیت ایجاد شد.'
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }
    }
}
