<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
    ) {}

    public function getPaginated(?string $search = null): LengthAwarePaginator
    {
        return $this->customers->paginate($search);
    }

    public function getDropdownList(): Collection
    {
        return $this->customers->allForDropdown();
    }

    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $this->customers->update($customer, $data);
    }

    public function delete(Customer $customer): bool
    {
        return $this->customers->delete($customer);
    }
}
