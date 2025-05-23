<?php

namespace App\Faker\Provider;

class AnimalNamesProvider
{
    protected static array $names = [
        'Biquette Bardot',
        'Chèvre Guevara',
        'Goatzilla',
        'Vincent Van Goat',
        'Billy The Kid',
        'E-goat',
        'Meryl Cheep',
        'Al Caprone',
        'Goatye',
        'Chewbacca',
        'Jean-Claude Van Damme de Lait',
        'Bêêêyoncé',
        'Captain Jack Sparrow',
        'Lady Gaga-dget',
        'Sergent Poivre',
        'Brad Pitt-bullion',
        'Leonardo DiCapricorne',
        'Bêêêthoven',
        'Angela Merbêêêl',
        'Goatdalf Le Gris',
        'Hercule Poivrot',
        'Indiana Bones',
        'Marie Curie-euse',
        'Napoléon Bonabroute',
        'Panic! At The Disco-rn',
        'Poulidor de Pâturage',
        'Sherlock Holmes-tein',
        'Taylor Swift-hooves',
        'Usain Bolt-de-foin',
        'Zinedine Ziehdane',
    ];

    public static function goatName()
    {
        return static::$names[array_rand(static::$names)];
    }
}
