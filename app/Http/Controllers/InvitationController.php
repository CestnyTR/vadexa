<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use App\Support\TenantContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitations,
    ) {}

    /**
     * Owner creates an invitation (Faz 3 — team management).
     */
    public function store(Request $request): RedirectResponse
    {
        $tenantId = TenantContext::tenantId();

        $data = $request->validate([
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('invitations', 'email')->where('tenant_id', $tenantId)->whereNull('accepted_at'),
            ],
            'role' => ['required', Rule::in([User::ROLE_OWNER, User::ROLE_MEMBER])],
        ]);

        $invitation = $this->invitations->invite($data['email'], $data['role']);

        return redirect()->route('team.index')
            ->with('success', 'Davet oluşturuldu. Bağlantı: '.route('invitations.accept', $invitation->token));
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        abort_unless($invitation->tenant_id === TenantContext::tenantId(), 403);

        $invitation->delete();

        return redirect()->route('team.index')->with('success', 'Davet iptal edildi.');
    }

    /**
     * Public — show the accept/registration form for a token.
     */
    public function accept(string $token): View
    {
        $invitation = $this->invitations->findPendingByToken($token);

        abort_if($invitation === null, 404, 'Davet bulunamadı veya süresi doldu.');

        return view('invitations.accept', compact('invitation'));
    }

    /**
     * Public — accept the invitation, create the user and log in.
     */
    public function storeAccepted(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->invitations->findPendingByToken($token);

        abort_if($invitation === null, 404, 'Davet bulunamadı veya süresi doldu.');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $this->invitations->accept($invitation, $request->name, $request->password);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
