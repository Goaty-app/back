<?php

namespace App\Faker\Provider;

class ProductionTypesProvider
{
    protected static array $types = [
        'Lait Cru',
        'Fromage Affiné',
        'Yaourt Nature',
        'Laine Brute',
        'Miel de Montagne',
        'Confiture Artisanale',
        'Jus de Pomme Bio',
        'Viande Séchée',
        'Oeufs Plein Air',
        'Savon au Lait',
    ];

    public static function getProductionType()
    {
        return static::$types[array_rand(static::$types)];
    }
}
