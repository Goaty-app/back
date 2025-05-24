<?php

namespace App\Enum;

enum BreedingStatus: string
{
    case NOT_ELIGIBLE = 'Not Eligible';
    case OPEN = 'Open';
    case BRED = 'Bred';
    case PREGNANT = 'Pregnant';
    case LACTATING = 'Lactating';
    case WEANED = 'Weaned';
    case CULLED = 'Culled';
}
