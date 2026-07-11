<?php

namespace App\Contracts\Repositories;

use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReceivableRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateForDashboard(string $tab, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function aggregateByDateCriteria(string $condition, Carbon $value, ?Carbon $valueEnd = null): object;

    public function getUnpaidDueBefore(Carbon $date): Collection;

    public function find(int $id): ?Receivable;

    public function create(array $data): Receivable;

    public function update(Receivable $receivable, array $data): bool;

    public function delete(Receivable $receivable): bool;
}
