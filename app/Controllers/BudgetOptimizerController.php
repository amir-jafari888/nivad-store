<?php

declare(strict_types=1);

final class BudgetOptimizerController
{
    private BudgetOptimizerService $optimizer;

    public function __construct(
        ?BudgetOptimizerService $optimizer = null
    ) {
        $this->optimizer =
            $optimizer ??
            new BudgetOptimizerService();
    }

    public function optimize(): void
    {
        $deviceId =
            filter_input(
                INPUT_GET,
                'device_id',
                FILTER_VALIDATE_INT
            );

        $budget =
            trim(
                (string) (
                    $_GET['budget'] ?? ''
                )
            );

        $result =
            $this->optimizer->optimize(
                (int) ($deviceId ?: 0),
                $budget
            );

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        http_response_code(
            $result['success'] === false
                ? 400
                : 200
        );

        echo json_encode(
            $result,
            JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
        );
    }
}
