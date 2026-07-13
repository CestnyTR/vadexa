<?php

namespace Tests\Feature;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): array
    {
        $tenant = Tenant::create(['name' => 'Acme', 'plan' => 'pro', 'is_active' => true]);
        $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        return [$tenant, $owner];
    }

    public function test_owner_can_invite_a_member(): void
    {
        Mail::fake();
        [$tenant, $owner] = $this->owner();

        $response = $this->actingAs($owner)->post(route('invitations.store'), [
            'email' => 'newbie@example.com',
            'role' => 'member',
        ]);

        $response->assertRedirect(route('team.index'));
        $this->assertDatabaseHas('invitations', [
            'tenant_id' => $tenant->id,
            'email' => 'newbie@example.com',
            'role' => 'member',
        ]);
        Mail::assertQueued(InvitationMail::class, fn ($mail) => $mail->hasTo('newbie@example.com'));
    }

    public function test_member_cannot_access_team_management(): void
    {
        $tenant = Tenant::create(['name' => 'Acme', 'is_active' => true]);
        $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($member)->get(route('team.index'))->assertForbidden();
        $this->actingAs($member)->post(route('invitations.store'), [
            'email' => 'x@example.com',
            'role' => 'member',
        ])->assertForbidden();
    }

    public function test_invitation_can_be_accepted_and_creates_user_in_tenant(): void
    {
        [$tenant, $owner] = $this->owner();

        $invitation = Invitation::create([
            'tenant_id' => $tenant->id,
            'email' => 'joiner@example.com',
            'role' => 'member',
            'token' => 'testtoken123',
            'expires_at' => now()->addDays(3),
        ]);

        $response = $this->post(route('invitations.accept.store', $invitation->token), [
            'name' => 'Joiner',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'joiner@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertEquals('member', $user->role);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_expired_invitation_returns_404(): void
    {
        [$tenant] = $this->owner();

        $invitation = Invitation::create([
            'tenant_id' => $tenant->id,
            'email' => 'late@example.com',
            'role' => 'member',
            'token' => 'expiredtoken',
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('invitations.accept', $invitation->token))->assertNotFound();
    }
}
