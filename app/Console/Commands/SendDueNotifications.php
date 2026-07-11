<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendDueNotifications extends Command
{
    protected $signature = 'notifications:send-due';

    protected $description = 'Dispatch due/overdue receivable SMS and mail notifications';

    public function handle(NotificationService $notificationService): int
    {
        $result = $notificationService->dispatchDueNotifications();

        $this->info("Dispatched {$result['sms']} SMS and {$result['mail']} mail job(s).");

        return CommandAlias::SUCCESS;
    }
}
