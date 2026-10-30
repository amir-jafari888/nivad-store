<?php

declare(strict_types=1);

final class CustomerSupportController extends Controller
{
    private AdminCapabilityService $service;

    public function __construct(?AdminCapabilityService $service = null)
    {
        $this->service = $service ?? new AdminCapabilityService();
    }

    private function customerId(): int
    {
        $id = Auth::id();
        if ($id === null || $id < 1) $this->error('برای استفاده از پشتیبانی وارد حساب کاربری شوید.', 401);
        return $id;
    }

    private function payload(): array
    {
        return Request::isJson() ? Request::json() : $_POST;
    }

    public function index(): never
    {
        $this->success(['items' => $this->service->customerTickets($this->customerId())], 'تیکت‌های شما دریافت شدند.');
    }

    public function show(array $parameters = []): never
    {
        $ticket = $this->service->customerTicket((int)($parameters['id'] ?? 0), $this->customerId());
        if ($ticket === null) $this->error('تیکت پیدا نشد.', 404);
        $this->success(['ticket' => $ticket], 'جزئیات تیکت دریافت شد.');
    }

    public function store(): never
    {
        try {
            $userId = $this->customerId();
            $id = $this->service->createCustomerTicket($userId, $this->payload());
            AdminAuditTrail::record(null, 'customer_create', 'customer_support', 'admin_support_ticket', $id, 'Customer submitted a support ticket.', null, ['customer_id' => $userId]);
            try {
                $this->service->createNotification(0, [
                    'recipient_admin_user_id' => null,
                    'category' => 'customer_support',
                    'severity' => 'info',
                    'title' => 'درخواست پشتیبانی جدید',
                    'message' => 'یک مشتری درخواست پشتیبانی جدید ثبت کرده است.',
                    'source_type' => 'support_ticket',
                    'source_id' => $id,
                    'dedupe_key' => 'customer-ticket-created:' . $id,
                ]);
            } catch (Throwable $notificationFailure) {
                Logger::error('Customer ticket was created but Admin notification failed.', [
                    'ticket_id' => $id,
                    'exception_type' => get_class($notificationFailure),
                ]);
            }
            $this->success(['id' => $id], 'درخواست پشتیبانی ثبت شد.', 201);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }

    public function reply(array $parameters = []): never
    {
        try {
            $userId = $this->customerId();
            $data = $this->payload();
            $id = $this->service->addCustomerTicketMessage(
                (int)($parameters['id'] ?? 0),
                $userId,
                (string)($data['message'] ?? '')
            );
            AdminAuditTrail::record(null, 'customer_reply', 'customer_support', 'admin_support_message', $id, 'Customer replied to a support ticket.', null, ['customer_id' => $userId]);
            try {
                $this->service->createNotification(0, [
                    'recipient_admin_user_id' => null,
                    'category' => 'customer_support',
                    'severity' => 'info',
                    'title' => 'پیام جدید در تیکت پشتیبانی',
                    'message' => 'مشتری در یک تیکت پشتیبانی پیام جدیدی ارسال کرده است.',
                    'source_type' => 'support_ticket',
                    'source_id' => (int)($parameters['id'] ?? 0),
                    'dedupe_key' => 'customer-ticket-message:' . $id,
                ]);
            } catch (Throwable $notificationFailure) {
                Logger::error('Customer ticket reply was created but Admin notification failed.', [
                    'ticket_id' => (int)($parameters['id'] ?? 0),
                    'message_id' => $id,
                    'exception_type' => get_class($notificationFailure),
                ]);
            }
            $this->success(['id' => $id], 'پیام شما ثبت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
