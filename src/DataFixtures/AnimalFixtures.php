<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\AnimalType;
use App\Entity\Herd;
use App\Entity\User;
use App\Enum\Country;
use App\Enum\Gender;
use App\Faker\Provider\AnimalNamesProvider;
use App\Faker\Provider\AnimalStatusProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AnimalFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'animal-';

    private static array $refs = [];
    private static array $femaleRefs = [];
    private static array $maleRefs = [];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new AnimalNamesProvider($this->faker));
        $this->faker->addProvider(new AnimalStatusProvider($this->faker));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            HerdFixtures::class,
            AnimalTypeFixtures::class,
        ];
    }

    private function generateRange(): array
    {
        return range(0, $this->faker->numberBetween(5, 15));
    }

    public function load(ObjectManager $manager): void
    {
        self::$refs = [];

        foreach (UserFixtures::getRefs() as $userRefKey) {
            /** @var User $owner */
            $owner = $this->getReference($userRefKey, User::class);

            $herdReferenceKeys = HerdFixtures::getRefs($userRefKey);
            $animalTypeReferenceKeys = AnimalTypeFixtures::getRefs($userRefKey);

            if (empty($herdReferenceKeys) || empty($animalTypeReferenceKeys)) {
                continue;
            }

            if (!isset(self::$refs[$userRefKey])) {
                self::$refs[$userRefKey] = [];
            }

            $availableAnimalTypes = [];
            foreach ($animalTypeReferenceKeys as $atKey) {
                $availableAnimalTypes[] = $this->getReference($atKey, AnimalType::class);
            }

            foreach ($herdReferenceKeys as $herdRefKey) {
                /** @var Herd $herd */
                $herd = $this->getReference($herdRefKey, Herd::class);

                $this->faker->unique(true);

                foreach ($this->generateRange() as $i) {
                    /** @var AnimalType $randomAnimalType */
                    $randomAnimalType = $this->faker->randomElement($availableAnimalTypes);

                    $animal = new Animal();
                    $animal->setOwner($owner);
                    $animal->setHerd($herd);
                    $animal->setAnimalType($randomAnimalType);
                    $animal->setGender($this->faker->randomElement(Gender::cases()));
                    $animal->setName($this->faker->unique()->goatName());

                    $animal->setIdNumber($this->faker->unique()->regexify('[A-Z]{2}-[0-4]{3}'));

                    $animal->setOriginCountry($this->faker->randomElement(Country::cases()));
                    $animal->setBehaviorNotes($this->faker->optional(0.3)->sentence());
                    $animal->setStatus($this->faker->getStatus());

                    $manager->persist($animal);

                    $ref = self::PREFIX.$herdRefKey.'-'.$i;
                    $this->addReference($ref, $animal);
                    self::$refs[$userRefKey][] = $ref;
                    if (Gender::FEMALE === $animal->getGender()) {
                        self::$femaleRefs[$userRefKey][] = $ref;
                    } else {
                        self::$maleRefs[$userRefKey][] = $ref;
                    }
                }
            }
        }
        $manager->flush();
    }

    public static function getRefs(string $userOwnerKey): array
    {
        return self::$refs[$userOwnerKey] ?? [];
    }

    public static function getFemaleRefs(string $userOwnerKey): array
    {
        return self::$femaleRefs[$userOwnerKey] ?? [];
    }

    public static function getMaleRefs(string $userOwnerKey): array
    {
        return self::$maleRefs[$userOwnerKey] ?? [];
    }
}
