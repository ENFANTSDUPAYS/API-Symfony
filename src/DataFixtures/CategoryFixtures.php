<?php

namespace App\DataFixtures;

use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new DateTimeImmutable();

        $data = [
            'MMORPG',
            'FPS (First Person Shooter)',
            'Action-Aventure',
            'Jeu de rôle (RPG)',
            'Stratégie en temps réel (RTS)',
            'Simulation',
            'Course automobile',
            'Jeu de sport',
            'Jeu de combat',
            'Sandbox / Monde ouvert',
            'Monde ouvert',
            'RPG',
            'Survie / Horreur',
            'Action',
            'Exploration',
            'FPS / Tir',
            'Fantasy',
        ];

        foreach ($data as $items) {
            $category = new Category();
            $category->setName($items);
            $category->setCreatedAt($now);
            $category->setUpdatedAt($now);

            $this->addReference($items, $category);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
