<?php

namespace App\DataFixtures;

use App\Entity\Herd;
use App\Entity\Production;
use App\Entity\ProductionType;
use App\Entity\User;
use App\Enum\QuantityUnit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ProductionFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            HerdFixtures::class,
            ProductionTypeFixtures::class,
            UserFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(1, 5));
    }

    public function load(ObjectManager $manager): void
    {
        foreach (UserFixtures::getRefs() as $userRef) {
            /** @var User $owner */
            $owner = $this->getReference($userRef, User::class);

            $herdRefs = HerdFixtures::getRefs($userRef);
            $productionTypeRefs = ProductionTypeFixtures::getRefs($userRef);

            foreach ($herdRefs as $herdRef) {
                /** @var Herd $herd */
                $herd = $this->getReference($herdRef, Herd::class);

                foreach ($this->generateRange() as $i) {
                    $production = new Production();
                    $production->setOwner($owner);
                    $production->setHerd($herd);
                    $production->setProductionDate($this->faker->dateTimeBetween('-1 year', 'now'));
                    $production->setExpirationDate($this->faker->dateTimeBetween('now', '+6 months'));
                    $production->setQuantity($this->faker->randomFloat(2, 1, 100));
                    $production->setQuantityUnit($this->faker->randomElement(QuantityUnit::cases()));
                    $production->setNotes($this->faker->optional()->sentence());

                    $production->setProductionType(
                        $this->getReference(
                            $this->faker->randomElement($productionTypeRefs),
                            ProductionType::class,
                        ),
                    );

                    $manager->persist($production);
                }
            }
        }

        $manager->flush();
    }
}
