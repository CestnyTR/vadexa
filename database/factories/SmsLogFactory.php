<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsLog>
 */
class SmsLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['queued', 'sent', 'failed']);

        return [
            'tenant_id' => Tenant::query()->where('is_active', true)->value('id') ?? Tenant::query()->insertGetId([
                'name' => 'Default Tenant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'customer_id' => fake()->boolean(80) ? Customer::factory() : null,
            'phone' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['upcoming', 'due_today', 'overdue']),
            'message' => fake()->sentence(),
            'status' => $status,
            'provider_message_id' => $status === 'sent' ? fake()->uuid() : null,
            'error' => $status === 'failed' ? 'Mock provider error' : null,
            'sent_at' => $status === 'sent' ? now() : null,
        ];
    }
}
