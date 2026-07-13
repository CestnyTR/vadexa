<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenantWithCustomer(string $name): array
    {
        $tenant = Tenant::create(['name' => $name, 'is_active' => true]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $customer = TenantContext::run($tenant->id, fn () => Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => $name.' Customer',
        ]));

        return [$tenant, $user, $customer];
    }

    public function test_users_only_see_their_own_tenant_customers(): void
    {
        [$tenantA, $userA, $customerA] = $this->makeTenantWithCustomer('Alpha');
        [$tenantB, $userB, $customerB] = $this->makeTenantWithCustomer('Beta');

        $response = $this->actingAs($userA)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Alpha Customer');
        $response->assertDontSee('Beta Customer');
    }

    public function test_user_cannot_edit_another_tenants_customer(): void
    {
        [$tenantA, $userA] = $this->makeTenantWithCustomer('Alpha');
        [$tenantB, $userB, $customerB] = $this->makeTenantWithCustomer('Beta');

        // userA tries to access Beta's customer edit page -> scoped out -> 404
        $response = $this->actingAs($userA)->get(route('customers.edit', $customerB));

        $response->assertNotFound();
    }

    public function test_user_cannot_delete_another_tenants_customer(): void
    {
        [$tenantA, $userA] = $this->makeTenantWithCustomer('Alpha');
        [$tenantB, $userB, $customerB] = $this->makeTenantWithCustomer('Beta');

        $response = $this->actingAs($userA)->delete(route('customers.destroy', $customerB));

        $response->assertNotFound();
        $this->assertDatabaseHas('customers', ['id' => $customerB->id]);
    }
}
