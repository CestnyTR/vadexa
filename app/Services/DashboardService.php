<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\MailLog;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardService
{
    public function __construct(
        private ReceivableRepositoryInterface $receivables,
        private TaskRepositoryInterface $tasks,
        private CustomerRepositoryInterface $customers,
    ) {}

    public function getDashboardData(Request $request): array
    {
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        $dueToday = $this->receivables->aggregateByDateCriteria('equals', $today);
        $overdue = $this->receivables->aggregateByDateCriteria('before', $today);
        $upcoming = $this->receivables->aggregateByDateCriteria('between', $today, $nextWeek);

        $last24h = now()->subDay();

        $smsStats = SmsLog::query()
            ->where('created_at', '>=', $last24h)
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as success_count")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count")
            ->first();

        $mailStats = MailLog::query()
            ->where('created_at', '>=', $last24h)
            ->selectRaw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count")
            ->first();

        $tab = $request->query('tab', 'overdue');
        $receivables = $this->receivables->paginateForDashboard($tab, [
            'q' => $request->query('q'),
            'customer_id' => $request->query('customer_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        $taskTab = $request->query('task_tab', 'todo');
        $tasks = $this->tasks->getForDashboard($taskTab);

        $recentSms = SmsLog::query()->with('customer')->latest()->limit(5)->get();
        $recentMails = MailLog::query()->with('customer')->latest()->limit(5)->get();

        $customers = $this->customers->allForDropdown();

        $editReceivable = null;
        if ($request->query('modal') === 'receivable-edit' && $request->query('id')) {
            $editReceivable = $this->receivables->find((int) $request->query('id'));
        }

        $tenant = Tenant::find(TenantContext::tenantId());

        return compact(
            'dueToday', 'overdue', 'upcoming', 'tenant',
            'smsStats', 'mailStats', 'receivables', 'tasks',
            'recentSms', 'recentMails', 'customers', 'tab', 'taskTab', 'editReceivable',
        );
    }
}
