<?php

use App\Models\Tenant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule based on tenant's notification_time (falls back to config)
$time = '10:00';
try {
    $tenant = Tenant::query()->where('is_active', true)->first();
    if ($tenant && $tenant->notification_time) {
        $time = $tenant->notification_time;
    }
} catch (\Throwable) {
    // DB may not be available during artisan commands like migrate
}

Schedule::command('notifications:send-due')->dailyAt($time);
