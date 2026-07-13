<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tenantWithOwner(): array
    {
        $tenant = Tenant::create(['name' => 'Acme', 'is_active' => true]);
        $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        return [$tenant, $owner];
    }

    public function test_owner_can_promote_a_member(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();
        $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($owner)
            ->patch(route('team.members.update', $member), ['role' => 'owner'])
            ->assertRedirect(route('team.index'));

        $this->assertTrue($member->fresh()->isOwner());
    }

    public function test_owner_can_remove_a_member(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();
        $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($owner)
            ->delete(route('team.members.destroy', $member))
            ->assertRedirect(route('team.index'));

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_last_owner_cannot_be_removed(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();

        // The sole owner is protected even against the member-management route.
        $this->actingAs($owner)
            ->delete(route('team.members.destroy', $owner))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_last_owner_cannot_be_demoted(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();

        $this->actingAs($owner)
            ->patch(route('team.members.update', $owner), ['role' => 'member'])
            ->assertSessionHas('error');

        $this->assertTrue($owner->fresh()->isOwner());
    }

    public function test_cannot_manage_member_from_another_tenant(): void
    {
        [$tenantA, $ownerA] = $this->tenantWithOwner();
        $tenantB = Tenant::create(['name' => 'Beta', 'is_active' => true]);
        $memberB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($ownerA)
            ->delete(route('team.members.destroy', $memberB))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $memberB->id]);
    }
}
