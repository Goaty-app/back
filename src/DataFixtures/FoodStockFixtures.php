<?php

namespace App\DataFixtures;

use App\Entity\FoodStock;
use App\Entity\FoodStockType;
use App\Entity\Herd;
use App\Entity\User;
use App\Enum\QuantityUnit;
use App\Faker\Provider\FoodStockNamesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class FoodStockFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'foodstock-';

    private static array $refs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new FoodStockNamesProvider($this->faker));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            HerdFixtures::class,
            FoodStockTypeFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(1, 2));
    }

    public function load(ObjectManager $manager): void
    {
        self::$refs = [];

        foreach (UserFixtures::getRefs() as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            $herdReferenceKeys = HerdFixtures::getRefs($userRefKey);
            $foodStockTypeReferenceKeys = FoodStockTypeFixtures::getRefs($userRefKey);

            foreach ($herdReferenceKeys as $herdRefKey) {
                /** @var Herd $herd */
                $herd = $this->getReference($herdRefKey, Herd::class);

                $this->faker->unique(true);

                foreach ($this->generateRange() as $i) {
                    /** @var FoodStockType $randomFoodStockType */
                    $randomFoodStockType = $this->getReference($this->faker->randomElement($foodStockTypeReferenceKeys), FoodStockType::class);

                    $foodStock = new FoodStock();
                    $foodStock->setOwner($owner);
                    $foodStock->setHerd($herd);
                    $foodStock->setFoodStockType($randomFoodStockType);
                    $foodStock->setName($this->faker->unique()->getStockName());
                    $foodStock->setQuantity(0);
                    $foodStock->setQuantityUnit($this->faker->randomElement(QuantityUnit::cases()));

                    $manager->persist($foodStock);

                    $ref = self::PREFIX.$herdRefKey.'-'.$i;
                    $this->addReference($ref, $foodStock);
                    self::$refs[$userRefKey][] = $ref;
                }
            }
        }

        $manager->flush();
    }

    public static function getRefs(string $userOwnerKey): array
    {
        return self::$refs[$userOwnerKey] ?? [];
    }
}
