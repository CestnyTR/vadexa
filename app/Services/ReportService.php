<?php

namespace App\Services;

use App\Models\Receivable;
use Carbon\Carbon;

class ReportService
{
    /**
     * Aging buckets for unpaid receivables in the active tenant, keyed by label
     * with ['count' => int, 'amount' => float].
     *
     * @return array<string, array{count: int, amount: float}>
     */
    public function aging(): array
    {
        $today = Carbon::today();

        $buckets = [
            'not_due' => ['count' => 0, 'amount' => 0.0],
            '1_30' => ['count' => 0, 'amount' => 0.0],
            '31_60' => ['count' => 0, 'amount' => 0.0],
            '61_90' => ['count' => 0, 'amount' => 0.0],
            '90_plus' => ['count' => 0, 'amount' => 0.0],
        ];

        Receivable::query()
            ->where('status', '!=', 'paid')
            ->select('due_date', 'amount')
            ->get()
            ->each(function (Receivable $r) use (&$buckets, $today): void {
                $key = match (true) {
                    $r->due_date->gte($today) => 'not_due',
                    $r->due_date->diffInDays($today) <= 30 => '1_30',
                    $r->due_date->diffInDays($today) <= 60 => '31_60',
                    $r->due_date->diffInDays($today) <= 90 => '61_90',
                    default => '90_plus',
                };

                $buckets[$key]['count']++;
                $buckets[$key]['amount'] += (float) $r->amount;
            });

        return $buckets;
    }

    /**
     * Collection summary: totals and paid ratio for the active tenant.
     *
     * @return array{total_amount: float, paid_amount: float, outstanding_amount: float, total_count: int, paid_count: int, rate: float}
     */
    public function collection(): array
    {
        $totalAmount = (float) Receivable::query()->sum('amount');
        $paidAmount = (float) Receivable::query()->where('status', 'paid')->sum('amount');
        $totalCount = Receivable::query()->count();
        $paidCount = Receivable::query()->where('status', 'paid')->count();

        return [
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $totalAmount - $paidAmount,
            'total_count' => $totalCount,
            'paid_count' => $paidCount,
            'rate' => $totalAmount > 0 ? round($paidAmount / $totalAmount * 100, 1) : 0.0,
        ];
    }
}
