<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plan',
        'trial_ends_at',
        'is_active',
        'notification_time',
        'sms_enabled',
        'mail_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sms_enabled' => 'boolean',
            'mail_enabled' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Full plan configuration (name, price, limits) for this tenant.
     *
     * @return array<string, mixed>
     */
    public function plan(): array
    {
        $plans = config('plans.plans');

        return $plans[$this->plan] ?? $plans[config('plans.default')];
    }

    /**
     * Limit value for a given key, e.g. 'customers', 'users', 'sms_per_month'.
     * Returns null for unlimited.
     */
    public function limit(string $key): ?int
    {
        $value = $this->plan()['limits'][$key] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function notificationTemplates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }
}
