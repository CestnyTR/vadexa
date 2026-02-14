<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Receivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class ReceivableTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivables.index'));

        $response->assertOk();
        $response->assertViewHas('receivables');
        $response->assertViewHas('customers');
    }

    public function test_index_filters_by_status(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($this->user)->get(route('receivables.index', ['status' => 'paid']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('receivables')->total());
    }

    public function test_index_filters_by_customer(): void
    {
        $customerA = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $customerB = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory(2)->create(['tenant_id' => $this->tenant->id, 'customer_id' => $customerA->id]);
        Receivable::factory(3)->create(['tenant_id' => $this->tenant->id, 'customer_id' => $customerB->id]);

        $response = $this->actingAs($this->user)->get(route('receivables.index', ['customer_id' => $customerA->id]));

        $response->assertOk();
        $this->assertEquals(2, $response->viewData('receivables')->total());
    }

    public function test_index_filters_by_due_date_range(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => '2026-02-10',
        ]);
        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => '2026-03-01',
        ]);

        $response = $this->actingAs($this->user)->get(route('receivables.index', [
            'due_date_from' => '2026-02-01',
            'due_date_to' => '2026-02-28',
        ]));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('receivables')->total());
    }

    public function test_index_searches_by_customer_name(): void
    {
        $customerA = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Ahmet']);
        $customerB = Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Mehmet']);

        Receivable::factory()->create(['tenant_id' => $this->tenant->id, 'customer_id' => $customerA->id]);
        Receivable::factory()->create(['tenant_id' => $this->tenant->id, 'customer_id' => $customerB->id]);

        $response = $this->actingAs($this->user)->get(route('receivables.index', ['q' => 'Ahmet']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('receivables')->total());
    }

    public function test_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivables.create'));

        $response->assertOk();
        $response->assertViewHas('customers');
    }

    public function test_receivable_can_be_created(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('receivables.store'), [
            'customer_id' => $customer->id,
            'amount' => 1500.50,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
            'description' => 'Test alacak',
        ]);

        $response->assertRedirect(route('receivables.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('receivables', [
            'customer_id' => $customer->id,
            'amount' => 1500.50,
            'status' => 'upcoming',
            'paid_at' => null,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_paid_status_sets_paid_at_automatically(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user)->post(route('receivables.store'), [
            'customer_id' => $customer->id,
            'amount' => 1000,
            'due_date' => '2026-02-14',
            'status' => 'paid',
        ]);

        $receivable = Receivable::first();
        $this->assertNotNull($receivable->paid_at);
    }

    public function test_changing_status_from_paid_clears_paid_at(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->user)->put(route('receivables.update', $receivable), [
            'customer_id' => $customer->id,
            'amount' => $receivable->amount,
            'due_date' => $receivable->due_date->format('Y-m-d'),
            'status' => 'overdue',
        ]);

        $receivable->refresh();
        $this->assertNull($receivable->paid_at);
        $this->assertEquals('overdue', $receivable->status);
    }

    public function test_receivable_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('receivables.store'), []);

        $response->assertSessionHasErrors(['customer_id', 'amount', 'due_date', 'status']);
    }

    public function test_receivable_creation_validates_status_enum(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('receivables.store'), [
            'customer_id' => $customer->id,
            'amount' => 100,
            'due_date' => '2026-03-01',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_receivable_creation_validates_amount_positive(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->post(route('receivables.store'), [
            'customer_id' => $customer->id,
            'amount' => -100,
            'due_date' => '2026-03-01',
            'status' => 'upcoming',
        ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_receivable_can_be_deleted(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $receivable = Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('receivables.destroy', $receivable));

        $response->assertRedirect(route('receivables.index'));
        $this->assertDatabaseMissing('receivables', ['id' => $receivable->id]);
    }
}
