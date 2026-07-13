<?php

namespace App\Services;

use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Jobs\SendDueMailJob;
use App\Jobs\SendSmsJob;
use App\Models\MailLog;
use App\Models\NotificationTemplate;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;

class NotificationService
{
    public function __construct(
        private ReceivableRepositoryInterface $receivables,
        private TenantService $tenants,
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

        if ($enableSms && filled($receivable->customer->phone) && ! $this->smsQuotaReached($tenant)) {
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
                    message: $this->buildMessage('sms', $receivable, $type),
                    contextType: 'receivable',
                    contextId: $receivable->id,
                );
                $smsDispatched = true;
            }
        }

        if ($enableMail && filled($receivable->customer->email) && ! $this->mailQuotaReached($tenant)) {
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
                    message: $this->buildMessage('mail', $receivable, $type),
                );
                $mailDispatched = true;
            }
        }

        return ['sms' => $smsDispatched, 'mail' => $mailDispatched, 'type' => $type];
    }

    /**
     * Dispatch due notifications for every active tenant. Used by the scheduler
     * and the manual "run notifications" command.
     */
    public function dispatchDueForAllTenants(bool $respectSchedule = false): array
    {
        $totals = ['sms' => 0, 'mail' => 0, 'tenants' => 0];

        Tenant::query()->where('is_active', true)->each(function (Tenant $tenant) use (&$totals, $respectSchedule): void {
            if ($respectSchedule && ! $this->isTenantDueTime($tenant)) {
                return;
            }

            $result = TenantContext::run($tenant->id, fn () => $this->dispatchDueNotifications());

            $totals['sms'] += $result['sms'];
            $totals['mail'] += $result['mail'];
            $totals['tenants']++;
        });

        return $totals;
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

            if ($enableSms && filled($receivable->customer->phone) && ! $this->smsQuotaReached($tenant)) {
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
                        message: $this->buildMessage('sms', $receivable, $type),
                        contextType: 'receivable',
                        contextId: $receivable->id,
                    );
                    $smsDispatched++;
                }
            }

            if ($enableMail && filled($receivable->customer->email) && ! $this->mailQuotaReached($tenant)) {
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
                        message: $this->buildMessage('mail', $receivable, $type),
                    );
                    $mailDispatched++;
                }
            }
        }

        return ['sms' => $smsDispatched, 'mail' => $mailDispatched];
    }

    /**
     * Whether the tenant's configured notification time falls in the current hour.
     */
    protected function isTenantDueTime(Tenant $tenant): bool
    {
        $time = $tenant->notification_time ?: '10:00';
        $hour = (int) explode(':', $time)[0];

        return $hour === (int) Carbon::now()->format('G');
    }

    /**
     * Whether the tenant has reached its monthly SMS quota.
     */
    protected function smsQuotaReached(?Tenant $tenant): bool
    {
        return $this->quotaReached($tenant, 'sms_per_month', SmsLog::class);
    }

    /**
     * Whether the tenant has reached its monthly e-mail quota.
     */
    protected function mailQuotaReached(?Tenant $tenant): bool
    {
        return $this->quotaReached($tenant, 'mail_per_month', MailLog::class);
    }

    /**
     * @param  class-string  $logModel
     */
    protected function quotaReached(?Tenant $tenant, string $limitKey, string $logModel): bool
    {
        if (! $tenant) {
            return false;
        }

        $limit = $this->tenants->effectiveLimit($tenant, $limitKey);

        if ($limit === null) {
            return false;
        }

        $used = $logModel::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        return $used >= $limit;
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

    /**
     * Build a notification message from the tenant's template (falling back to
     * the built-in default) for the given channel and type.
     */
    protected function buildMessage(string $channel, Receivable $receivable, string $type): string
    {
        $body = $this->templateBody($receivable->tenant_id, $channel, $type)
            ?? $this->defaultBody($type);

        return strtr($body, [
            '{ad}' => $receivable->customer->name,
            '{tutar}' => (string) $receivable->amount,
            '{vade}' => $receivable->due_date->format('d.m.Y'),
        ]);
    }

    protected function templateBody(int $tenantId, string $channel, string $type): ?string
    {
        return NotificationTemplate::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('channel', $channel)
            ->where('type', $type)
            ->value('body');
    }

    protected function defaultBody(string $type): string
    {
        return match ($type) {
            'upcoming' => '{ad}, {vade} tarihli {tutar} TL odemeniz yarin vadelidir.',
            'due_today' => '{ad}, {tutar} TL odemenizin vadesi bugundur ({vade}).',
            'overdue' => '{ad}, {vade} vadeli {tutar} TL odemeniz gecikmededir.',
            default => '{ad}, {tutar} TL odemeniz icin bilgilendirme mesajidir.',
        };
    }
}
