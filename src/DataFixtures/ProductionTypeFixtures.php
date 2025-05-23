<?php

namespace App\DataFixtures;

use App\Entity\ProductionType;
use App\Entity\User;
use App\Faker\Provider\ProductionTypesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ProductionTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'production-type-';

    private static array $refs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new ProductionTypesProvider($this->faker));
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

        foreach (UserFixtures::getRefs() as $userRef) {
            /** @var User $owner */
            $owner = $this->getReference($userRef, User::class);

            $this->faker->unique(true);

            foreach ($this->generateRange() as $i) {
                $productionType = new ProductionType();
                $productionType->setOwner($owner)
                    ->setName($this->faker->unique()->getProductionType())
                ;

                $manager->persist($productionType);

                $ref = self::PREFIX.$userRef.'-'.$i;
                $this->addReference($ref, $productionType);
                self::$refs[$userRef][] = $ref;
            }
        }

        $manager->flush();
    }

    public static function getRefs(string $userOwnerKey): array
    {
        return self::$refs[$userOwnerKey] ?? [];
    }
}
