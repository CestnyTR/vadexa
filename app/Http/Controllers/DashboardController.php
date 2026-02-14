<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MailLog;
use App\Models\Receivable;
use App\Models\SmsLog;
use App\Models\Task;
use App\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        // KPI stats
        $dueToday = Receivable::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        $overdue = Receivable::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        $upcoming = Receivable::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>', $today)
            ->whereDate('due_date', '<=', $nextWeek)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

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

        // Receivables tab (filtered)
        $tab = $request->query('tab', 'overdue');
        $receivablesQuery = Receivable::query()->with('customer');

        // Apply tab filter
        match ($tab) {
            'overdue' => $receivablesQuery->where('status', '!=', 'paid')->whereDate('due_date', '<', $today),
            'due_today' => $receivablesQuery->where('status', '!=', 'paid')->whereDate('due_date', $today),
            'upcoming' => $receivablesQuery->where('status', '!=', 'paid')->whereDate('due_date', '>', $today),
            'paid' => $receivablesQuery->where('status', 'paid'),
            default => $receivablesQuery->where('status', '!=', 'paid')->whereDate('due_date', '<', $today),
        };

        // Search
        if ($q = $request->query('q')) {
            $receivablesQuery->where(function ($query) use ($q) {
                $query->whereHas('customer', function ($cq) use ($q) {
                    $cq->where('name', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            });
        }

        // Customer filter
        if ($customerId = $request->query('customer_id')) {
            $receivablesQuery->where('customer_id', $customerId);
        }

        // Date range filter
        if ($dateFrom = $request->query('date_from')) {
            $receivablesQuery->whereDate('due_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $receivablesQuery->whereDate('due_date', '<=', $dateTo);
        }

        $receivables = $receivablesQuery->orderBy('due_date')->paginate(15)->withQueryString();

        // Tasks (filtered)
        $taskTab = $request->query('task_tab', 'todo');
        $tasksQuery = Task::query()->with('customer');

        if ($taskTab !== 'all') {
            $tasksQuery->where('status', $taskTab);
        }

        $tasks = $tasksQuery->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")->limit(20)->get();

        // Recent notifications (last 10)
        $recentSms = SmsLog::query()->with('customer')->latest()->limit(5)->get();
        $recentMails = MailLog::query()->with('customer')->latest()->limit(5)->get();

        // Customers list (for forms)
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        // Receivable for editing (if modal=receivable-edit&id=X)
        $editReceivable = null;
        if ($request->query('modal') === 'receivable-edit' && $request->query('id')) {
            $editReceivable = Receivable::find($request->query('id'));
        }

        // Tenant settings
        $tenant = Tenant::find(TenantContext::tenantId());

        return view('dashboard', compact(
            'dueToday',
            'overdue',
            'upcoming',
            'tenant',
            'smsStats',
            'mailStats',
            'receivables',
            'tasks',
            'recentSms',
            'recentMails',
            'customers',
            'tab',
            'taskTab',
            'editReceivable',
        ));
    }
}
