<?php

namespace App\DataFixtures;

use App\Entity\FoodStock;
use App\Entity\FoodStockHistory;
use App\Entity\User;
use App\Enum\Operation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class FoodStockHistoryFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    private array $weightedOperations;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');

        $this->weightedOperations = [
            Operation::PLUS,
            Operation::PLUS,
            Operation::PLUS,
            Operation::MINUS,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            FoodStockFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(2, 5));
    }

    public function load(ObjectManager $manager): void
    {
        foreach (UserFixtures::getRefs() as $userRef) {
            /** @var User $owner */
            $owner = $this->getReference($userRef, User::class);

            $foodStockReferenceKeys = FoodStockFixtures::getRefs($userRef);

            foreach ($foodStockReferenceKeys as $foodStockRefKey) {
                /** @var FoodStock $foodStock */
                $foodStock = $this->getReference($foodStockRefKey, FoodStock::class);

                foreach ($this->generateRange() as $i) {
                    $foodStockHistory = new FoodStockHistory();
                    $foodStockHistory->setOwner($owner);
                    $foodStockHistory->setFoodStock($foodStock);
                    $foodStockHistory->setOperation($this->faker->randomElement($this->weightedOperations));
                    $foodStockHistory->setQuantity($this->faker->randomFloat(2, 1, 1000));

                    $manager->persist($foodStockHistory);

                    match ($foodStockHistory->getOperation()) {
                        Operation::PLUS  => $foodStock->setQuantity($foodStock->getQuantity() + $foodStockHistory->getQuantity()),
                        Operation::MINUS => $foodStock->setQuantity($foodStock->getQuantity() - $foodStockHistory->getQuantity()),
                    };

                    $manager->persist($foodStock);
                }
            }
        }

        $manager->flush();
    }
}
