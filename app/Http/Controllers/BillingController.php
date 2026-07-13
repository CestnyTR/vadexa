<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function __construct(
        private BillingService $billing,
    ) {}

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_keys(config('plans.plans')))],
        ]);

        try {
            $this->billing->subscribe($data['plan']);
        } catch (PaymentException $e) {
            return redirect()->route('plan.index')->with('error', $e->getMessage());
        }

        return redirect()->route('plan.index')
            ->with('success', 'Ödemeniz alındı, planınız güncellendi.');
    }

    public function cancel(): RedirectResponse
    {
        $this->billing->cancel();

        return redirect()->route('plan.index')
            ->with('success', 'Aboneliğiniz iptal edildi, Ücretsiz plana geçildi.');
    }
}
