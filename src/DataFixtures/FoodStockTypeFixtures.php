<?php

namespace App\DataFixtures;

use App\Entity\FoodStockType;
use App\Entity\User;
use App\Faker\Provider\FoodStockTypesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class FoodStockTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'foodstock-type-';

    private static array $refs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new FoodStockTypesProvider($this->faker));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
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

            $this->faker->unique(true);

            foreach ($this->generateRange() as $i) {
                $foodStockType = new FoodStockType();
                $foodStockType->setOwner($owner);
                $foodStockType->setName($this->faker->unique()->getStockType());

                $manager->persist($foodStockType);

                $ref = self::PREFIX.$userRefKey.'-'.$i;
                $this->addReference($ref, $foodStockType);

                self::$refs[$userRefKey][] = $ref;
            }
        }

        $manager->flush();
    }

    public static function getRefs(string $userOwnerKey): array
    {
        return self::$refs[$userOwnerKey] ?? [];
    }
}
