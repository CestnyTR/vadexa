<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::query()->where('is_active', true)->value('id') ?? Tenant::query()->insertGetId([
                'name' => 'Default Tenant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'customer_id' => fake()->boolean(70) ? Customer::factory() : null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['todo', 'doing', 'done']),
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d') : null,
        ];
    }
}
