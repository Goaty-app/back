<?php

namespace App\DataFixtures;

use App\Entity\Herd;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class HerdFixtures extends Fixture implements DependentFixtureInterface
{
    public const PREFIX = 'herd-';

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

            foreach ($this->generateRange() as $i) {
                $herd = new Herd();
                $herd->setOwner($owner)
                    ->setName('Troupeau de '.$this->faker->word())
                    ->setLocation($this->faker->city())
                    ->setCreatedAt(
                        DateTimeImmutable::createFromMutable(
                            $this->faker->dateTimeThisDecade(),
                        ),
                    )
                ;

                $manager->persist($herd);

                $ref = self::PREFIX.$userRef.'-'.$i;
                $this->addReference($ref, $herd);

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
