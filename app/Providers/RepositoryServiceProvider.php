<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ReceivableRepositoryInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Repositories\Eloquent\EloquentCustomerRepository;
use App\Repositories\Eloquent\EloquentReceivableRepository;
use App\Repositories\Eloquent\EloquentTaskRepository;
use App\Services\Payments\IyzicoGateway;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(ReceivableRepositoryInterface::class, EloquentReceivableRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);

        $this->app->bind(PaymentGateway::class, IyzicoGateway::class);
    }
}
