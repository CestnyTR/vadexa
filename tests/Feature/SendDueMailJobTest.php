<?php

namespace Tests\Feature;

use App\Jobs\SendDueMailJob;
use App\Models\MailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class SendDueMailJobTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_job_sends_mail_and_creates_success_log(): void
    {
        $job = new SendDueMailJob(
            tenantId: $this->tenant->id,
            customerId: null,
            email: 'test@example.com',
            type: 'due_today',
            customerName: 'Ahmet Yilmaz',
            amount: '1500.00',
            dueDate: '14.02.2026',
        );

        $job->handle();

        $this->assertDatabaseHas('mail_logs', [
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
            'type' => 'due_today',
            'status' => 'success',
        ]);

        $log = MailLog::first();
        $this->assertNotNull($log->sent_at);
        $this->assertNotEmpty($log->subject);
        $this->assertNotEmpty($log->body);
    }

    public function test_job_creates_correct_subject_for_types(): void
    {
        $types = [
            'upcoming' => 'Odeme Hatirlatmasi',
            'due_today' => 'Bugun Vadesi Gelen',
            'overdue' => 'Geciken Odeme',
        ];

        foreach ($types as $type => $expectedSubjectPart) {
            $job = new SendDueMailJob(
                tenantId: $this->tenant->id,
                customerId: null,
                email: "test-{$type}@example.com",
                type: $type,
                customerName: 'Test',
                amount: '100.00',
                dueDate: '14.02.2026',
            );

            $job->handle();

            $log = MailLog::where('email', "test-{$type}@example.com")->first();
            $this->assertStringContains($expectedSubjectPart, $log->subject, "Subject for type '{$type}' should contain '{$expectedSubjectPart}'");
        }
    }

    public function test_job_has_correct_retry_config(): void
    {
        $job = new SendDueMailJob(
            tenantId: $this->tenant->id,
            customerId: null,
            email: 'test@example.com',
            type: 'due_today',
            customerName: 'Test',
            amount: '100',
            dueDate: '14.02.2026',
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300, 900], $job->backoff);
        $this->assertEquals(30, $job->timeout);
    }

    /**
     * Custom assertion since assertStringContainsString is verbose.
     */
    private function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }
}
