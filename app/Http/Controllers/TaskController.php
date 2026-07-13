<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\CustomerService;
use App\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private CustomerService $customerService,
    ) {}

    public function index()
    {
        $tasks = $this->taskService->getPaginated(
            request()->only(['status', 'due_date_from', 'due_date_to', 'q'])
        );

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $customers = $this->customerService->getDropdownList();

        return view('tasks.create', compact('customers'));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->taskService->create($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Görev başarıyla oluşturuldu.');
    }

    public function edit(Task $task)
    {
        $customers = $this->customerService->getDropdownList();

        return view('tasks.edit', compact('task', 'customers'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->taskService->update($task, $request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Görev başarıyla güncellendi.');
    }

    public function destroy(Task $task)
    {
        $this->taskService->delete($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Görev başarıyla silindi.');
    }
}
