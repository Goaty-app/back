<?php

namespace App\Faker\Provider;

class AnimalStatusProvider
{
    protected static array $status = [
        'Actif',
        'En quarantaine',
        'Vendu',
        'Décédé',
        'Nouveau-né',
        'Jeune',
        'Adulte Reproducteur',
    ];

    public static function getStatus()
    {
        return static::$status[array_rand(static::$status)];
    }
}
