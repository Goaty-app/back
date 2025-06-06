<?php

namespace App\Enum;

use App\Enum\Trait\EnumDumperTrait;

enum Operation: string
{
    use EnumDumperTrait;

    case PLUS = '+';
    case MINUS = '-';
}
