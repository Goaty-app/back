<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\Healthcare;
use App\Entity\HealthcareType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class HealthcareFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AnimalFixtures::class,
            HealthcareTypeFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(1, 3));
    }

    public function load(ObjectManager $manager): void
    {
        foreach (UserFixtures::getRefs() as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            $animalReferenceKeys = AnimalFixtures::getRefs($userRefKey);
            $healthcareTypeReferenceKeys = HealthcareTypeFixtures::getRefs($userRefKey);

            foreach ($animalReferenceKeys as $animalRefKey) {
                /** @var Animal $animal */
                $animal = $this->getReference($animalRefKey, Animal::class);

                foreach ($this->generateRange() as $i) {
                    /** @var HealthcareType $randomHealthcareType */
                    $randomHealthcareType = $this->getReference($this->faker->randomElement($healthcareTypeReferenceKeys), HealthcareType::class);

                    $healthcare = new Healthcare();
                    $healthcare->setOwner($owner);
                    $healthcare->setAnimal($animal);
                    $healthcare->setHealthcareType($randomHealthcareType);

                    $animalCreationDateObject = $animal->getCreatedAt();
                    $animalCreationDateString = $animalCreationDateObject->format('Y-m-d H:i:s');

                    $careDate = $this->faker->dateTimeBetween($animalCreationDateString, 'now');
                    $healthcare->setCareDate($careDate);

                    $healthcare->setDescription($this->faker->optional(0.7)->sentence(10));

                    $manager->persist($healthcare);
                }
            }
        }

        $manager->flush();
    }
}
