<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Services\QuotaService;
use App\Services\TeamService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        private QuotaService $quota,
        private TeamService $team,
    ) {}

    public function index(): View
    {
        $tenantId = TenantContext::tenantId();

        $members = User::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('role')
            ->orderBy('name')
            ->get();

        $invitations = Invitation::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('accepted_at')
            ->latest()
            ->get();

        return view('team.index', [
            'members' => $members,
            'invitations' => $invitations,
            'userLimit' => $this->quota->limit('users'),
            'userUsage' => $this->quota->usage('users'),
        ]);
    }

    public function updateMember(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_OWNER, User::ROLE_MEMBER])],
        ]);

        try {
            $this->team->changeRole($user, $data['role']);
        } catch (\App\Exceptions\TeamRuleException $e) {
            return redirect()->route('team.index')->with('error', $e->getMessage());
        }

        return redirect()->route('team.index')->with('success', 'Üye rolü güncellendi.');
    }

    public function destroyMember(User $user): RedirectResponse
    {
        try {
            $this->team->remove($user);
        } catch (\App\Exceptions\TeamRuleException $e) {
            return redirect()->route('team.index')->with('error', $e->getMessage());
        }

        return redirect()->route('team.index')->with('success', 'Üye ekipten çıkarıldı.');
    }
}
