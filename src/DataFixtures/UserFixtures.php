<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const PREFIX = 'user-';

    private UserPasswordHasherInterface $passwordHasher;
    private Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    private function generateRange(): array
    {
        return range(2, 4);
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
            ->setRoles(['ROLE_ADMIN'])
        ;
        $manager->persist($admin);
        $this->addReference(self::PREFIX. 0, $admin);

        $defaultUser = new User();
        $defaultUser->setEmail('user@example.com')
            ->setPassword($this->passwordHasher->hashPassword($defaultUser, 'password'))
            ->setRoles(['ROLE_USER'])
        ;
        $manager->persist($defaultUser);
        $this->addReference(self::PREFIX. 1, $defaultUser);

        foreach ($this->generateRange() as $i) {
            $user = new User();
            $user->setEmail($this->faker->unique()->safeEmail())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER'])
            ;
            $manager->persist($user);
            $this->addReference(self::PREFIX.$i, $user);
        }

        $manager->flush();
    }

    public static function getRefs(): array
    {
        foreach (range(0, 4) as $i) {
            $refs[] = static::PREFIX.$i;
        }

        return $refs;
    }
}
