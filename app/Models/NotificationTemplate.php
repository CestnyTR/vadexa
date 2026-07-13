<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    public const CHANNELS = ['sms', 'mail'];

    public const TYPES = ['upcoming', 'due_today', 'overdue'];

    protected $fillable = [
        'tenant_id',
        'channel',
        'type',
        'body',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
