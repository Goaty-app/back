<?php

namespace App\Enum;

use App\Trait\EnumDumperTrait;

enum Operation: string
{
    use EnumDumperTrait;

    case PLUS = '+';
    case MINUS = '-';
}
