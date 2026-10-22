<?php

declare(strict_types=1);

final class AdminController extends Controller
{
    public function dashboard(): never
    {
        $service =
            new AdminAuthService();

        if (
            !$service->validateCurrentSession()
        ) {
            Response::redirect(
                url('/admin/login')
            );
        }

        $admin =
            $service->currentSafe();

        if ($admin === null) {
            Response::redirect(
                url('/admin/login')
            );
        }

        $metricsService =
            new AdminDashboardMetricsService();

        $metrics =
            $metricsService->metrics();

        $this->view(
            'admin.dashboard',
            [
                'admin' => $admin,
                'metrics' => $metrics,
            ]
        );
    }

    public function reports(): never
    {
        $this->view(
            'admin.reports.index'
        );
    }

    public function salesReports(): never
    {
        $this->view(
            'admin.reports.sales'
        );
    }

    public function orderReports(): never
    {
        $this->view(
            'admin.reports.orders'
        );
    }

    public function productReports(): never
    {
        $this->view(
            'admin.reports.products'
        );
    }

    public function categoryReports(): never
    {
        $this->view(
            'admin.reports.categories'
        );
    }

    public function loginPage(): never
    {
        $service =
            new AdminAuthService();

        if (
            $service->validateCurrentSession()
        ) {
            Response::redirect(
                url('/admin')
            );
        }

        $this->view(
            'admin.auth.login',
            [
                'csrf_token' => Csrf::token(),
            ]
        );
    }
}
