<?php

namespace App\Support;

use App\Models\Tenant;

class TenantContext
{
    protected static ?int $tenantId = null;

    public static function setTenantId(int $id): void
    {
        static::$tenantId = $id;
    }

    public static function tenantId(): int
    {
        if (static::$tenantId === null) {
            $tenant = Tenant::query()
                ->where('is_active', true)
                ->firstOrFail();

            static::$tenantId = $tenant->id;
        }

        return static::$tenantId;
    }
}
