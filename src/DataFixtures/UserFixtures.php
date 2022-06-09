<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($nbrUser = 1; $nbrUser < 3; $nbrUser++) {
            $user = new User();
            $user->setUsername('user' . $nbrUser)
                ->setEmail('user' . $nbrUser  . '@hotmail.com')
                ->setPassword($this->encoder->hashPassword($user, 'user' . $nbrUser))  //user1, user2, ...
                ->setRoles(['ROLE_USER']);

            $manager->persist($user);

            // we save the reference of the user
            $this->addReference('user' . $nbrUser, $user);
        }

        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@hotmail.com')
            ->setPassword($this->encoder->hashPassword($admin, 'admin'))
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $this->addReference('admin', $admin);


        $manager->flush();
    }
}