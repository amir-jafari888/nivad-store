<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';

Bootstrap::boot();

if (Auth::id() === null) {
    http_response_code(401);
    exit('دسترسی غیرمجاز.');
}

$refundId =
    (int)($_GET['id'] ?? 0);

$refund =
    (new RefundRepository())
    ->findById($refundId);

if ($refund === null) {
    http_response_code(404);
    exit('بازپرداخت پیدا نشد.');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>جزئیات بازپرداخت</title>

    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>

<body>
    <main>
        <h1>
            <?= htmlspecialchars(
                $refund->refundNumber,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <p>
            مبلغ:
            <?= htmlspecialchars(
                $refund->amount,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            ارز:
            <?= htmlspecialchars(
                $refund->currency,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            وضعیت:
            <?= htmlspecialchars(
                $refund->status,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            سفارش:
            <?= (int)$refund->orderId ?>
        </p>

        <p>
            مرجوعی:
            <?= (int)$refund->returnId ?>
        </p>
    </main>
</body>

</html>