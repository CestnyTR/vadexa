<?php

namespace App\Support;

use App\Jobs\SendDueMailJob;
use App\Jobs\SendSmsJob;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Tenant;
use Carbon\Carbon;

class NotificationDispatcher
{
    public static function dispatchForReceivable(Receivable $receivable): array
    {
        $receivable->loadMissing('customer');

        if (! $receivable->customer) {
            return ['sms' => false, 'mail' => false, 'reason' => 'no_customer'];
        }

        $type = static::resolveType($receivable);
        $today = Carbon::today();
        $smsDispatched = false;
        $mailDispatched = false;

        $tenant = Tenant::find($receivable->tenant_id);
        $enableSms = $tenant ? $tenant->sms_enabled : (bool) config('services.notifications.enable_sms', true);
        $enableMail = $tenant ? $tenant->mail_enabled : (bool) config('services.notifications.enable_mail', true);

        // SMS dispatch with idempotency
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
                    message: static::buildSmsMessage(
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

        // Mail dispatch with idempotency
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

    public static function resolveType(Receivable $receivable): string
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

    protected static function buildSmsMessage(string $customerName, string $amount, Carbon $dueDate, string $type): string
    {
        return match ($type) {
            'upcoming' => "{$customerName}, {$dueDate->format('d.m.Y')} tarihli {$amount} TL odemeniz yarin vadelidir.",
            'due_today' => "{$customerName}, {$amount} TL odemenizin vadesi bugundur ({$dueDate->format('d.m.Y')}).",
            'overdue' => "{$customerName}, {$dueDate->format('d.m.Y')} vadeli {$amount} TL odemeniz gecikmededir.",
            default => "{$customerName}, {$amount} TL odemeniz icin bilgilendirme mesajidir.",
        };
    }
}
