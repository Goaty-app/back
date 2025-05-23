<?php

namespace App\Faker\Provider;

class HealthcareTypesProvider
{
    protected static array $types = [
        'Vaccination Annuelle',
        'Vermifugation Printemps',
        'Contrôle Parasitaire Externe',
        'Bilan Sanitaire Troupeau',
        'Soin des Onglons/Sabots',
        'Examen de Gestation',
        'Prise de Sang Analyse',
        'Traitement Antibiotique',
        'Soin Post-Mise Bas',
        'Désinfection Bâtiment',
        'Conseil Nutritionnel',
        'Visite Vétérinaire d\'Urgence',
        'Identification Électronique',
        'Test Tuberculination',
        'Analyse Coprologique',
    ];

    public static function getHealthcareType()
    {
        return static::$types[array_rand(static::$types)];
    }
}
