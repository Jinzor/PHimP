<?php

use App\Core\App;
use App\Core\Log;

define("ROOT", dirname(__DIR__) . '/');

/**
 * Chargement des dépendances composer + autoload
 */
require ROOT . 'vendor/autoload.php';

/**
 * Chargement du fichier .env
 */
$dotenv = Dotenv\Dotenv::createImmutable([ROOT, dirname(ROOT)]);
try {
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die(".env file must be set");
}

/**
 * Fichier de fonctions utiles
 */
require ROOT . 'app/helpers.php';

ini_set("log_errors", 1);
ini_set("error_log", Log::getFileName());
ini_set('memory_limit', '256M');

if (isDev()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (!is_writable(ROOT . 'data')) {
    die('Error Data folder must be writable.');
}

if (!file_exists(ROOT . 'data/cache/')) {
    mkdir(ROOT . 'data/cache/');
}

/**
 * Connexion à la base de données
 */
$sql = App::getInstance()->db();
