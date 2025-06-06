<?php

namespace App\Enum\Trait;

use BackedEnum;

trait EnumDumperTrait
{
    public static function enumValues(): array
    {
        return array_map(static fn (BackedEnum $item): string|int => $item->value, self::cases());
    }

    public static function enumNames(): array
    {
        return array_map(static fn (BackedEnum $item): string => $item->name, self::cases());
    }
}
