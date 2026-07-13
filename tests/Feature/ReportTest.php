<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ReportService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_and_aging_reflect_tenant_data(): void
    {
        $tenant = Tenant::create(['name' => 'Acme', 'is_active' => true]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        TenantContext::run($tenant->id, function () use ($tenant) {
            $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

            // paid 1000
            Receivable::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'amount' => 1000, 'status' => 'paid', 'paid_at' => now(),
                'due_date' => now()->subDays(10),
            ]);
            // unpaid, not due (amount 500)
            Receivable::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'amount' => 500, 'status' => 'upcoming', 'paid_at' => null,
                'due_date' => now()->addDays(5),
            ]);
            // unpaid, overdue 40 days (amount 300) -> 31_60 bucket
            Receivable::factory()->create([
                'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
                'amount' => 300, 'status' => 'overdue', 'paid_at' => null,
                'due_date' => now()->subDays(40),
            ]);
        });

        $reports = app(ReportService::class);

        [$collection, $aging] = TenantContext::run($tenant->id, fn () => [
            $reports->collection(),
            $reports->aging(),
        ]);

        $this->assertEquals(1800.0, $collection['total_amount']);
        $this->assertEquals(1000.0, $collection['paid_amount']);
        $this->assertEquals(800.0, $collection['outstanding_amount']);
        $this->assertEqualsWithDelta(55.6, $collection['rate'], 0.1);

        $this->assertEquals(1, $aging['not_due']['count']);
        $this->assertEquals(500.0, $aging['not_due']['amount']);
        $this->assertEquals(1, $aging['31_60']['count']);
        $this->assertEquals(300.0, $aging['31_60']['amount']);
    }

    public function test_reports_page_renders(): void
    {
        $tenant = Tenant::create(['name' => 'Acme', 'is_active' => true]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Tahsilat Oranı');
    }
}
