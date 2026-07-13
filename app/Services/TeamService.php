<?php

namespace App\Services;

use App\Exceptions\TeamRuleException;
use App\Models\User;
use App\Support\TenantContext;

class TeamService
{
    /**
     * Ensure the user belongs to the active tenant.
     */
    public function assertInTenant(User $user): void
    {
        if ($user->tenant_id !== TenantContext::tenantId()) {
            abort(404);
        }
    }

    public function ownerCount(): int
    {
        return User::query()
            ->where('tenant_id', TenantContext::tenantId())
            ->where('role', User::ROLE_OWNER)
            ->count();
    }

    /**
     * Change a member's role, protecting the last remaining owner.
     *
     * @throws TeamRuleException
     */
    public function changeRole(User $user, string $role): void
    {
        $this->assertInTenant($user);

        if ($user->isOwner() && $role !== User::ROLE_OWNER && $this->ownerCount() <= 1) {
            throw new TeamRuleException('Son yöneticinin rolü değiştirilemez.');
        }

        $user->update(['role' => $role]);
    }

    /**
     * Remove a member, protecting the last remaining owner.
     *
     * @throws TeamRuleException
     */
    public function remove(User $user): void
    {
        $this->assertInTenant($user);

        if ($user->isOwner() && $this->ownerCount() <= 1) {
            throw new TeamRuleException('Son yönetici ekipten çıkarılamaz.');
        }

        $user->delete();
    }
}
