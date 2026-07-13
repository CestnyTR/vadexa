<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use App\Support\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
        ]);

        $templates = app(TenantService::class);

        // Primary demo tenant — full data set + login account.
        $primary = Tenant::query()->where('is_active', true)->orderBy('id')->first();
        $templates->seedDefaultTemplates($primary);

        User::factory()->create([
            'tenant_id' => $primary->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => User::ROLE_OWNER,
        ]);

        $this->seedTenantData($primary->id);

        // Second tenant with its own owner + isolated data, to prove tenant scoping.
        $second = Tenant::query()->where('is_active', true)->where('id', '!=', $primary->id)->orderBy('id')->first();

        if ($second) {
            $templates->seedDefaultTemplates($second);

            User::factory()->create([
                'tenant_id' => $second->id,
                'name' => 'Beta Owner',
                'email' => 'beta@example.com',
                'role' => User::ROLE_OWNER,
            ]);

            TenantContext::run($second->id, function () use ($second): void {
                $customers = Customer::factory(5)->create(['tenant_id' => $second->id]);
                $customers->each(fn ($c) => Receivable::factory(2)->create([
                    'tenant_id' => $second->id,
                    'customer_id' => $c->id,
                ]));
            });
        }
    }

    protected function seedTenantData(int $tenantId): void
    {
        TenantContext::run($tenantId, function () use ($tenantId): void {
            $customers = Customer::factory(20)->create(['tenant_id' => $tenantId]);

            $customers->each(function ($customer) use ($tenantId) {
                Receivable::factory(3)->create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'due_date' => fake()->randomElement([
                        now()->subDays(rand(1, 30))->format('Y-m-d'),
                        now()->format('Y-m-d'),
                        now()->addDays(rand(1, 30))->format('Y-m-d'),
                    ]),
                ]);
            });

            Task::factory(20)->create([
                'tenant_id' => $tenantId,
                'customer_id' => fn () => $customers->random()->id,
            ]);

            foreach ($customers->take(10) as $customer) {
                SmsLog::factory(rand(1, 3))->create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'phone' => $customer->phone,
                    'created_at' => fake()->dateTimeBetween('-3 days', 'now'),
                ]);
            }

            foreach ($customers->take(8) as $customer) {
                MailLog::factory(rand(1, 2))->create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'email' => $customer->email ?? fake()->safeEmail(),
                    'created_at' => fake()->dateTimeBetween('-3 days', 'now'),
                ]);
            }
        });
    }
}
