<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MailLog;
use App\Models\NotificationTemplate;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Services\NotificationService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_templates_are_used_for_sms_and_mail_bodies(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tmpl Co', 'is_active' => true,
            'sms_enabled' => true, 'mail_enabled' => true,
        ]);

        TenantContext::run($tenant->id, function () use ($tenant) {
            NotificationTemplate::create([
                'tenant_id' => $tenant->id, 'channel' => 'sms', 'type' => 'due_today',
                'body' => 'SMS-OZEL {ad} {tutar}',
            ]);
            NotificationTemplate::create([
                'tenant_id' => $tenant->id, 'channel' => 'mail', 'type' => 'due_today',
                'body' => 'MAIL-OZEL {ad} {tutar}',
            ]);

            $customer = Customer::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Ali Veli',
                'phone' => '5551112233',
                'email' => 'ali@example.com',
            ]);

            $receivable = Receivable::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'amount' => 250, 'status' => 'due_today', 'paid_at' => null,
                'due_date' => now(),
            ]);

            app(NotificationService::class)->dispatchForReceivable($receivable);
        });

        // Queue is sync in tests, so the jobs have run and written their logs.
        $sms = SmsLog::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
        $mail = MailLog::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($sms);
        $this->assertStringContainsString('SMS-OZEL Ali Veli', $sms->message);
        $this->assertNotNull($mail);
        $this->assertStringContainsString('MAIL-OZEL Ali Veli', $mail->body);
    }
}
