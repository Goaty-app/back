<?php

namespace App\DataFixtures;

use App\Entity\Animal;
use App\Entity\Herd;
use App\Entity\User;
use App\Enum\Country;
use App\Enum\Gender;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('test@test.fr')
            ->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'))
            ->setRoles(['ROLE_ADMIN'])
        ;

        $manager->persist($admin);

        $user = new User();
        $password = $this->faker->password(3, 6);
        $user->setEmail($this->faker->name().$password)
            ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
            ->setRoles(['ROLE_USER'])
        ;

        $manager->persist($user);

        $herd = new Herd();
        $herd->setOwner($admin);
        $herd->setName('Goat')
            ->setLocation('Alpes')
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $manager->persist($herd);
        $goat = new Animal();
        $goat->setHerd($herd)
            ->setName('Pepe')
            ->setIdNumber('GOAT-001')
            ->setGender(Gender::FEMALE)
            ->setOriginCountry(Country::FRANCE)
            ->setStatus('Baby')
        ;

        $manager->persist($goat);
        $manager->flush();
    }
}
