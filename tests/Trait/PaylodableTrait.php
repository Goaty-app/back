<?php

namespace App\Tests\Trait;

trait PaylodableTrait
{
    private static bool $isInit = false;

    private static array $requiredPayload = [];

    private static array $optionalPayload = [];

    private static array $implicitPayload = [];

    public static function getImplicitPayload(): array
    {
        static::setupPayload();

        return static::$implicitPayload;
    }

    private static function setImplicitPayload(): array
    {
        return [];
    }

    public static function getRequiredPayload(): array
    {
        static::setupPayload();

        return static::$requiredPayload;
    }

    private static function setRequiredPayload(): array
    {
        return [];
    }

    public static function getOptionalPayload(): array
    {
        static::setupPayload();

        return static::$optionalPayload;
    }

    private static function setOptionalPayload(): array
    {
        return [];
    }

    private static function setupPayload(): void
    {
        if (!static::$isInit) {
            static::$implicitPayload = static::setImplicitPayload();
            static::$requiredPayload = static::setRequiredPayload();
            static::$optionalPayload = static::setOptionalPayload();

            static::$isInit = true;
        }
    }

    protected function overrideSetUp(): void
    {
        static::setupPayload();
    }
}
