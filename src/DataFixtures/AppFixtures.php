<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Account;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $faker= Faker\Factory:: create('fr_FR');

        $account = new Account();
        $account
            ->setEmail('arthur@youpi.fr')
            ->setPassword($this->passwordHasher->hashPassword($account, 'Fr84!'))
            ->setRoles(['ROLE_ADMIN'])
            ->setStatus("open")
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;

        $manager->persist($account);
        $manager->flush();
    }
}
// test: email: account1@authws.com, pw: account1, role: Admin et user