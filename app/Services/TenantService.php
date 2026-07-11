<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\TenantContext;

class TenantService
{
    public function updateSettings(array $data): bool
    {
        $tenant = Tenant::findOrFail(TenantContext::tenantId());

        return $tenant->update($data);
    }
}
