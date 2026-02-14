<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_index_page_is_displayed(): void
    {
        Customer::factory(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertOk();
        $response->assertViewHas('customers');
    }

    public function test_index_search_filters_by_name(): void
    {
        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Ahmet Yilmaz']);
        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Mehmet Demir']);

        $response = $this->actingAs($this->user)->get(route('customers.index', ['q' => 'Ahmet']));

        $response->assertOk();
        $response->assertSee('Ahmet Yilmaz');
        $response->assertDontSee('Mehmet Demir');
    }

    public function test_index_search_filters_by_phone(): void
    {
        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Test A', 'phone' => '5551234567']);
        Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Test B', 'phone' => '5559876543']);

        $response = $this->actingAs($this->user)->get(route('customers.index', ['q' => '555123']));

        $response->assertOk();
        $response->assertSee('Test A');
        $response->assertDontSee('Test B');
    }

    public function test_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('customers.create'));

        $response->assertOk();
    }

    public function test_customer_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'name' => 'Yeni Musteri',
            'phone' => '5551112233',
            'email' => 'yeni@example.com',
            'notes' => 'Test notu',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Yeni Musteri',
            'phone' => '5551112233',
            'email' => 'yeni@example.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_customer_creation_requires_name_and_phone(): void
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'name' => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_customer_creation_validates_email(): void
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'name' => 'Test',
            'phone' => '555',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_edit_page_is_displayed(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->get(route('customers.edit', $customer));

        $response->assertOk();
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->put(route('customers.update', $customer), [
            'name' => 'Guncellenmis Ad',
            'phone' => '5559998877',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Guncellenmis Ad',
            'phone' => '5559998877',
        ]);
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_unauthenticated_user_cannot_access_customers(): void
    {
        $response = $this->get(route('customers.index'));

        $response->assertRedirect(route('login'));
    }
}
