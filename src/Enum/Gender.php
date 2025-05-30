<?php

namespace App\Enum;

use App\Trait\EnumDumperTrait;

enum Gender: string
{
    use EnumDumperTrait;

    case MALE = 'male';
    case FEMALE = 'female';
}
