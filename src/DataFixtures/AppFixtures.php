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
        
        for($i=1; $i<50;$i++){
            $account = new Account();
            $password = $this->passwordHasher->hashPassword($account, "account".$i);
           
            $account->setEmail("account" . $i . "@youpi.com");
            $account->setPassword($password);
         // les 5 premiers ont le roles admin
            if ($i <= 5) {
                $account->setRoles(['ROLE_ADMIN']);
            }else {
                $account->setRoles(['ROLE_USER']);
            }
            $account->setStatus("open");
            $account->setCreatedAt(new \DateTime());
            $account->setUpdatedAt(new \DateTime());
            $manager->persist($account);
        }
       
        $manager->flush();
    }
}
// test: email: account1@authws.com, pw: account1, role: Admin et user