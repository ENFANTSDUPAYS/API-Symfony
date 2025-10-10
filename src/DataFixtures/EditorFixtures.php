<?php

namespace App\DataFixtures;

use App\Entity\Editor;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EditorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new DateTimeImmutable();
        $data = [
             [
                'name' => 'Ubisoft',
                'country' => 'France',
            ],
            [
                'name' => 'Electronic Arts (EA)',
                'country' => 'États-Unis',
            ],
            [
                'name' => 'Square Enix',
                'country' => 'Japon',
            ],
            [
                'name' => 'Capcom',
                'country' => 'Japon',
            ],
            [
                'name' => 'Bethesda Softworks',
                'country' => 'États-Unis',
            ],
            [
                'name' => 'Activision Blizzard',
                'country' => 'États-Unis',
            ],
            [
                'name' => 'Nintendo',
                'country' => 'Japon',
            ],
            [
                'name' => 'Bandai Namco Entertainment',
                'country' => 'Japon',
            ],
            [
                'name' => 'Rockstar Games',
                'country' => 'Royaume-Uni',
            ],
            [
                'name' => 'CD Projekt RED',
                'country' => 'Pologne',
            ],
        ];

        foreach($data as $items) {
            $editor = new Editor();
            $editor->setName($items['name']);
            $editor->setCountry($items['country']);
            $editor->setCreatedAt($now);
            $editor->setUpdatedAt($now);

            $this->addReference($items['name'], $editor);

            $manager->persist($editor);
        }

        $manager->flush();
    }
}
