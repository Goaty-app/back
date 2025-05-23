<?php

namespace App\DataFixtures;

use App\Entity\AnimalType;
use App\Entity\User;
use App\Faker\Provider\AnimalTypesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AnimalTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'animal-type-';

    private static array $refs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new AnimalTypesProvider($this->faker));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(1, 3));
    }

    public function load(ObjectManager $manager): void
    {
        self::$refs = [];

        foreach (UserFixtures::getRefs() as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            if (!isset(self::$refs[$userRefKey])) {
                self::$refs[$userRefKey] = [];
            }

            $this->faker->unique(true);

            foreach ($this->generateRange() as $i) {
                $animalType = new AnimalType();
                $animalType->setOwner($owner);

                $animalType->setName($name = $this->faker->unique()->getAnimalType());

                $manager->persist($animalType);

                $ref = self::PREFIX.$userRefKey.'-'.$i;
                $this->addReference($ref, $animalType);
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
