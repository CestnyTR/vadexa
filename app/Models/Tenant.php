<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
        ];
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
