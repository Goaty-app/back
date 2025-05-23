<?php

namespace App\DataFixtures;

use App\Entity\HealthcareType;
use App\Entity\User;
use App\Faker\Provider\HealthcareTypesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class HealthcareTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'healthcare-type-';

    private static array $refs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new HealthcareTypesProvider($this->faker));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(1, 4));
    }

    public function load(ObjectManager $manager): void
    {
        self::$refs = [];

        foreach (UserFixtures::getRefs() as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            $this->faker->unique(true);

            foreach ($this->generateRange() as $i) {
                $healthcareType = new HealthcareType();
                $healthcareType->setOwner($owner);
                $healthcareType->setName($this->faker->getHealthcareType());

                $manager->persist($healthcareType);

                $ref = self::PREFIX.$userRefKey.'-'.$i;
                $this->addReference($ref, $healthcareType);
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
