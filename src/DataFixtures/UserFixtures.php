<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('adrien.leclere@orange.fr')
            ->setFirstName('Adrien')
            ->setLastName('Leclère')
            ->setRoles(['ROLE_ADMIN'])
            ->setSubscriptionToNewsletter(true)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'adminbts');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        $manager->flush();
    }
}