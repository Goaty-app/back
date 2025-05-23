<?php

namespace App\Faker\Provider;

class FoodStockNamesProvider
{
    protected static array $names = [
        'Foin de Prairie Naturelle',
        'Foin de Luzerne Bio',
        'Foin de Crau AOP',
        'Paille d\'Orge Dépoussiérée',
        'Foin de Montagne Séché au Soleil',
        'Foin de Trèfle Rouge',
        'Foin Timothy de Qualité Supérieure',
        'Paille de Blé Hachée',
        'Granulés Équilibre Complet',
        'Aliment Lactation Haute Performance',
        'Muesli Croissance Jeunes Animaux',
        'Granulés Entretien Adultes',
        'Aliment Bio sans OGM pour Volailles',
        'Granulés Spécial Chèvres Laitières',
        'Aliment Complet pour Moutons',
        'Flocons de Céréales Mélangés',
        'Maïs Concassé Jaune',
        'Orge Aplati Dépoussiéré',
        'Avoine Entière Non Traitée',
        'Blé Dur de Qualité',
        'Graines de Lin Extrudées',
        'Tournesol Noir Strié',
        'Mélange de Graines Énergétique',
        'Sorgho Grain Rouge',
        'Bloc de Sel Marin Pur',
        'Seau à Lécher Minéraux et Vitamines',
        'Poudre de Calcium et Phosphore',
        'Levure de Bière en Flocons',
        'Huile de Foie de Morue Naturelle',
        'Supplément Probiotique Actif',
        'Vinaigre de Cidre Bio pour Animaux',
        'Argile Bentonite Purifiée',
        'Ensilage d\'Herbe Préfanée',
        'Ensilage de Maïs Épi',
        'Pulpe de Betterave Déshydratée',
        'Drêches de Brasserie Fraîches',
        'Luzerne Enrubannée',
        'Carottes Fraîches en Vrac',
        'Pommes Golden Déclassées',
        'Pain Dur Séché (complément)',
        'Herbes Aromatiques Séchées pour Lapins',
        'Friandises Naturelles aux Fruits Rouges',
    ];

    public static function getStockName()
    {
        return static::$names[array_rand(static::$names)];
    }
}
