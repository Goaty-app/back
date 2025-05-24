<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\Birth;
use App\Entity\Breeding;
use App\Entity\User;
use App\Enum\Gender;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class BirthFixtures extends Fixture implements DependentFixtureInterface
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
            BreedingFixtures::class,
            AnimalFixtures::class,
            AnimalTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $userReferenceKeys = UserFixtures::getRefs();

        foreach ($userReferenceKeys as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);
            $breedingReferenceKeys = BreedingFixtures::getRefs($userRefKey);

            if (empty($breedingReferenceKeys)) {
                continue;
            }

            foreach ($breedingReferenceKeys as $breedingRefKey) {
                /** @var Breeding $breeding */
                $breeding = $this->getReference($breedingRefKey, Breeding::class);

                if (null === $breeding->getFemale() || null === $breeding->getFemale()->getAnimalType() || null === $breeding->getFemale()->getHerd()) {
                    continue;
                }

                $this->faker->unique(true);

                $expectedChildren = $breeding->getExpectedChildCount() ?? $this->faker->numberBetween(1, 2);
                for ($c = 0; $c < $expectedChildren; ++$c) {
                    $birth = new Birth();
                    $birth->setOwner($owner);
                    $birth->setBreeding($breeding);

                    $child = new Animal();
                    $child->setOwner($owner);
                    $child->setHerd($breeding->getFemale()->getHerd());
                    $child->setAnimalType($breeding->getFemale()->getAnimalType());

                    $gender = $this->faker->randomElement(Gender::cases());
                    $child->setGender($gender);
                    $child->setName(Gender::MALE === $gender ? $this->faker->firstNameMale() : $this->faker->firstNameFemale());

                    $child->setIdNumber($this->faker->unique()->regexify('[A-Z]{2}-[0-4]{3}'));

                    $child->setOriginCountry($breeding->getFemale()->getOriginCountry());
                    $child->setStatus('Nouveau-né');

                    $matingStartDate = $breeding->getMatingDateStart();
                    if (!$matingStartDate instanceof DateTimeInterface) {
                        $matingStartDate = (clone $breeding->getCreatedAt())->modify('-150 days');
                    }
                    $minBirthDate = (clone DateTime::createFromInterface($matingStartDate))->modify('+150 days');
                    $maxBirthDate = (clone $minBirthDate)->modify('+20 days');

                    /** @var DateTimeInterface $birthDateFaker */
                    $birthDateFaker = $this->faker->dateTimeBetween($minBirthDate, $maxBirthDate);

                    $birth->setBirthDate($birthDateFaker);

                    $birth->setBirthWeight($this->faker->optional(0.8)->randomFloat(2, 0.5, 5.0));
                    $birth->setNotes($this->faker->optional(0.3)->sentence());

                    $birth->setChild($child);

                    $manager->persist($child);
                    $manager->persist($birth);
                }
            }
        }

        $manager->flush();
    }
}
