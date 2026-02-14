<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;

class SmsLogController extends Controller
{
    public function index()
    {
        $logs = SmsLog::query()
            ->with('customer')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('type'), fn ($query, $type) => $query->where('type', $type))
            ->when(request('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when(request('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(request('q'), fn ($query, $q) => $query->where('phone', 'like', "%{$q}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('sms_logs.index', compact('logs'));
    }
}
