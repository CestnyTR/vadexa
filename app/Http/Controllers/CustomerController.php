<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
    ) {}

    public function index()
    {
        $customers = $this->customerService->getPaginated(request('q'));

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->create($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Musteri basariyla olusturuldu.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Musteri basariyla guncellendi.');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Musteri basariyla silindi.');
    }
}
