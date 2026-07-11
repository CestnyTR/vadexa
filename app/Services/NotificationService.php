<?php

namespace App\Services;

use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Jobs\SendDueMailJob;
use App\Jobs\SendSmsJob;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;

class NotificationService
{
    public function __construct(
        private ReceivableRepositoryInterface $receivables,
    ) {}

    public function dispatchForReceivable(Receivable $receivable): array
    {
        $receivable->loadMissing('customer');

        if (! $receivable->customer) {
            return ['sms' => false, 'mail' => false, 'reason' => 'no_customer'];
        }

        $type = $this->resolveType($receivable);
        $today = Carbon::today();
        $smsDispatched = false;
        $mailDispatched = false;

        $tenant = Tenant::find($receivable->tenant_id);
        $enableSms = $tenant ? $tenant->sms_enabled : (bool) config('services.notifications.enable_sms', true);
        $enableMail = $tenant ? $tenant->mail_enabled : (bool) config('services.notifications.enable_mail', true);

        if ($enableSms && filled($receivable->customer->phone)) {
            $alreadySent = SmsLog::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $receivable->tenant_id)
                ->where('context_type', 'receivable')
                ->where('context_id', $receivable->id)
                ->where('type', $type)
                ->whereDate('created_at', $today)
                ->exists();

            if (! $alreadySent) {
                SendSmsJob::dispatch(
                    tenantId: $receivable->tenant_id,
                    customerId: $receivable->customer_id,
                    phone: $receivable->customer->phone,
                    type: $type,
                    message: $this->buildSmsMessage(
                        customerName: $receivable->customer->name,
                        amount: (string) $receivable->amount,
                        dueDate: $receivable->due_date,
                        type: $type,
                    ),
                    contextType: 'receivable',
                    contextId: $receivable->id,
                );
                $smsDispatched = true;
            }
        }

        if ($enableMail && filled($receivable->customer->email)) {
            $alreadySent = MailLog::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $receivable->tenant_id)
                ->where('customer_id', $receivable->customer_id)
                ->where('type', $type)
                ->whereDate('created_at', $today)
                ->exists();

            if (! $alreadySent) {
                SendDueMailJob::dispatch(
                    tenantId: $receivable->tenant_id,
                    customerId: $receivable->customer_id,
                    email: $receivable->customer->email,
                    type: $type,
                    customerName: $receivable->customer->name,
                    amount: (string) $receivable->amount,
                    dueDate: $receivable->due_date->format('d.m.Y'),
                );
                $mailDispatched = true;
            }
        }

        return ['sms' => $smsDispatched, 'mail' => $mailDispatched, 'type' => $type];
    }

    public function dispatchDueNotifications(): array
    {
        $tenantId = TenantContext::tenantId();
        $tenant = Tenant::find($tenantId);

        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();
        $smsDispatched = 0;
        $mailDispatched = 0;

        $enableSms = $tenant ? $tenant->sms_enabled : (bool) config('services.notifications.enable_sms', true);
        $enableMail = $tenant ? $tenant->mail_enabled : (bool) config('services.notifications.enable_mail', true);

        $receivables = $this->receivables->getUnpaidDueBefore($tomorrow);

        foreach ($receivables as $receivable) {
            if (! $receivable->customer) {
                continue;
            }

            $type = match (true) {
                $receivable->due_date->lt($today) => 'overdue',
                $receivable->due_date->isSameDay($today) => 'due_today',
                $receivable->due_date->isSameDay($tomorrow) => 'upcoming',
                default => null,
            };

            if ($type === null) {
                continue;
            }

            if ($enableSms && filled($receivable->customer->phone)) {
                $alreadySentSms = SmsLog::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $receivable->tenant_id)
                    ->where('context_type', 'receivable')
                    ->where('context_id', $receivable->id)
                    ->whereDate('created_at', $today)
                    ->exists();

                if (! $alreadySentSms) {
                    SendSmsJob::dispatch(
                        tenantId: $receivable->tenant_id,
                        customerId: $receivable->customer_id,
                        phone: $receivable->customer->phone,
                        type: $type,
                        message: $this->buildSmsMessage(
                            customerName: $receivable->customer->name,
                            amount: (string) $receivable->amount,
                            dueDate: $receivable->due_date,
                            type: $type,
                        ),
                        contextType: 'receivable',
                        contextId: $receivable->id,
                    );
                    $smsDispatched++;
                }
            }

            if ($enableMail && filled($receivable->customer->email)) {
                $alreadySentMail = MailLog::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $receivable->tenant_id)
                    ->where('customer_id', $receivable->customer_id)
                    ->where('type', $type)
                    ->whereDate('created_at', $today)
                    ->exists();

                if (! $alreadySentMail) {
                    SendDueMailJob::dispatch(
                        tenantId: $receivable->tenant_id,
                        customerId: $receivable->customer_id,
                        email: $receivable->customer->email,
                        type: $type,
                        customerName: $receivable->customer->name,
                        amount: (string) $receivable->amount,
                        dueDate: $receivable->due_date->format('d.m.Y'),
                    );
                    $mailDispatched++;
                }
            }
        }

        return ['sms' => $smsDispatched, 'mail' => $mailDispatched];
    }

    protected function resolveType(Receivable $receivable): string
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        return match (true) {
            $receivable->due_date->lt($today) => 'overdue',
            $receivable->due_date->isSameDay($today) => 'due_today',
            $receivable->due_date->isSameDay($tomorrow) => 'upcoming',
            $receivable->due_date->gt($tomorrow) => 'upcoming',
            default => 'due_today',
        };
    }

    protected function buildSmsMessage(string $customerName, string $amount, Carbon $dueDate, string $type): string
    {
        return match ($type) {
            'upcoming' => "{$customerName}, {$dueDate->format('d.m.Y')} tarihli {$amount} TL odemeniz yarin vadelidir.",
            'due_today' => "{$customerName}, {$amount} TL odemenizin vadesi bugundur ({$dueDate->format('d.m.Y')}).",
            'overdue' => "{$customerName}, {$dueDate->format('d.m.Y')} vadeli {$amount} TL odemeniz gecikmededir.",
            default => "{$customerName}, {$amount} TL odemeniz icin bilgilendirme mesajidir.",
        };
    }
}
