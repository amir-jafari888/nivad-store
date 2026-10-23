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
         FROM returns
         ORDER BY requested_at DESC, id DESC'
    );

$stmt->execute();

$returns = [];

while (
    $row =
    $stmt->fetch(PDO::FETCH_ASSOC)
) {
    $returns[] =
        ReturnRequest::fromArray($row);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>مدیریت مرجوعی‌ها</title>

    <link rel="stylesheet" href="<?= e(url('/assets/css/admin/admin.css')) ?>">
    <script defer src="<?= e(url('/assets/js/admin-theme.js')) ?>"></script>
</head>

<body>
    <main>
        <h1>مدیریت مرجوعی‌ها</h1>

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
    </main>
</body>

</html>