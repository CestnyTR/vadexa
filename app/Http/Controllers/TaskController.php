<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Customer;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::query()
            ->with('customer')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('due_date_from'), fn ($query, $date) => $query->whereDate('due_date', '>=', $date))
            ->when(request('due_date_to'), fn ($query, $date) => $query->whereDate('due_date', '<=', $date))
            ->when(request('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('tasks.create', compact('customers'));
    }

    public function store(StoreTaskRequest $request)
    {
        Task::create($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Gorev basariyla olusturuldu.');
    }

    public function edit(Task $task)
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('tasks.edit', compact('task', 'customers'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Gorev basariyla guncellendi.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Gorev basariyla silindi.');
    }
}
