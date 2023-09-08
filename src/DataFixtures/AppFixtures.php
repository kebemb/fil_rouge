<?php

namespace App\DataFixtures;

use App\Entity\Type;
use App\Entity\User;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();

        $user = new User();
        $user->setNom('besse');
        $user->setPrenom('seb');
        $user->setEmail('seb@gmail.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, '123'));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);


        $manager->flush();
    }
}
