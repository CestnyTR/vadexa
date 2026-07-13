<?php

namespace App\Services\Payments;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $error = null,
    ) {}

    public static function success(string $reference): self
    {
        return new self(true, $reference);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }
}
