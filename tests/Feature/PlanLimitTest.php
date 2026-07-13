<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_blocks_creating_customers_over_limit(): void
    {
        config()->set('plans.plans.free.limits.customers', 2);

        $tenant = Tenant::create(['name' => 'Limited', 'plan' => 'free', 'is_active' => true]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        TenantContext::run($tenant->id, fn () => Customer::factory(2)->create(['tenant_id' => $tenant->id]));

        $response = $this->actingAs($user)->post(route('customers.store'), [
            'name' => 'Over Limit',
            'phone' => '5551234567',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(2, TenantContext::run($tenant->id, fn () => Customer::count()));
    }

    public function test_trial_tenant_gets_higher_limits(): void
    {
        config()->set('plans.trial_plan', 'pro');
        config()->set('plans.plans.free.limits.customers', 1);
        config()->set('plans.plans.pro.limits.customers', 100);

        $tenant = Tenant::create([
            'name' => 'Trial Co',
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(5),
            'is_active' => true,
        ]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        TenantContext::run($tenant->id, fn () => Customer::factory(1)->create(['tenant_id' => $tenant->id]));

        $response = $this->actingAs($user)->post(route('customers.store'), [
            'name' => 'Second Customer',
            'phone' => '5551234567',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(2, TenantContext::run($tenant->id, fn () => Customer::count()));
    }
}
