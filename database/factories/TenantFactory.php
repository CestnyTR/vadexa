<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'plan' => 'free',
            'trial_ends_at' => null,
            'is_active' => true,
            'notification_time' => '10:00',
            'sms_enabled' => true,
            'mail_enabled' => true,
        ];
    }

    public function onTrial(): static
    {
        return $this->state(fn () => ['trial_ends_at' => now()->addDays(14)]);
    }
}
