<?php

namespace App\Console\Commands;

use App\Jobs\SendDueMailJob;
use App\Jobs\SendSmsJob;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendDueNotifications extends Command
{
    protected $signature = 'notifications:send-due';

    protected $description = 'Dispatch due/overdue receivable SMS and mail notifications';

    public function handle(): int
    {
        $tenantId = TenantContext::tenantId();
        $tenant = Tenant::find($tenantId);

        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();
        $smsDispatched = 0;
        $mailDispatched = 0;

        $enableSms = $tenant ? $tenant->sms_enabled : (bool) config('services.notifications.enable_sms', true);
        $enableMail = $tenant ? $tenant->mail_enabled : (bool) config('services.notifications.enable_mail', true);

        $receivables = Receivable::query()
            ->with('customer')
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<=', $tomorrow)
            ->get();

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

            // SMS dispatch with idempotency check
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

            // Mail dispatch with idempotency check
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

        $this->info("Dispatched {$smsDispatched} SMS and {$mailDispatched} mail job(s).");

        return CommandAlias::SUCCESS;
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
