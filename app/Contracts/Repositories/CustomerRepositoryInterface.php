<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function allForDropdown(): Collection;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): bool;

    public function delete(Customer $customer): bool;
}
