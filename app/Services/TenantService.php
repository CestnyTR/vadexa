<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Support\TenantContext;

class TenantService
{
    public function current(): Tenant
    {
        return Tenant::findOrFail(TenantContext::tenantId());
    }

    /**
     * Create a fresh tenant for a new registration, on trial, with default
     * notification templates seeded.
     */
    public function createForRegistration(string $company): Tenant
    {
        $tenant = Tenant::create([
            'name' => $company,
            'plan' => config('plans.default'),
            'trial_ends_at' => now()->addDays((int) config('plans.trial_days')),
            'is_active' => true,
            'notification_time' => '10:00',
            'sms_enabled' => true,
            'mail_enabled' => true,
        ]);

        $this->seedDefaultTemplates($tenant);

        return $tenant;
    }

    /**
     * Seed a tenant with the built-in default notification templates.
     */
    public function seedDefaultTemplates(Tenant $tenant): void
    {
        TenantContext::run($tenant->id, function () use ($tenant): void {
            foreach (NotificationTemplate::CHANNELS as $channel) {
                foreach (NotificationTemplate::TYPES as $type) {
                    NotificationTemplate::query()->firstOrCreate(
                        ['tenant_id' => $tenant->id, 'channel' => $channel, 'type' => $type],
                        ['body' => $this->defaultTemplateBody($channel, $type)],
                    );
                }
            }
        });
    }

    public function updateSettings(array $data): bool
    {
        return $this->current()->update($data);
    }

    /**
     * Effective plan for the tenant, honouring the trial upgrade.
     *
     * @return array<string, mixed>
     */
    public function effectivePlan(Tenant $tenant): array
    {
        if ($tenant->onTrial()) {
            $plans = config('plans.plans');

            return $plans[config('plans.trial_plan')] ?? $tenant->plan();
        }

        return $tenant->plan();
    }

    public function effectiveLimit(Tenant $tenant, string $key): ?int
    {
        $value = $this->effectivePlan($tenant)['limits'][$key] ?? null;

        return $value === null ? null : (int) $value;
    }

    protected function defaultTemplateBody(string $channel, string $type): string
    {
        return match ($type) {
            'upcoming' => '{ad}, {vade} tarihli {tutar} TL odemeniz yarin vadelidir.',
            'due_today' => '{ad}, {tutar} TL odemenizin vadesi bugundur ({vade}).',
            'overdue' => '{ad}, {vade} vadeli {tutar} TL odemeniz gecikmededir.',
            default => '{ad}, {tutar} TL odemeniz icin bilgilendirme mesajidir.',
        };
    }
}
