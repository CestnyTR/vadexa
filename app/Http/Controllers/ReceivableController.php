<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Receivable;
use App\Services\CustomerService;
use App\Services\ReceivableService;

class ReceivableController extends Controller
{
    public function __construct(
        private ReceivableService $receivableService,
        private CustomerService $customerService,
    ) {}

    public function index()
    {
        $receivables = $this->receivableService->getPaginated(
            request()->only(['status', 'customer_id', 'due_date_from', 'due_date_to', 'q'])
        );
        $customers = $this->customerService->getDropdownList();

        return view('receivables.index', compact('receivables', 'customers'));
    }

    public function create()
    {
        $customers = $this->customerService->getDropdownList();

        return view('receivables.create', compact('customers'));
    }

    public function store(StoreReceivableRequest $request)
    {
        $this->receivableService->create($request->validated());

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla olusturuldu.');
    }

    public function edit(Receivable $receivable)
    {
        $customers = $this->customerService->getDropdownList();

        return view('receivables.edit', compact('receivable', 'customers'));
    }

    public function update(UpdateReceivableRequest $request, Receivable $receivable)
    {
        $this->receivableService->update($receivable, $request->validated());

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla guncellendi.');
    }

    public function destroy(Receivable $receivable)
    {
        $this->receivableService->delete($receivable);

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla silindi.');
    }
}
