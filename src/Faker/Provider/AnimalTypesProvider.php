<?php

namespace App\Faker\Provider;

class AnimalTypesProvider
{
    protected static array $types = [
        'Chèvre',
        'Mouton',
        'Vache',
        'Âne',
        'Lapin',
    ];

    public static function getAnimalType()
    {
        return static::$types[array_rand(static::$types)];
    }
}
