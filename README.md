# PHimP

Simple PHP template using MVC architecture.

### Installation

Clone the repo and run

```
composer install
```

```
npm install
```

### Fichiers de configuration

Créer un fichier `.env` à la racine du projet.

```
cp .env.example ../.env
```

### JS & SCSS

To generate js (babel) use

```
npm run build 
```

To generate css use

```
scss public/assets/scss/style.scss public/assets/css/style.css --style compressed
```

## Routes

See https://github.com/nikic/FastRoute

Les routes sont définies dans **/src/routes.php** sous forme d'un array PHP. Les appels portent sur **/public/index.php** qui redispatche vers *Controller->method()*.

Le format est 'key' => ['GET / POST', 'url', ['NomDuController', 'nomDeLaMethode']]

> Note : La clée 'key' peut être utilisé avec la fonction *route()* dans **/src/helpers.php** permettant de retourner l'url associée.

Exemple :
```
 'dossier.index' => ['GET', '/dossier/home', ['MyController', 'getIndex']],
```

## Migrations / Seeds

Utilise [phinx.php](https://book.cakephp.org/3.0/en/phinx.html).
Les migrations sont définies dans **/data/migrations** et les seeds dans **/data/seeds**.

Créer une nouvelle route
```
php vendor/bin/phinx create MyNewMigration
```

Exécuter la migration
```
php vendor/bin/phinx migrate
```

Exécuter un seed
```
php vendor/bin/phinx seed:run
```
