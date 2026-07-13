<?php

namespace App\Support;

use RuntimeException;

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
            throw new RuntimeException('No tenant is set in the current context.');
        }

        return static::$tenantId;
    }

    public static function hasTenant(): bool
    {
        return static::$tenantId !== null;
    }

    public static function id(): ?int
    {
        return static::$tenantId;
    }

    public static function reset(): void
    {
        static::$tenantId = null;
    }

    /**
     * Run a callback within a given tenant context, restoring the previous one afterwards.
     */
    public static function run(int $tenantId, callable $callback): mixed
    {
        $previous = static::$tenantId;
        static::$tenantId = $tenantId;

        try {
            return $callback();
        } finally {
            static::$tenantId = $previous;
        }
    }
}
