# Contribuer à EasyColoc

Merci pour votre intérêt !

## Prérequis

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL (par défaut via `.env`)

## Installation / exécution

- `composer install`
- `npm install`
- `copy .env.example .env` puis configurer la DB
- `php artisan key:generate`
- `php artisan migrate`
- `composer dev`

## Qualité

- Formatage : `composer format`
- Vérification format : `composer lint`
- Tests : `composer test`

## Pull requests

- Une PR = un sujet (petit et clair)
- Décrire le changement + comment tester
- Éviter de committer des secrets (`.env`, clés, tokens)
