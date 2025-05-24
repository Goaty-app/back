<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\Breeding;
use App\Entity\User;
use App\Enum\BreedingStatus;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class BreedingFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'breeding-';

    private static array $refs = [];

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
        ];
    }

    private function generateRange($userRefKey): array
    {
        return range(0, $this->faker->numberBetween(1, \count(AnimalFixtures::getFemaleRefs($userRefKey))));
    }

    public function load(ObjectManager $manager): void
    {
        $userReferenceKeys = UserFixtures::getRefs();

        foreach ($userReferenceKeys as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            $femaleAnimalReferenceKeys = AnimalFixtures::getFemaleRefs($userRefKey);
            $maleAnimalReferenceKeys = AnimalFixtures::getMaleRefs($userRefKey);

            foreach ($this->generateRange($userRefKey) as $i) {
                $femaleRefKey = $this->faker->randomElement($femaleAnimalReferenceKeys);
                /** @var Animal $female */
                $female = $this->getReference($femaleRefKey, Animal::class);

                $maleRefKey = $this->faker->randomElement($maleAnimalReferenceKeys);
                /** @var Animal $male */
                $male = $this->getReference($maleRefKey, Animal::class);

                // Skip if the type and the herd are not the same (add more Animals to uncomment the continue)
                if ($female->getAnimalType() !== $male->getAnimalType() || $female->getHerd() !== $male->getHerd()) {
                    // continue;
                }

                $breeding = new Breeding();
                $breeding->setOwner($owner);
                $breeding->setFemale($female);
                $breeding->setMale($male);
                $latestAnimalCreationDate = max(
                    $female->getCreatedAt()->getTimestamp(),
                    $male->getCreatedAt()->getTimestamp(),
                );
                $latestAnimalCreationDateTime = (new DateTimeImmutable())->setTimestamp($latestAnimalCreationDate);
                $latestAnimalCreationDateString = $latestAnimalCreationDateTime->format('Y-m-d H:i:s');

                $matingDateStart = $this->faker->dateTimeBetween($latestAnimalCreationDateString, 'now');
                $breeding->setMatingDateStart($matingDateStart);

                if ($this->faker->boolean(70)) {
                    $breeding->setMatingDateEnd(
                        $this->faker->dateTimeBetween(
                            $matingDateStart->format('Y-m-d H:i:s P'),
                            (clone $matingDateStart)->modify('+7 days')->format('Y-m-d H:i:s P'),
                        ),
                    );
                }

                $breeding->setExpectedChildCount($this->faker->optional(0.8, 0)->numberBetween(1, 3));
                $breeding->setStatus($this->faker->randomElement(BreedingStatus::cases()));
                $breeding->setCreatedAt(DateTimeImmutable::createFromMutable($matingDateStart));

                $manager->persist($breeding);

                $ref = self::PREFIX.$userRefKey.'-'.$i;
                $this->addReference($ref, $breeding);
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
