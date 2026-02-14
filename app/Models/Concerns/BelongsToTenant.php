<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('tenant_id'), TenantContext::tenantId());
        });

        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('tenant_id'))) {
                $model->setAttribute('tenant_id', TenantContext::tenantId());
            }
        });
    }
}
