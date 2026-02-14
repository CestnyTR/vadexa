<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertViewHas('tasks');
    }

    public function test_index_filters_by_status(): void
    {
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'todo']);
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'done']);

        $response = $this->actingAs($this->user)->get(route('tasks.index', ['status' => 'todo']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('tasks')->total());
    }

    public function test_index_searches_by_title(): void
    {
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'title' => 'Fatura takibi']);
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'title' => 'Musteri arama']);

        $response = $this->actingAs($this->user)->get(route('tasks.index', ['q' => 'Fatura']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('tasks')->total());
    }

    public function test_index_filters_by_due_date(): void
    {
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'due_date' => '2026-02-15']);
        Task::factory()->create(['tenant_id' => $this->tenant->id, 'due_date' => '2026-03-15']);

        $response = $this->actingAs($this->user)->get(route('tasks.index', [
            'due_date_from' => '2026-02-01',
            'due_date_to' => '2026-02-28',
        ]));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('tasks')->total());
    }

    public function test_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('tasks.create'));

        $response->assertOk();
        $response->assertViewHas('customers');
    }

    public function test_task_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('tasks.store'), [
            'title' => 'Yeni Gorev',
            'status' => 'todo',
            'due_date' => '2026-03-01',
            'description' => 'Aciklama',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Yeni Gorev',
            'status' => 'todo',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_task_can_be_created_with_customer(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user)->post(route('tasks.store'), [
            'title' => 'Musteri gorevi',
            'status' => 'doing',
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Musteri gorevi',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_task_creation_requires_title_and_status(): void
    {
        $response = $this->actingAs($this->user)->post(route('tasks.store'), []);

        $response->assertSessionHasErrors(['title', 'status']);
    }

    public function test_task_creation_validates_status_enum(): void
    {
        $response = $this->actingAs($this->user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'todo']);

        $response = $this->actingAs($this->user)->put(route('tasks.update', $task), [
            'title' => 'Guncellenmis',
            'status' => 'done',
        ]);

        $response->assertRedirect(route('tasks.index'));

        $task->refresh();
        $this->assertEquals('Guncellenmis', $task->title);
        $this->assertEquals('done', $task->status);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
