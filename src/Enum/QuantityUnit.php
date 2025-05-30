<?php

namespace App\Enum;

use App\Trait\EnumDumperTrait;

enum QuantityUnit: string
{
    use EnumDumperTrait;

    // Unités métriques
    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case MILLIGRAM = 'mg';
    case TONNE_METRIC = 't';

    // Unités impériales / US
    case POUND = 'lb';
    case OUNCE = 'oz';

    // Unités non massiques
    case PIECE = 'piece';
    case UNIT = 'unit';
}
