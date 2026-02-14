<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    /** @use HasFactory<\Database\Factories\ReceivableFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'description',
        'recurrence_type',
        'installment_count',
        'installment_number',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'installment_count' => 'integer',
            'installment_number' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Receivable::class, 'parent_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Receivable::class, 'parent_id');
    }

    public function isRecurring(): bool
    {
        return $this->recurrence_type !== 'once' && $this->recurrence_type !== null;
    }

    public function installmentLabel(): ?string
    {
        if ($this->installment_number && $this->installment_count) {
            return $this->installment_number . '/' . $this->installment_count;
        }

        return null;
    }
}
