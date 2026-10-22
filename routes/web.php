<?php

declare(strict_types=1);

return static function (
    Router $router
): void {

    /*
    |--------------------------------------------------------------------------
    | Customer Authentication
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/login',
        [AuthController::class, 'login'],
        [
            CsrfMiddleware::class,
        ],
        'auth.login'
    );

    $router->post(
        '/register',
        [AuthController::class, 'register'],
        [
            CsrfMiddleware::class,
        ],
        'auth.register'
    );

    $router->post(
        '/logout',
        [AuthController::class, 'logout'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'auth.logout'
    );

    $router->post(
        '/logout-other-sessions',
        [AuthController::class, 'logoutOtherSessions'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Customer Account
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/account/sessions',
        [AuthController::class, 'sessions'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->post(
        '/account/security',
        [AuthController::class, 'securitySummary'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->post(
        '/account/sessions/revoke',
        [AuthController::class, 'revokeSession'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->post(
        '/account/password',
        [AuthController::class, 'changePassword'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.password.change'
    );

    $router->get(
        '/account/verification-status',
        [AuthController::class, 'verificationStatus'],
        [
            AuthMiddleware::class,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Password / Email
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/forgot-password',
        [AuthController::class, 'forgotPassword'],
        [
            CsrfMiddleware::class,
        ],
        'auth.forgot-password'
    );

    $router->post(
        '/reset-password',
        [AuthController::class, 'resetPassword'],
        [
            CsrfMiddleware::class,
        ],
        'auth.reset-password'
    );

    $router->post(
        '/verify-email',
        [AuthController::class, 'verifyEmail'],
        [
            CsrfMiddleware::class,
        ],
        'auth.verify-email'
    );

    $router->post(
        '/resend-email-verification',
        [AuthController::class, 'resendEmailVerification'],
        [
            CsrfMiddleware::class,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Admin Authentication
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/admin/login',
        [AdminController::class, 'loginPage'],
        [],
        'admin.login'
    );

    $router->post(
        '/admin/login',
        [AdminAuthController::class, 'login'],
        [
            CsrfMiddleware::class,
        ],
        'admin.auth.login'
    );

    $router->post(
        '/admin/logout',
        [AdminAuthController::class, 'logout'],
        [
            AdminAuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.auth.logout'
    );

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/admin',
        [AdminController::class, 'dashboard'],
        [
            AdminAuthMiddleware::class,
        ],
        'admin.dashboard'
    );

    /*
    |--------------------------------------------------------------------------
    | Admin Authentication API
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/admin/me',
        [AdminAuthController::class, 'me'],
        [
            AdminAuthMiddleware::class,
        ],
        'admin.auth.me'
    );

    $router->get(
        '/admin/auth/status',
        [AdminAuthController::class, 'status'],
        [],
        'admin.auth.status'
    );
};
