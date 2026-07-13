<?php

namespace Tests\Feature;

use App\Jobs\SendDueMailJob;
use App\Models\Customer;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\Tenant;
use App\Services\NotificationService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MailQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_is_not_dispatched_when_monthly_quota_reached(): void
    {
        Queue::fake();
        config()->set('plans.plans.free.limits.mail_per_month', 1);

        $tenant = Tenant::create([
            'name' => 'Quota Co', 'plan' => 'free', 'is_active' => true,
            'sms_enabled' => false, 'mail_enabled' => true,
        ]);

        TenantContext::run($tenant->id, function () use ($tenant) {
            $customer = Customer::factory()->create([
                'tenant_id' => $tenant->id, 'email' => 'client@example.com',
            ]);

            // Already used this month's single mail allowance.
            MailLog::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'email' => 'client@example.com', 'created_at' => now(),
            ]);

            Receivable::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'amount' => 100, 'status' => 'due_today', 'paid_at' => null,
                'due_date' => now(),
            ]);

            app(NotificationService::class)->dispatchDueNotifications();
        });

        Queue::assertNotPushed(SendDueMailJob::class);
    }
}
