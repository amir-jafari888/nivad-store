<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/Core/Bootstrap.php';

Bootstrap::boot();

$userId = Auth::id();

if ($userId === null) {
    http_response_code(401);
    exit('برای مشاهده مرجوعی‌ها باید وارد حساب شوید.');
}

$returns =
    (new ReturnRepository())
    ->findByUserId($userId);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>درخواست‌های مرجوعی</title>
</head>

<body>
    <main>
        <h1>درخواست‌های مرجوعی</h1>

        <?php if ($returns === []): ?>

            <p>هنوز درخواست مرجوعی ثبت نشده است.</p>

        <?php else: ?>

            <ul>
                <?php foreach ($returns as $return): ?>

                    <li>
                        <a
                            href="show.php?id=<?= (int)$return->id ?>">
                            <?= htmlspecialchars(
                                $return->returnNumber,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </a>

                        —

                        <?= htmlspecialchars(
                            $return->status,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </li>

                <?php endforeach; ?>
            </ul>

        <?php endif; ?>
    </main>
</body>

</html>