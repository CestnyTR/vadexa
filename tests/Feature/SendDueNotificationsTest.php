<?php

namespace Tests\Feature;

use App\Jobs\SendDueMailJob;
use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class SendDueNotificationsTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_command_dispatches_sms_for_due_receivables(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'upcoming',
            'due_date' => today(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertPushed(SendSmsJob::class);
    }

    public function test_command_dispatches_mail_for_due_receivables(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
            'email' => 'test@example.com',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
            'due_date' => today()->subDays(3),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertPushed(SendDueMailJob::class);
    }

    public function test_command_skips_paid_receivables(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'due_date' => today(),
            'paid_at' => now(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertNotPushed(SendSmsJob::class);
        Queue::assertNotPushed(SendDueMailJob::class);
    }

    public function test_command_skips_customers_without_phone(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'upcoming',
            'due_date' => today(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertNotPushed(SendSmsJob::class);
    }

    public function test_command_is_idempotent_for_sms(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'upcoming',
            'due_date' => today(),
        ]);

        // Simulate already sent today
        SmsLog::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'phone' => '5551112233',
            'type' => 'due_today',
            'message' => 'test',
            'status' => 'sent',
            'context_type' => 'receivable',
            'context_id' => $receivable->id,
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertNotPushed(SendSmsJob::class);
    }

    public function test_command_respects_sms_disable_config(): void
    {
        Queue::fake();
        $this->tenant->update(['sms_enabled' => false]);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'upcoming',
            'due_date' => today(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertNotPushed(SendSmsJob::class);
    }

    public function test_command_respects_mail_disable_config(): void
    {
        Queue::fake();
        $this->tenant->update(['mail_enabled' => false]);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
            'email' => 'test@example.com',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
            'due_date' => today()->subDay(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertNotPushed(SendDueMailJob::class);
    }

    public function test_command_dispatches_correct_type_for_overdue(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->type === 'overdue';
        });
    }

    public function test_command_dispatches_upcoming_for_tomorrow(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
        ]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'upcoming',
            'due_date' => today()->addDay(),
        ]);

        $this->artisan('notifications:send-due')->assertSuccessful();

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->type === 'upcoming';
        });
    }
}
