<?php

namespace App\Services;

use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReceivableService
{
    public function __construct(
        private ReceivableRepositoryInterface $receivables,
    ) {}

    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->receivables->paginate($filters);
    }

    public function find(int $id): ?Receivable
    {
        return $this->receivables->find($id);
    }

    public function create(array $data): Receivable
    {
        if ($data['status'] === 'paid') {
            $data['paid_at'] = now();
        } else {
            $data['paid_at'] = null;
        }

        return $this->receivables->create($data);
    }

    public function createWithInstallments(array $data): array
    {
        if ($data['status'] === 'paid') {
            $data['paid_at'] = now();
        }

        $recurrenceType = $data['recurrence_type'] ?? 'once';
        $installmentCount = (int) ($data['installment_count'] ?? 1);

        if ($recurrenceType !== 'once' && $installmentCount >= 2) {
            $baseDate = Carbon::parse($data['due_date']);

            $parent = $this->receivables->create(array_merge($data, [
                'recurrence_type' => $recurrenceType,
                'installment_count' => $installmentCount,
                'installment_number' => 1,
                'parent_id' => null,
            ]));

            for ($i = 2; $i <= $installmentCount; $i++) {
                $nextDate = $this->advanceDate($baseDate->copy(), $recurrenceType, $i - 1);

                $this->receivables->create([
                    'tenant_id' => $parent->tenant_id,
                    'customer_id' => $data['customer_id'],
                    'amount' => $data['amount'],
                    'due_date' => $nextDate,
                    'status' => 'upcoming',
                    'description' => $data['description'] ?? null,
                    'recurrence_type' => $recurrenceType,
                    'installment_count' => $installmentCount,
                    'installment_number' => $i,
                    'parent_id' => $parent->id,
                ]);
            }

            return ['receivables_created' => $installmentCount, 'parent' => $parent];
        }

        $receivable = $this->receivables->create($data);

        return ['receivables_created' => 1, 'parent' => $receivable];
    }

    public function update(Receivable $receivable, array $data): bool
    {
        if ($data['status'] === 'paid') {
            $data['paid_at'] = $receivable->paid_at ?? now();
        } else {
            $data['paid_at'] = null;
        }

        return $this->receivables->update($receivable, $data);
    }

    public function markPaid(Receivable $receivable): bool
    {
        return $this->receivables->update($receivable, [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function delete(Receivable $receivable): bool
    {
        return $this->receivables->delete($receivable);
    }

    protected function advanceDate(Carbon $base, string $type, int $steps): Carbon
    {
        return match ($type) {
            'weekly' => $base->addWeeks($steps),
            'biweekly' => $base->addWeeks($steps * 2),
            'monthly' => $base->addMonths($steps),
            'yearly' => $base->addYears($steps),
            default => $base->addMonths($steps),
        };
    }
}
