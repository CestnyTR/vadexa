<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;

trait WithTenant
{
    protected Tenant $tenant;

    protected User $user;

    protected function setUpTenant(): void
    {
        $this->tenant = Tenant::create(['name' => 'Test Tenant', 'is_active' => true]);
        TenantContext::setTenantId($this->tenant->id);

        $this->user = User::factory()->owner()->create(['tenant_id' => $this->tenant->id]);
    }
}
