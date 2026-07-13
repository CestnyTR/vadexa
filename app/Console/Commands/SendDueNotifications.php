<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendDueNotifications extends Command
{
    protected $signature = 'notifications:send-due {--respect-schedule : Only process tenants whose notification time matches the current hour}';

    protected $description = 'Dispatch due/overdue receivable SMS and mail notifications for all active tenants';

    public function handle(NotificationService $notificationService): int
    {
        $result = $notificationService->dispatchDueForAllTenants(
            respectSchedule: (bool) $this->option('respect-schedule'),
        );

        $this->info("Processed {$result['tenants']} tenant(s): dispatched {$result['sms']} SMS and {$result['mail']} mail job(s).");

        return CommandAlias::SUCCESS;
    }
}
