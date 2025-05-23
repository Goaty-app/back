<?php

namespace App\Faker\Provider;

class FoodStockTypesProvider
{
    protected static array $types = [
        'Foin de Crau AOP',
        'Granulés Bio Croissance',
        'Bloc de Minéraux',
        'Paille Dépoussiérée',
        'Luzerne Déshydratée',
        'Ensilage d\'Herbe',
        'Maïs Concassé',
        'Orge Aplati',
        'Betteraves Fourragères',
        'Mélange Céréalier Complet',
    ];

    public static function getStockType()
    {
        return static::$types[array_rand(static::$types)];
    }
}
