<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';

$returnId =
    (int)($_GET['id'] ?? 0);

$repository =
    new ReturnRepository();

$return =
    $repository->findById($returnId);

if ($return === null) {
    http_response_code(404);
    exit('مرجوعی پیدا نشد.');
}

$items =
    $repository->findItems($returnId);

$refunds =
    (new RefundRepository())
    ->findByReturnId($returnId);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>جزئیات مرجوعی</title>
</head>

<body>
    <main>
        <h1>
            <?= htmlspecialchars(
                $return->returnNumber,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <p>
            وضعیت:
            <?= htmlspecialchars(
                $return->status,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <h2>اقلام</h2>

        <ul>
            <?php foreach ($items as $item): ?>
                <li>
                    #<?= (int)$item->orderItemId ?>
                    —
                    <?= (int)$item->quantity ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>بازپرداخت‌ها</h2>

        <ul>
            <?php foreach ($refunds as $refund): ?>
                <li>
                    <?= htmlspecialchars(
                        $refund->refundNumber,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    —

                    <?= htmlspecialchars(
                        $refund->status,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>

</html>