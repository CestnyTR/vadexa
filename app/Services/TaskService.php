<?php

namespace App\Services;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->tasks->paginate($filters);
    }

    public function create(array $data): Task
    {
        return $this->tasks->create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $this->tasks->update($task, $data);
    }

    public function delete(Task $task): bool
    {
        return $this->tasks->delete($task);
    }
}
