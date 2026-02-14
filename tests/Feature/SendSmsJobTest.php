<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class SendSmsJobTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_job_creates_sms_log_on_success_in_mock_mode(): void
    {
        config(['services.netgsm.mock' => true]);

        $job = new SendSmsJob(
            tenantId: $this->tenant->id,
            customerId: null,
            phone: '5551112233',
            type: 'due_today',
            message: 'Test mesaji',
            contextType: 'receivable',
            contextId: 1,
        );

        $job->handle();

        $this->assertDatabaseHas('sms_logs', [
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
            'type' => 'due_today',
            'status' => 'sent',
            'context_type' => 'receivable',
            'context_id' => 1,
        ]);

        $log = SmsLog::first();
        $this->assertStringStartsWith('mock-', $log->provider_message_id);
        $this->assertNotNull($log->sent_at);
    }

    public function test_job_creates_failed_log_when_credentials_missing(): void
    {
        config([
            'services.netgsm.mock' => false,
            'services.netgsm.user_code' => '',
            'services.netgsm.password' => '',
            'services.netgsm.header' => '',
        ]);

        $job = new SendSmsJob(
            tenantId: $this->tenant->id,
            customerId: null,
            phone: '5551112233',
            type: 'overdue',
            message: 'Test mesaji',
        );

        $job->handle();

        $this->assertDatabaseHas('sms_logs', [
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
            'status' => 'failed',
        ]);

        $log = SmsLog::first();
        $this->assertNotNull($log->error);
    }

    public function test_job_has_correct_retry_config(): void
    {
        $job = new SendSmsJob(
            tenantId: $this->tenant->id,
            customerId: null,
            phone: '555',
            type: 'due_today',
            message: 'test',
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300, 900], $job->backoff);
        $this->assertEquals(30, $job->timeout);
    }
}
