<?php

namespace App\Enum;

use App\Enum\Trait\EnumDumperTrait;

enum BreedingStatus: string
{
    use EnumDumperTrait;

    case NOT_ELIGIBLE = 'NotEligible';
    case OPEN = 'Open';
    case BRED = 'Bred';
    case PREGNANT = 'Pregnant';
    case LACTATING = 'Lactating';
    case WEANED = 'Weaned';
    case CULLED = 'Culled';
}
