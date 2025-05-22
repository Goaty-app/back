<?php

namespace App\Enum;

enum QuantityUnit: string
{
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
