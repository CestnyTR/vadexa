<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
