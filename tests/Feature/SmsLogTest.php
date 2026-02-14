<?php

namespace Tests\Feature;

use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

class SmsLogTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('sms-logs.index'));

        $response->assertOk();
        $response->assertViewHas('logs');
    }

    public function test_index_filters_by_status(): void
    {
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'sent']);
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'failed']);

        $response = $this->actingAs($this->user)->get(route('sms-logs.index', ['status' => 'sent']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_index_filters_by_type(): void
    {
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'type' => 'due_today']);
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'type' => 'overdue']);

        $response = $this->actingAs($this->user)->get(route('sms-logs.index', ['type' => 'overdue']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_index_filters_by_date_range(): void
    {
        SmsLog::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_at' => '2026-02-10 10:00:00',
        ]);
        SmsLog::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_at' => '2026-02-20 10:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('sms-logs.index', [
            'date_from' => '2026-02-15',
            'date_to' => '2026-02-25',
        ]));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_index_searches_by_phone(): void
    {
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'phone' => '5551112233']);
        SmsLog::factory()->create(['tenant_id' => $this->tenant->id, 'phone' => '5554445566']);

        $response = $this->actingAs($this->user)->get(route('sms-logs.index', ['q' => '555111']));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('logs')->total());
    }

    public function test_sms_logs_are_read_only(): void
    {
        $response = $this->actingAs($this->user)->post(route('sms-logs.index'));

        $response->assertStatus(405);
    }
}
