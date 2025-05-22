<?php

namespace App\DataFixtures;

use App\Entity\FoodStock;
use App\Entity\Herd;
use App\Entity\Production;
use App\Entity\ProductionType;
use App\Entity\User;
use DateTime;
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
        $herd->setOwner($admin)
            ->setName('Goat')
            ->setLocation('Alpes')
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $manager->persist($herd);

        $productionType = new ProductionType();
        $productionType->setOwner($admin)
            ->setName('Ma petite production')
        ;

        $manager->persist($productionType);

        $production = new Production();
        $production->setOwner($admin)
            ->setHerd($herd)
            ->setProductionDate(new DateTime())
            ->setExpirationDate(new DateTime())
            ->setQuantity(20.3)
            ->setQuantityUnit('Kilos')
            ->setNotes('Je suis une petite note')
            ->setCreatedAt(new DateTimeImmutable())
            ->addProductionType($productionType)
        ;

        $manager->persist($production);

        $foodStock = new FoodStock();
        $foodStock->setOwner($admin)
            ->setHerd($herd)
            ->setQuantity(0)
            ->setQuantityUnit('Kilos')
            ->setName('Mon petit stock')
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $manager->persist($foodStock);

        $manager->flush();
    }
}
