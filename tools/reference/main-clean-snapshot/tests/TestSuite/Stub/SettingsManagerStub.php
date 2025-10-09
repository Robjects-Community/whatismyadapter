<?php
declare(strict_types=1);

namespace App\TestSuite\Stub;

class SettingsManagerStub
{
    private static array $store = [];

    public static function set(string $key, mixed $value): void
    {
        self::$store[$key] = $value;
    }

    public static function reset(): void
    {
        self::$store = [];
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        return self::$store[$key] ?? $default;
    }
}
