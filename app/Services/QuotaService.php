<?php

namespace App\Services;

use App\Exceptions\PlanLimitException;
use App\Models\Customer;
use App\Models\User;
use App\Support\TenantContext;

class QuotaService
{
    public function __construct(
        private TenantService $tenants,
    ) {}

    /**
     * Current usage for a quota key within the active tenant.
     */
    public function usage(string $key): int
    {
        return match ($key) {
            'customers' => Customer::query()->count(),
            'users' => User::query()->where('tenant_id', TenantContext::tenantId())->count(),
            default => 0,
        };
    }

    public function limit(string $key): ?int
    {
        return $this->tenants->effectiveLimit($this->tenants->current(), $key);
    }

    /**
     * Remaining allowance for a quota key, or null when unlimited.
     */
    public function remaining(string $key): ?int
    {
        $limit = $this->limit($key);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->usage($key));
    }

    public function reached(string $key): bool
    {
        $limit = $this->limit($key);

        return $limit !== null && $this->usage($key) >= $limit;
    }

    /**
     * @throws PlanLimitException
     */
    public function assertCanCreate(string $key): void
    {
        $limit = $this->limit($key);

        if ($limit !== null && $this->usage($key) >= $limit) {
            throw PlanLimitException::for($key, $limit);
        }
    }
}
