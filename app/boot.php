<?php

use App\Core\Sql;

define("ROOT", dirname(__DIR__) . '/');

require ROOT . 'conf.inc.php';
require ROOT . 'app/helpers.php';
require ROOT . 'app/roles.php';
require ROOT . 'vendor/autoload.php';

ini_set("log_errors", 1);
ini_set("error_log", \App\Core\Dbg::getFileName());

if (isDev()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();

/**
 * Connexion à la base de données
 */
try {
    Sql::connect();
} catch (Exception $e) {
    die("Database connection error");
}
