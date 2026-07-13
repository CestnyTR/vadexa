<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function __construct(
        private QuotaService $quota,
    ) {}

    /**
     * Create (or refresh) an invitation for an e-mail within the current tenant.
     *
     * @throws \App\Exceptions\PlanLimitException
     */
    public function invite(string $email, string $role = User::ROLE_MEMBER): Invitation
    {
        $this->quota->assertCanCreate('users');

        $tenantId = TenantContext::tenantId();

        $invitation = Invitation::updateOrCreate(
            ['tenant_id' => $tenantId, 'email' => $email],
            [
                'role' => $role,
                'token' => Str::random(48),
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ],
        );

        Mail::to($email)->send(new InvitationMail($invitation, Tenant::find($tenantId)->name));

        return $invitation;
    }

    public function findPendingByToken(string $token): ?Invitation
    {
        $invitation = Invitation::query()->where('token', $token)->first();

        return $invitation && $invitation->isPending() ? $invitation : null;
    }

    /**
     * Accept an invitation, creating the user in the invitation's tenant.
     */
    public function accept(Invitation $invitation, string $name, string $password): User
    {
        return TenantContext::run($invitation->tenant_id, function () use ($invitation, $name, $password): User {
            $user = User::create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $name,
                'email' => $invitation->email,
                'password' => Hash::make($password),
                'role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });
    }
}
