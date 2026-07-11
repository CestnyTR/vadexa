<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentReceivableRepository implements ReceivableRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Receivable::query()
            ->with('customer')
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['customer_id'] ?? null, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($filters['due_date_from'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '>=', $d))
            ->when($filters['due_date_to'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '<=', $d))
            ->when($filters['q'] ?? null, function ($q, $search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForDashboard(string $tab, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $today = Carbon::today();
        $query = Receivable::query()->with('customer');

        match ($tab) {
            'overdue' => $query->where('status', '!=', 'paid')->whereDate('due_date', '<', $today),
            'due_today' => $query->where('status', '!=', 'paid')->whereDate('due_date', $today),
            'upcoming' => $query->where('status', '!=', 'paid')->whereDate('due_date', '>', $today),
            'paid' => $query->where('status', 'paid'),
            default => $query->where('status', '!=', 'paid')->whereDate('due_date', '<', $today),
        };

        if ($q = $filters['q'] ?? null) {
            $query->where(function ($qb) use ($q) {
                $qb->whereHas('customer', function ($cq) use ($q) {
                    $cq->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            });
        }

        if ($customerId = $filters['customer_id'] ?? null) {
            $query->where('customer_id', $customerId);
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('due_date', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('due_date', '<=', $dateTo);
        }

        return $query->orderBy('due_date')->paginate($perPage)->withQueryString();
    }

    public function aggregateByDateCriteria(string $condition, Carbon $value, ?Carbon $valueEnd = null): object
    {
        $query = Receivable::query()->where('status', '!=', 'paid');

        match ($condition) {
            'equals' => $query->whereDate('due_date', $value),
            'before' => $query->whereDate('due_date', '<', $value),
            'between' => $query->whereDate('due_date', '>', $value)
                               ->whereDate('due_date', '<=', $valueEnd),
        };

        return $query->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')->first();
    }

    public function getUnpaidDueBefore(Carbon $date): Collection
    {
        return Receivable::query()
            ->with('customer')
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<=', $date)
            ->get();
    }

    public function find(int $id): ?Receivable
    {
        return Receivable::find($id);
    }

    public function create(array $data): Receivable
    {
        return Receivable::create($data);
    }

    public function update(Receivable $receivable, array $data): bool
    {
        return $receivable->update($data);
    }

    public function delete(Receivable $receivable): bool
    {
        return $receivable->delete();
    }
}
