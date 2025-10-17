# Jeu vidéo API
Ce projet a été réalisé dans un cadre scolaire, avec pour objectif de découvrir la création d'APIs avec Symfony sans recourir à API Platform. 
Il s'agit d'une API from Scratch pour mieux comprendre les mécanismes de l'API. Grâce à l'envoie de donner via Postman, vous pouvez vous identifiez afin
d'accéder au crud, puis envoyer des données.

# Description

Cette API permet de gérer une base de données de jeux vidéo avec les fonctionnalités suivantes :

- Création et gestion de jeux vidéo
- Gestion des éditeurs
- Gestion des catégories
- Système d'utilisateurs
- Envoi automatique de newsletters pour les prochaines sorties

# Prérequis
- PHP 8.3+
- Composer
- PostMan
- Mysql
- Symfony Cli

# Installation

### Cloner le projet
```
https://github.com/ENFANTSDUPAYS/API-Symfony.git
```
### Installation package
```
- Composer install
```
### Configurer DATABASE_URL
```
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/nom_base_données?serverVersion=8.0.32&charset=utf8mb4"
```
### Initialisation de la base de donnée
```
symfony console doctrine:database:create
php bin/console doctrine:schema:update --force
symfony console doctrine:fixtures:load
```
### Démarrage du serveur
```
symfony serve
```

# Problème
Si vous rencontrez un souci, contactez moi à l'adresse "proadrien.911@gmail.com"
