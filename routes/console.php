<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run hourly; each tenant is only processed when its own notification_time
// falls in the current hour (see NotificationService::isTenantDueTime).
Schedule::command('notifications:send-due --respect-schedule')->hourly();
