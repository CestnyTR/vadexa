<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Ücretsiz Başla');
    }

    public function test_authenticated_users_are_redirected_to_dashboard(): void
    {
        $tenant = Tenant::create(['name' => 'Acme', 'is_active' => true]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }
}
