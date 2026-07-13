<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\QuotaService;
use App\Services\TenantService;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(
        private TenantService $tenants,
        private QuotaService $quota,
    ) {}

    public function index(): View
    {
        $tenant = $this->tenants->current();

        $usage = [];
        foreach (['customers', 'users'] as $key) {
            $usage[$key] = [
                'used' => $this->quota->usage($key),
                'limit' => $this->quota->limit($key),
            ];
        }

        return view('plan.index', [
            'tenant' => $tenant,
            'currentPlan' => $this->tenants->effectivePlan($tenant),
            'plans' => config('plans.plans'),
            'usage' => $usage,
            'onTrial' => $tenant->onTrial(),
            'subscription' => Subscription::where('tenant_id', $tenant->id)->first(),
            'payments' => Payment::where('tenant_id', $tenant->id)->latest()->take(10)->get(),
        ]);
    }
}
