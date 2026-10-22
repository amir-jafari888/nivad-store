<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Setup | نیواد</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: Tahoma, Arial
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px
        }

        a {
            display: inline-block;
            padding: 10px 15px;
            background: #111827;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            margin: 5px
        }
    </style>
</head>

<body>

    <main>
        <h1>مدیریت Setup</h1>

        <div class="card">
            <p>دستگاه‌های فعال Setup</p>
            <a href="devices.php">مشاهده دستگاه‌ها</a>
            <a href="accessories.php">مشاهده سازگاری اکسسوری</a>
        </div>

    </main>

</body>

</html>