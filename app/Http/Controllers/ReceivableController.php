<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivableRequest;
use App\Http\Requests\UpdateReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;

class ReceivableController extends Controller
{
    public function index()
    {
        $receivables = Receivable::query()
            ->with('customer')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('customer_id'), fn ($query, $id) => $query->where('customer_id', $id))
            ->when(request('due_date_from'), fn ($query, $date) => $query->whereDate('due_date', '>=', $date))
            ->when(request('due_date_to'), fn ($query, $date) => $query->whereDate('due_date', '<=', $date))
            ->when(request('q'), function ($query, $q) {
                $query->whereHas('customer', function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('receivables.index', compact('receivables', 'customers'));
    }

    public function create()
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('receivables.create', compact('customers'));
    }

    public function store(StoreReceivableRequest $request)
    {
        $data = $request->validated();

        if ($data['status'] === 'paid') {
            $data['paid_at'] = now();
        } else {
            $data['paid_at'] = null;
        }

        Receivable::create($data);

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla olusturuldu.');
    }

    public function edit(Receivable $receivable)
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('receivables.edit', compact('receivable', 'customers'));
    }

    public function update(UpdateReceivableRequest $request, Receivable $receivable)
    {
        $data = $request->validated();

        if ($data['status'] === 'paid') {
            $data['paid_at'] = $receivable->paid_at ?? now();
        } else {
            $data['paid_at'] = null;
        }

        $receivable->update($data);

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla guncellendi.');
    }

    public function destroy(Receivable $receivable)
    {
        $receivable->delete();

        return redirect()->route('receivables.index')
            ->with('success', 'Alacak basariyla silindi.');
    }
}
