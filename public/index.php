<?php

use App\Controllers\Controller;
use App\Core\App;
use App\Core\Log;

require '../app/boot.php';

session_start();

$routes = include('../app/routes.php');

$user = App::getInstance()->auth()->user;

$dispatcher = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $r) use ($routes) {
    $dir = getenv('RELATIVE_DIR') ?: '';
    foreach ($routes as $routeId => $rt) {
        $arr[] = $dir . $rt[1];
        $r->addRoute($rt[0], $dir . $rt[1], [$rt[2], $routeId]);
    }
}, [
    'cacheFile'     => ROOT . 'data/cache/routes.cache',
    'cacheDisabled' => isDev(),
]);

// Récupération de la methode HTTP et l'URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        Log::error('Route not found ' . $uri);
        (new Controller())->error_404();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        (new Controller())->error_403();
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $routeId = $handler[1];
        [$controller, $method] = $handler[0];
        if (method_exists($controller, $method)) {
            // Appel de la méthode depuis le Controller
            call_user_func_array([new $controller, $method], $vars);
        } else {
            Log::error("Method $method not found");
            (new Controller())->error_404();
        }
        break;
    default:
        (new Controller())->error_404();
        break;
}
