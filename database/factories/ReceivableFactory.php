<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receivable>
 */
class ReceivableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['upcoming', 'due_today', 'overdue', 'paid']);

        return [
            'tenant_id' => Tenant::query()->where('is_active', true)->value('id') ?? Tenant::query()->insertGetId([
                'name' => 'Default Tenant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'customer_id' => Customer::factory(),
            'amount' => fake()->randomFloat(2, 100, 25000),
            'due_date' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
