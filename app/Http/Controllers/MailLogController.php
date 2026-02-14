<?php

namespace App\Http\Controllers;

use App\Models\MailLog;

class MailLogController extends Controller
{
    public function index()
    {
        $logs = MailLog::query()
            ->with('customer')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when(request('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(request('q'), fn ($query, $q) => $query->where('email', 'like', "%{$q}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('mail_logs.index', compact('logs'));
    }
}
