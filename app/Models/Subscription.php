<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_PAST_DUE = 'past_due';

    protected $fillable = [
        'tenant_id',
        'plan',
        'status',
        'provider',
        'provider_ref',
        'current_period_ends_at',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'current_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
