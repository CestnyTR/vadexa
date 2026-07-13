<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        private PaymentGateway $gateway,
        private TenantService $tenants,
    ) {}

    /**
     * Subscribe the current tenant to a paid plan: create a payment, charge it
     * via the gateway, and on success activate the subscription and switch the
     * tenant's plan.
     *
     * @throws PaymentException
     */
    public function subscribe(string $planKey): Payment
    {
        $plans = config('plans.plans');

        if (! isset($plans[$planKey])) {
            throw new PaymentException('Geçersiz plan seçildi.');
        }

        $plan = $plans[$planKey];

        if (($plan['price'] ?? 0) <= 0) {
            throw new PaymentException('Bu plan için ödeme gerekmiyor.');
        }

        $tenant = $this->tenants->current();

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'plan' => $planKey,
            'amount' => $plan['price'],
            'currency' => (string) config('services.iyzico.currency', 'TRY'),
            'status' => Payment::STATUS_PENDING,
            'provider' => 'iyzico',
        ]);

        $result = $this->gateway->charge($payment, $tenant);

        if (! $result->success) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'error' => $result->error,
            ]);

            throw new PaymentException($result->error ?? 'Ödeme başarısız oldu.');
        }

        DB::transaction(function () use ($payment, $tenant, $planKey, $result): void {
            $payment->update([
                'status' => Payment::STATUS_PAID,
                'provider_ref' => $result->reference,
                'paid_at' => now(),
            ]);

            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan' => $planKey,
                    'status' => Subscription::STATUS_ACTIVE,
                    'provider' => 'iyzico',
                    'provider_ref' => $result->reference,
                    'current_period_ends_at' => now()->addMonth(),
                    'canceled_at' => null,
                ],
            );

            $tenant->update([
                'plan' => $planKey,
                'trial_ends_at' => null,
            ]);
        });

        return $payment;
    }

    /**
     * Cancel the current tenant's subscription and revert to the free plan.
     */
    public function cancel(): void
    {
        $tenant = $this->tenants->current();

        $subscription = Subscription::where('tenant_id', $tenant->id)->first();

        if ($subscription && $subscription->isActive()) {
            $subscription->update([
                'status' => Subscription::STATUS_CANCELED,
                'canceled_at' => now(),
            ]);
        }

        $tenant->update(['plan' => config('plans.default')]);
    }
}
