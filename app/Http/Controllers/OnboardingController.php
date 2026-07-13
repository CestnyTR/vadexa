<?php

namespace App\Http\Controllers;

use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        private TenantService $tenants,
    ) {}

    public function show(): View
    {
        return view('onboarding.show', [
            'tenant' => $this->tenants->current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_time' => ['required', 'date_format:H:i'],
            'sms_enabled' => ['boolean'],
            'mail_enabled' => ['boolean'],
        ]);

        $this->tenants->updateSettings([
            'notification_time' => $data['notification_time'],
            'sms_enabled' => $request->boolean('sms_enabled'),
            'mail_enabled' => $request->boolean('mail_enabled'),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Kurulum tamamlandı. Vadexa kullanıma hazır!');
    }
}
