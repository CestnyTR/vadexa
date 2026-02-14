<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MailLog>
 */
class MailLogFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['success', 'failed']);

        return [
            'tenant_id' => Tenant::query()->where('is_active', true)->value('id') ?? Tenant::query()->insertGetId([
                'name' => 'Default Tenant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'customer_id' => fake()->boolean(80) ? Customer::factory() : null,
            'email' => fake()->safeEmail(),
            'type' => fake()->randomElement(['upcoming', 'due_today', 'overdue']),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => $status,
            'provider_message_id' => $status === 'success' ? fake()->uuid() : null,
            'error' => $status === 'failed' ? 'Mail delivery failed' : null,
            'sent_at' => $status === 'success' ? fake()->dateTimeBetween('-3 days', 'now') : null,
        ];
    }
}
