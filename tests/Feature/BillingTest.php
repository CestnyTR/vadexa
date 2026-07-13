<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function ownerOnFree(): array
    {
        $tenant = Tenant::create(['name' => 'Acme', 'plan' => 'free', 'is_active' => true]);
        $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        return [$tenant, $owner];
    }

    public function test_owner_can_subscribe_to_a_paid_plan_in_mock_mode(): void
    {
        config()->set('services.iyzico.mock', true);
        [$tenant, $owner] = $this->ownerOnFree();

        $response = $this->actingAs($owner)->post(route('billing.subscribe'), ['plan' => 'pro']);

        $response->assertRedirect(route('plan.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('pro', $tenant->fresh()->plan);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id, 'plan' => 'pro', 'status' => 'active',
        ]);
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $tenant->id, 'plan' => 'pro', 'status' => 'paid',
        ]);
    }

    public function test_member_cannot_subscribe(): void
    {
        [$tenant] = $this->ownerOnFree();
        $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($member)->post(route('billing.subscribe'), ['plan' => 'pro'])
            ->assertForbidden();

        $this->assertEquals('free', $tenant->fresh()->plan);
    }

    public function test_cancel_reverts_to_free_plan(): void
    {
        config()->set('services.iyzico.mock', true);
        [$tenant, $owner] = $this->ownerOnFree();

        $this->actingAs($owner)->post(route('billing.subscribe'), ['plan' => 'business']);
        $this->assertEquals('business', $tenant->fresh()->plan);

        $this->actingAs($owner)->post(route('billing.cancel'))->assertRedirect(route('plan.index'));

        $this->assertEquals('free', $tenant->fresh()->plan);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id, 'status' => 'canceled',
        ]);
    }

    public function test_failed_payment_does_not_change_plan(): void
    {
        [$tenant, $owner] = $this->ownerOnFree();

        // Swap the gateway for one that always declines.
        $this->app->bind(PaymentGateway::class, fn () => new class implements PaymentGateway
        {
            public function charge(Payment $payment, Tenant $tenant): PaymentResult
            {
                return PaymentResult::failure('Kart reddedildi.');
            }
        });

        $response = $this->actingAs($owner)->post(route('billing.subscribe'), ['plan' => 'pro']);

        $response->assertRedirect(route('plan.index'));
        $response->assertSessionHas('error');

        $this->assertEquals('free', $tenant->fresh()->plan);
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $tenant->id, 'plan' => 'pro', 'status' => 'failed',
        ]);
        $this->assertDatabaseMissing('subscriptions', ['tenant_id' => $tenant->id]);
    }
}
