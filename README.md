# Documentation d'Installation - Projet Symfony API

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Démarrage](#démarrage)
5. [Commandes utiles](#commandes-utiles)
6. [Authentification JWT](#authentification-jwt)
7. [Endpoints de l'API](#endpoints-de-lapi)
8. [Tests](#tests)
9. [Dépannage](#dépannage)
10. [Changelog](#changelog)

---

## 🔧 Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants sur votre machine :

### Obligatoire
- **Docker Desktop** (version 20.10 ou supérieure)
  - [Télécharger Docker Desktop pour Mac](https://www.docker.com/products/docker-desktop/)
- **Docker Compose** (version 2.0 ou supérieure - inclus dans Docker Desktop)
- **Git** (pour cloner le projet)

### Optionnel (pour développement local sans Docker)
- **PHP 8.2** ou supérieur
- **Composer** (version 2.5 ou supérieure)
- **PostgreSQL 16** (ou MySQL 8.0)
- **Node.js** et **npm** (pour les assets front-end)

---

## 📦 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repository>
```

### 2. Se positionner dans le répertoire API

```bash
cd lp-jdr-api
```

### 3. Copier le fichier d'environnement

```bash
cp .env .env.local
```

### 4. Éditer les variables d'environnement

Ouvrez le fichier `.env.local` et configurez les variables selon votre environnement :

```dotenv
# Environnement
APP_ENV=dev
APP_SECRET=VotreSecretAleatoireTresLong

# Base de données MySQL (si vous utilisez docker-compose.yml)
DATABASE_URL="mysql://symfony:symfony@database:3306/symfony_db?serverVersion=8.0.32&charset=utf8mb4"

# CORS Configuration
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

# Mailer (configurer selon vos besoins)
MAILER_DSN=null://null

# Messenger
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

---

## ⚙️ Configuration

### Installation avec Docker

#### 1. Construire et démarrer les conteneurs

```bash
docker compose up -d --build
```

Cette commande va :
- Construire l'image PHP avec toutes les extensions nécessaires
- Démarrer le serveur Nginx sur le port 8080
- Démarrer le serveur MySQL
- Créer les volumes pour la persistance des données

#### 2. Installer les dépendances PHP

```bash
docker compose exec php composer install
```

#### 3. Créer la base de données

```bash
docker compose exec php php bin/console doctrine:database:create
```

#### 4. Exécuter les migrations

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

#### 5. Installer les assets

```bash
docker compose exec php php bin/console asset-map:compile
docker compose exec php php bin/console importmap:install
```
---

## 🚀 Démarrage

### Avec Docker

L'application est accessible à l'adresse : **http://localhost:8080**

Pour vérifier que tout fonctionne :

```bash
# Vérifier le statut des conteneurs
docker compose ps

# Voir les logs
docker compose logs -f
```

---

## 🔐 Authentification JWT

### 1. Générer les clés JWT

#### Avec Docker :

```bash
docker compose exec php php bin/console lexik:jwt:generate-keypair
```

#### Sans Docker :

```bash
php bin/console lexik:jwt:generate-keypair
```

Cette commande va créer :
- `config/jwt/private.pem` : clé privée
- `config/jwt/public.pem` : clé publique

### 2. Vérifier la configuration JWT

Assurez-vous que les chemins dans `.env.local` sont corrects :

```dotenv
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=VotrePassphraseSecurisee
```

### 3. Permissions des clés (si nécessaire)

```bash
# Avec Docker
docker compose exec php chmod 644 config/jwt/private.pem
docker compose exec php chmod 644 config/jwt/public.pem

# Sans Docker
chmod 644 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

---

## 🛠️ Commandes utiles

### Gestion de la base de données

```bash
# Avec Docker (ajouter 'docker compose exec php' avant chaque commande)

# Créer la base de données
php bin/console doctrine:database:create

# Supprimer la base de données
php bin/console doctrine:database:drop --force

# Créer une nouvelle migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Charger des fixtures (données de test)
php bin/console doctrine:fixtures:load
```

### Gestion du cache

```bash
# Vider le cache
php bin/console cache:clear

# Warmup du cache
php bin/console cache:warmup
```

### Gestion des entités

```bash
# Créer une nouvelle entité
php bin/console make:entity

# Créer un CRUD complet
php bin/console make:crud

# Valider le schéma de la base de données
php bin/console doctrine:schema:validate
```

### Gestion des utilisateurs (si applicable)

```bash
# Créer un utilisateur
php bin/console make:user

# Hasher un mot de passe
php bin/console security:hash-password
```

### Debug

```bash
# Voir toutes les routes
php bin/console debug:router

# Voir les services disponibles
php bin/console debug:container

# Voir la configuration
php bin/console debug:config

# Voir les events
php bin/console debug:event-dispatcher
```

---

## 🌐 Endpoints de l'API

Tous les endpoints sont préfixés par `/api`. Le frontend React appelle `http://localhost:8080/api`.

### Authentification (public)

| Méthode | Endpoint | Description | Corps |
|---------|----------|-------------|-------|
| `POST` | `/api/login_check` | Connexion — retourne un JWT | `{ "email": "...", "password": "..." }` |
| `POST` | `/api/register` | Inscription | `{ "email": "...", "password": "...", "username": "..." }` |

**Réponse login :**
```json
{ "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..." }
```

---

### Personnages (JWT requis)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/api/characters` | Liste les personnages de l'utilisateur connecté |
| `GET` | `/api/characters/{id}` | Détail d'un personnage |
| `POST` | `/api/characters` | Crée un personnage |
| `DELETE` | `/api/characters/{id}` | Supprime un personnage |

**Corps de création :**
```json
{
  "firstName": "Aragorn",
  "lastName": "Elessar",
  "class": "Guerrier",
  "race": "Humain",
  "strength": 16,
  "dexterity": 12,
  "constitution": 14,
  "intelligence": 10,
  "wisdom": 11,
  "charisma": 13
}
```

**Réponse :**
```json
{
  "id": 1,
  "firstName": "Aragorn",
  "lastName": "Elessar",
  "class": "Guerrier",
  "race": "Humain",
  "strength": 16,
  "dexterity": 12,
  "constitution": 14,
  "intelligence": 10,
  "wisdom": 11,
  "charisma": 13
}
```

---

### Parties (JWT requis)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/games` | Démarre une nouvelle partie |
| `POST` | `/api/games/{id}/message` | Envoie un message au maître du jeu (IA) |
| `POST` | `/api/games/{id}/roll` | Lance un dé |

**Démarrer une partie :**
```json
// Corps
{ "characterId": 1 }

// Réponse
{ "id": 42, "introduction": "Bienvenue, Aragorn Elessar ! Vous êtes un(e) Guerrier Humain..." }
```

**Envoyer un message :**
```json
// Corps
{ "message": "J'avance prudemment vers la porte." }

// Réponse
{ "response": "Vous approchez de la porte massive en chêne renforcé de fer..." }
```

**Lancer un dé :**
```json
// Corps
{ "diceType": 20 }

// Réponse
{ "result": 17, "diceType": 20 }
```

---

### Variable d'environnement requise — OpenAI

Le endpoint `/api/games/{id}/message` utilise l'API OpenAI (modèle `gpt-4o`). Ajoutez la clé dans `.env.local` :

```dotenv
OPENAI_API_KEY=sk-proj-...
```

---

## 🧪 Tests

### Exécuter tous les tests

```bash
# Avec Docker
docker compose exec php php bin/phpunit

# Sans Docker
php bin/phpunit
```

### Exécuter un test spécifique

```bash
php bin/phpunit tests/Controller/VotreTestController.php
```

### Tests avec couverture de code

```bash
php bin/phpunit --coverage-html coverage/
```

---

## 🐛 Dépannage

### Problème : Les conteneurs Docker ne démarrent pas

**Solution 1 :** Vérifier que les ports ne sont pas déjà utilisés

```bash
# Vérifier le port 8080
lsof -i :8080

# Vérifier le port 3306 (MySQL)
lsof -i :3306

# Vérifier le port 5432 (PostgreSQL)
lsof -i :5432
```

**Solution 2 :** Reconstruire les conteneurs

```bash
docker compose down -v
docker compose up -d --build --force-recreate
```

### Problème : Erreur de connexion à la base de données

**Solution :** Vérifier que le nom du service correspond dans `DATABASE_URL`

Pour Docker, utilisez le nom du service (ex: `database`) au lieu de `127.0.0.1` :

```dotenv
# ✅ Correct avec Docker
DATABASE_URL="postgresql://app:!ChangeMe!@database:5432/app"

# ❌ Incorrect avec Docker
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app"
```

### Problème : Permission denied sur les fichiers JWT

**Solution :**

```bash
# Avec Docker
docker compose exec php chown -R www-data:www-data config/jwt
docker compose exec php chmod -R 644 config/jwt/*.pem

# Sans Docker
sudo chown -R $USER:$USER config/jwt
chmod -R 644 config/jwt/*.pem
```

### Problème : Erreur 500 sur l'application

**Solution :** Vérifier les logs

```bash
# Avec Docker
docker compose logs -f php
docker compose logs -f nginx

# Sans Docker (ou avec Docker)
tail -f var/log/dev.log
```

### Problème : Les migrations ne fonctionnent pas

**Solution :**

```bash
# Réinitialiser la base de données
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Ou forcer l'exécution d'une migration spécifique
php bin/console doctrine:migrations:execute --up Version20XXXXXX
```

### Problème : Composer install échoue

**Solution :**

```bash
# Vider le cache Composer
composer clear-cache

# Réinstaller les dépendances
rm -rf vendor/
composer install --no-cache
```

### Problème : Les assets ne se chargent pas

**Solution :**

```bash
# Réinstaller les assets
php bin/console asset-map:compile
php bin/console importmap:install
php bin/console cache:clear
```

---

## 📖 Documentation supplémentaire

### Liens utiles

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation API Platform](https://api-platform.com/docs/)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Documentation JWT Bundle](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/main/Resources/doc/index.rst)
- [Documentation Docker](https://docs.docker.com/)

### Structure du projet

```
lp/api/
├── assets/              # Fichiers JavaScript et CSS
├── bin/                 # Exécutables (console, phpunit)
├── config/              # Configuration de l'application
│   ├── packages/        # Configuration des bundles
│   └── routes/          # Configuration des routes
├── docker/              # Configuration Docker
│   └── nginx/           # Configuration Nginx
├── migrations/          # Migrations de base de données
├── public/              # Point d'entrée public (index.php)
├── src/                 # Code source de l'application
│   ├── ApiResource/     # Ressources API Platform
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine
│   └── Repository/      # Repositories Doctrine
├── templates/           # Templates Twig
├── tests/               # Tests unitaires et fonctionnels
├── translations/        # Fichiers de traduction
├── var/                 # Fichiers temporaires (cache, logs)
├── vendor/              # Dépendances Composer
├── .env                 # Variables d'environnement (par défaut)
├── composer.json        # Dépendances PHP
├── docker-compose.yml   # Configuration Docker Compose
└── Dockerfile           # Image Docker PHP
```

---

## 🤝 Contribution

Pour contribuer au projet :

1. Fork le projet
2. Créer une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📝 Licence

Ce projet est sous licence propriétaire.

---

## 👥 Support

Pour toute question ou problème, veuillez contacter l'équipe de développement.

**Version de Symfony :** 7.4  
**Version de PHP :** 8.2+  
**Version de API Platform :** 4.2+

---

## 📜 Changelog

### v1.1.0 — 2026-04-10

#### Nouveaux endpoints

- **`POST /api/register`** — Inscription d'un nouvel utilisateur. Accepte `email`, `password` et `username` (mappé sur le champ `name` de l'entité `User`). Route publique (pas de JWT requis).
- **`GET /api/characters`** — Liste les personnages appartenant à l'utilisateur authentifié.
- **`GET /api/characters/{id}`** — Retourne le détail d'un personnage (vérifie que le personnage appartient bien à l'utilisateur).
- **`POST /api/characters`** — Crée un personnage lié à l'utilisateur connecté.
- **`DELETE /api/characters/{id}`** — Supprime un personnage (vérifie le propriétaire).
- **`POST /api/games`** — Démarre une nouvelle partie pour un personnage donné. Génère une introduction narrative et persiste l'historique en base.
- **`POST /api/games/{id}/message`** — Envoie un message au maître du jeu. Appelle l'API OpenAI (`gpt-4o`) avec le contexte du personnage et l'historique complet de la partie. Sauvegarde la réponse dans l'historique.
- **`POST /api/games/{id}/roll`** — Lance un dé de `diceType` faces (défaut : d20). Retourne le résultat.

#### Nouvelles entités

- **`Character`** — Personnage joueur avec `firstName`, `lastName`, `class`, `race` et les 6 statistiques D&D (`strength`, `dexterity`, `constitution`, `intelligence`, `wisdom`, `charisma`). Lié à un `User` par une relation `ManyToOne`.
- **`Game`** — Partie en cours. Liée à un `Character`, stocke le statut (`active`/`ended`) et l'historique des messages en JSON.

#### Migration à exécuter

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

La migration `Version20260410140000` crée les tables `character` et `game` avec leurs clés étrangères.

#### Modifications de configuration

- **`security.yaml`** — Le `json_login` est configuré avec `username_path: email` pour que le champ `email` du JSON soit utilisé à la place du champ `username` par défaut de Symfony.
- **`security.yaml`** — La route publique est désormais `^/api/login_check` (au lieu de `^/api/login`).

#### Modification du frontend React

Le fichier `react/src/services/api.js` a été mis à jour en conséquence :

| Avant | Après |
|-------|-------|
| `baseURL: 'http://localhost:8080'` | `baseURL: 'http://localhost:8080/api'` |
| `POST /login` | `POST /login_check` |
| `POST /chat` | `POST /games/${gameId}/message` |

---

## 🎯 Prochaines étapes après l'installation

1. ✅ Vérifier que l'API fonctionne : http://localhost:8080/api
2. ✅ Générer les clés JWT pour l'authentification
3. ✅ Créer vos premières entités avec `make:entity`
4. ✅ Configurer les fixtures pour les données de test
5. ✅ Configurer le CORS selon vos besoins
6. ✅ Mettre en place les tests unitaires
7. ✅ Documenter vos endpoints API

