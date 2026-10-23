<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';

Bootstrap::boot();

if (Auth::id() === null) {
    http_response_code(401);
    exit('دسترسی غیرمجاز.');
}

$stmt =
    Database::connection()->prepare(
        'SELECT *
         FROM refunds
         ORDER BY created_at DESC, id DESC'
    );

$stmt->execute();

$refunds = [];

while (
    $row =
    $stmt->fetch(PDO::FETCH_ASSOC)
) {
    $refunds[] =
        Refund::fromArray($row);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>مدیریت بازپرداخت‌ها</title>

    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script defer src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>

<body>
    <main>
        <h1>مدیریت بازپرداخت‌ها</h1>

        <ul>
            <?php foreach ($refunds as $refund): ?>
                <li>
                    <a
                        href="show.php?id=<?= (int)$refund->id ?>">
                        <?= htmlspecialchars(
                            $refund->refundNumber,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>

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