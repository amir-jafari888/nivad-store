<?php

declare(strict_types=1);

final class AdminController extends Controller
{
    public function dashboard(): never
    {
        $service = new AdminAuthService();

        if (!$service->validateCurrentSession()) {
            Response::redirect(url('/admin/login'));
        }

        $admin = $service->currentSafe();
        if ($admin === null) {
            Response::redirect(url('/admin/login'));
        }

        $overviewService = new AdminDashboardOverviewService();
        $permissions = $overviewService->permissions($service);
        $metrics = (new AdminDashboardMetricsService())->metrics();
        $overview = $overviewService->overview($permissions);

        // Register the same authenticated shell used by direct Admin pages.
        require_once dirname(__DIR__, 2) . '/admin/includes/auth.php';

        $this->view('admin.dashboard', [
            'admin' => $admin,
            'metrics' => $metrics,
            'overview' => $overview,
            'permissions' => $permissions,
        ]);
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
