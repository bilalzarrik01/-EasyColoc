# EasyColoc

Application web pour gérer une colocation : membres, catégories/dépenses, calcul des soldes, et règlements (“qui paie qui”).

## Fonctionnalités (aperçu)

- Création/gestion de colocations
- Invitations (par e-mail) et gestion des membres
- Catégories + dépenses
- Calcul des règlements et marquage “payé”
- Espace admin global (le 1er utilisateur inscrit devient admin global)

## Démarrage (local)

Pré-requis : PHP 8.2+, Composer, Node.js + npm, et une base MySQL (par défaut via `.env`).

1. Installer les dépendances

   - `composer install`
   - `npm install`

2. Configurer l’environnement

   - `copy .env.example .env`
   - Mettre à jour les variables MySQL dans `.env`
   - `php artisan key:generate`

3. Lancer les migrations + build front

   - `php artisan migrate`
   - `npm run build`

4. Démarrer en mode dev

   - `composer dev`

## Tests

- `composer test`

## Documentation

- Diagrammes : `docs/README.md`
