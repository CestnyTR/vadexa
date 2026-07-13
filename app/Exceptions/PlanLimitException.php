<?php

namespace App\Exceptions;

use RuntimeException;

class PlanLimitException extends RuntimeException
{
    public static function for(string $resource, int $limit): self
    {
        $label = match ($resource) {
            'customers' => 'müşteri',
            'users' => 'kullanıcı',
            'sms_per_month' => 'aylık SMS',
            'mail_per_month' => 'aylık e-posta',
            default => $resource,
        };

        return new self("Plan limitinize ulaştınız ({$label}: {$limit}). Daha fazlası için planınızı yükseltin.");
    }
}
