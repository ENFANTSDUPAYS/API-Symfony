<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\VideoGame;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
         $data = [
            [
                "title" => "The Witcher 3: Wild Hunt",
                "description" => "Un RPG en monde ouvert dans un univers de fantasy où le sorceleur Geralt de Riv chasse des monstres tout en suivant sa fille adoptive Ciri.",
                "release_date" => new DateTime("2015-05-19"),
                "editor_ref" => "CD Projekt RED",
                "category_id" => ["Jeu de rôle (RPG)", "Action-Aventure"],
            ],
            [
                "title" => "Assassin’s Creed Odyssey",
                "description" => "Un jeu d’action-aventure se déroulant dans la Grèce antique, où vous incarnez un mercenaire pendant la guerre du Péloponnèse.",
                "release_date" => new DateTime("2018-10-05"),
                "editor_ref" => "Ubisoft",
                "category_id" => ["Action-Aventure", "Monde ouvert", "RPG"],
            ],
            [
                "title" => "Elden Ring",
                "description" => "Un action-RPG épique développé par FromSoftware et co-écrit par George R.R. Martin, situé dans un vaste monde ouvert.",
                "release_date" => new DateTime("2022-02-25"),
                "editor_ref" => "Bandai Namco Entertainment",
                "category_id" => ["Jeu de rôle (RPG)", "Action-Aventure"],
            ],
            [
                "title" => "Resident Evil 4 Remake",
                "description" => "Une réinterprétation moderne du célèbre jeu de survie-horreur, avec Leon S. Kennedy en mission pour sauver la fille du président.",
                "release_date" => new DateTime("2023-03-24"),
                "editor_ref" => "Capcom",
                "category_id" => ["Survie / Horreur", "Action"],
            ],
            [
                "title" => "The Legend of Zelda: Tears of the Kingdom",
                "description" => "Link explore un Hyrule en ruine et le ciel pour sauver le royaume dans cette suite du célèbre Breath of the Wild.",
                "release_date" => new DateTime("2023-05-12"),
                "editor_ref" => "Nintendo",
                "category_id" => ["Action-Aventure", "Monde ouvert", "Exploration"],
            ],
            [
                "title" => "Grand Theft Auto V",
                "description" => "Un jeu d’action-aventure en monde ouvert se déroulant à Los Santos, mêlant crime, satire sociale et liberté totale.",
                "release_date" => new DateTime("2013-09-17"),
                "editor_ref" => "Rockstar Games",
                "category_id" => ["Action-Aventure", "Sandbox / Monde ouvert"],
            ],
            [
                "title" => "Final Fantasy VII Remake",
                "description" => "Une réimagination moderne du classique JRPG culte, suivant Cloud Strife et AVALANCHE dans leur lutte contre la Shinra.",
                "release_date" => new DateTime("2020-04-10"),
                "editor_ref" => "Square Enix",
                "category_id" => ["Jeu de rôle (RPG)", "Action"],
            ],
            [
                "title" => "FIFA 23",
                "description" => "Le jeu de football emblématique d’EA Sports, dernier à porter le nom FIFA avant le changement de licence.",
                "release_date" => new DateTime("2022-09-30"),
                "editor_ref" => "Electronic Arts (EA)",
                "category_id" => ["Jeu de sport", "Simulation"],
            ],
            [
                "title" => "Call of Duty: Modern Warfare III",
                "description" => "Un FPS nerveux et spectaculaire où les forces spéciales affrontent un réseau terroriste mondial.",
                "release_date" => new DateTime("2023-11-10"),
                "editor_ref" => "Activision Blizzard",
                "category_id" => ["FPS / Tir", "Action"],
            ],
            [
                "title" => "The Elder Scrolls V: Skyrim",
                "description" => "Un jeu de rôle légendaire où le joueur incarne l’Enfant de Dragon, libre d’explorer le vaste monde de Bordeciel.",
                "release_date" => new DateTime("2011-11-11"),
                "editor_ref" => "Bethesda Softworks",
                "category_id" => ["Jeu de rôle (RPG)", "Monde ouvert", "Fantasy"],
            ],
        ];

        $now = new DateTimeImmutable();

        foreach ($data as $items) {
            $game = new VideoGame();
            $game->setTitle($items["title"]);
            $game->setDescription($items["description"]);
            $game->setReleaseDate($items["release_date"]);
            $game->setCreatedAt($now);
            $game->setUpdatedAt($now);

            //RÉFÉRENCE AVEC EDITOR
            $game->setEditor($this->getReference($items["editor_ref"], Editor::class));

            //REFERENCE A PLUSIEURS CATEGORIE
            foreach ($items["category_id"] as $categoryName) {
                $game->addCategory($this->getReference($categoryName, Category::class));
            }

            $manager->persist($game);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EditorFixtures::class,
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}
