<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';

Bootstrap::boot();

$userId = Auth::id();

if ($userId === null) {
    http_response_code(401);
    exit('دسترسی غیرمجاز.');
}

$returnId =
    (int)($_GET['id'] ?? 0);

$repository =
    new ReturnRepository();

$return =
    $repository->findById($returnId);

if (
    $return === null
    || $return->userId !== $userId
) {
    http_response_code(404);
    exit('درخواست مرجوعی پیدا نشد.');
}

$items =
    $repository->findItems($returnId);

$history =
    $repository->findStatusHistory($returnId);
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
                    سفارش #<?= (int)$item->orderItemId ?>
                    —
                    تعداد <?= (int)$item->quantity ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>تاریخچه وضعیت</h2>

        <ul>
            <?php foreach ($history as $entry): ?>
                <li>
                    <?= htmlspecialchars(
                        $entry->toStatus,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>

</html>