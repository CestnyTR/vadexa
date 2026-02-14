<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $tenantId = Tenant::query()->where('is_active', true)->value('id');
        TenantContext::setTenantId($tenantId);

        // 20 customers
        $customers = Customer::factory(20)->create(['tenant_id' => $tenantId]);

        // 60 receivables with mixed statuses and date distribution
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

        // 20 tasks
        Task::factory(20)->create([
            'tenant_id' => $tenantId,
            'customer_id' => fn () => $customers->random()->id,
        ]);

        // Demo SMS logs (last 3 days)
        foreach ($customers->take(10) as $customer) {
            SmsLog::factory(rand(1, 3))->create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'phone' => $customer->phone,
                'created_at' => fake()->dateTimeBetween('-3 days', 'now'),
            ]);
        }

        // Demo Mail logs (last 3 days)
        foreach ($customers->take(8) as $customer) {
            MailLog::factory(rand(1, 2))->create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'email' => $customer->email ?? fake()->safeEmail(),
                'created_at' => fake()->dateTimeBetween('-3 days', 'now'),
            ]);
        }
    }
}
