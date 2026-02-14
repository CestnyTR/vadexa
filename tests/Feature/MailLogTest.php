<?php

namespace Tests\Feature;

use App\Models\MailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class MailLogTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('mail-logs.index'));

        $response->assertOk();
        $response->assertViewHas('logs');
    }

    public function test_index_filters_by_status(): void
    {
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'success']);
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'failed']);

        $response = $this->actingAs($this->user)->get(route('mail-logs.index', ['status' => 'failed']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_index_filters_by_type(): void
    {
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'type' => 'upcoming']);
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'type' => 'overdue']);

        $response = $this->actingAs($this->user)->get(route('mail-logs.index', ['type' => 'upcoming']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_index_searches_by_email(): void
    {
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'ahmet@test.com']);
        MailLog::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'mehmet@test.com']);

        $response = $this->actingAs($this->user)->get(route('mail-logs.index', ['q' => 'ahmet']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }
}
