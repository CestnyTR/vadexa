<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Receivable;
use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class CommandCenterTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_store_customer_from_command_center(): void
    {
        $response = $this->actingAs($this->user)->post(route('command-center.store-customer'), [
            'name' => 'Test Musteri',
            'phone' => '5551112233',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('customers', ['name' => 'Test Musteri', 'phone' => '5551112233']);
    }

    public function test_store_receivable_from_command_center(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => $customer->id,
            'amount' => 1500.00,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('receivables', ['customer_id' => $customer->id, 'amount' => 1500.00]);
    }

    public function test_store_task_from_command_center(): void
    {
        $response = $this->actingAs($this->user)->post(route('command-center.store-task'), [
            'title' => 'Test Gorev',
            'status' => 'todo',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tasks', ['title' => 'Test Gorev', 'status' => 'todo']);
    }

    public function test_update_receivable_from_command_center(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'amount' => 1000,
            'status' => 'upcoming',
        ]);

        $response = $this->actingAs($this->user)->put(route('command-center.update-receivable', $receivable), [
            'customer_id' => $customer->id,
            'amount' => 2000,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('receivables', ['id' => $receivable->id, 'amount' => 2000]);
    }

    public function test_mark_receivable_paid(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($this->user)->post(route('command-center.mark-paid', $receivable));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('receivables', ['id' => $receivable->id, 'status' => 'paid']);
        $this->assertNotNull($receivable->fresh()->paid_at);
    }

    public function test_send_receivable_notification(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'phone' => '5551112233',
            'email' => 'test@example.com',
        ]);
        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
            'due_date' => today()->subDays(3),
        ]);

        $response = $this->actingAs($this->user)->post(route('command-center.notify-receivable', $receivable));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_run_due_notifications(): void
    {
        $response = $this->actingAs($this->user)->post(route('command-center.run-notifications'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_store_customer_validation_errors(): void
    {
        $response = $this->actingAs($this->user)->post(route('command-center.store-customer'), [
            'name' => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_store_receivable_validation_errors(): void
    {
        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => '',
            'amount' => '',
            'due_date' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['customer_id', 'amount', 'due_date', 'status']);
    }

    public function test_store_receivable_with_monthly_installments(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
            'recurrence_type' => 'monthly',
            'installment_count' => 6,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Should create 6 receivable records
        $receivables = Receivable::where('customer_id', $customer->id)->orderBy('due_date')->get();
        $this->assertCount(6, $receivables);

        // First is parent (no parent_id), rest point to parent
        $parent = $receivables->first();
        $this->assertNull($parent->parent_id);
        $this->assertEquals(1, $parent->installment_number);
        $this->assertEquals(6, $parent->installment_count);
        $this->assertEquals('monthly', $parent->recurrence_type);
        $this->assertEquals('2026-03-01', $parent->due_date->format('Y-m-d'));

        // Check installments have correct due dates (monthly increments)
        $this->assertEquals('2026-04-01', $receivables[1]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-05-01', $receivables[2]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-06-01', $receivables[3]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-07-01', $receivables[4]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-08-01', $receivables[5]->due_date->format('Y-m-d'));

        // All child installments point to parent
        for ($i = 1; $i < 6; $i++) {
            $this->assertEquals($parent->id, $receivables[$i]->parent_id);
            $this->assertEquals($i + 1, $receivables[$i]->installment_number);
        }
    }

    public function test_store_receivable_weekly_installments(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => $customer->id,
            'amount' => 500.00,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
            'recurrence_type' => 'weekly',
            'installment_count' => 4,
        ]);

        $response->assertRedirect(route('dashboard'));
        $receivables = Receivable::where('customer_id', $customer->id)->orderBy('due_date')->get();
        $this->assertCount(4, $receivables);
        $this->assertEquals('2026-03-08', $receivables[1]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-03-15', $receivables[2]->due_date->format('Y-m-d'));
        $this->assertEquals('2026-03-22', $receivables[3]->due_date->format('Y-m-d'));
    }

    public function test_store_receivable_requires_installment_count_for_recurring(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
            'recurrence_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors(['installment_count']);
    }

    public function test_store_receivable_once_does_not_create_installments(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('command-center.store-receivable'), [
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
            'recurrence_type' => 'once',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertCount(1, Receivable::where('customer_id', $customer->id)->get());
    }
}
