<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Receivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_dashboard_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHasAll(['dueToday', 'overdue', 'upcoming', 'smsStats', 'mailStats', 'receivables', 'tasks', 'customers']);
    }

    public function test_dashboard_shows_correct_due_today_stats(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today(),
            'amount' => 1000,
            'status' => 'due_today',
        ]);
        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today(),
            'amount' => 2000,
            'status' => 'due_today',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $dueToday = $response->viewData('dueToday');
        $this->assertEquals(2, $dueToday->count);
        $this->assertEquals(3000.00, $dueToday->total);
    }

    public function test_dashboard_excludes_paid_from_stats(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today(),
            'amount' => 1000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $dueToday = $response->viewData('dueToday');
        $this->assertEquals(0, $dueToday->count);
    }

    public function test_dashboard_shows_overdue_receivables(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory(3)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today()->subDays(5),
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['tab' => 'overdue']));

        $response->assertOk();
        $this->assertCount(3, $response->viewData('receivables'));
    }

    public function test_dashboard_filters_by_tab(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today(),
            'status' => 'due_today',
        ]);
        Receivable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'due_date' => today()->subDays(5),
            'status' => 'overdue',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['tab' => 'due_today']));
        $this->assertCount(1, $response->viewData('receivables'));

        $response = $this->actingAs($this->user)->get(route('dashboard', ['tab' => 'overdue']));
        $this->assertCount(1, $response->viewData('receivables'));
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
