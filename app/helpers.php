<?php

function dd(...$args) {
    ob_clean();
    http_response_code(500);

    array_map(function ($args) {
        var_dump($args);
    }, func_get_args());

    die(1);
}

/**
 * @param string $path
 * @return string
 */
function url($path = '/') {
    return getenv('DOMAIN') . getenv('RELATIVE_DIR') . $path;
}

/**
 * @param string $img
 * @return string
 */
function image(string $img) {
    return url() . 'assets/images/' . $img;
}

/**
 * @param string $img
 * @param bool $absolute
 * @return string
 */
function media(string $img, $absolute = false) {
    return ($absolute ? ROOT : url()) . 'data/media/' . $img;
}

/**
 * @param $type
 * @param $file
 * @param bool $cachebusting
 * @return string
 */
function resource($type, $file, $cachebusting = false) {
    return url() . 'assets/' . $type . '/' . $file . ($cachebusting ? '?v=' . filemtime(ROOT . "public/assets/$type/$file") : '');
}

/**
 * @param $route
 * @param ?array $var
 * @return string
 */
function route($route, $var = null) {

    $routes = include('routes.php');

    if (isset($routes[$route][1])) {
        $uri = trim($routes[$route][1], '/');
    } else {
        $uri = $route;
    }

    $params = '';
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            // On remplace les paramètres de la route par $var
            $uri = preg_replace('/{' . $k . ':?([a-zA-Z0-9-_.|+\\\]+)?}/', $v, $uri, -1, $count);
            if ($count > 0) {
                unset($var[$k]);
            }
        }
        if (!empty($var)) {
            $params = http_build_query($var);
        }
    } elseif (intval($var) > 0) {
        $uri = preg_replace('/\{id:(.*)\}/', $var, $uri);
    } else {
        $params = $var;
    }

    if (!empty($params) && strpos($uri, '?') === false && strpos($params, '?') === false) {
        $params = "?" . $params;
    }

    return url() . $uri . $params;
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param string|object $class
 * @return string
 */
function class_basename($class) {
    $class = is_object($class) ? get_class($class) : $class;

    return basename(str_replace('\\', '/', $class));
}

/**
 * @return bool
 */
function isDev() {
    return getenv('MODE') !== 'production';
}

/**
 * @param $message
 * @param array $params
 * @return string
 */
function jsonErrorFormat($message, $params = []) {
    $arr['error'] = ['message' => $message];
    if (!empty($params)) {
        $arr['error'] = array_merge($arr['error'], $params);
    }
    return json_encode($arr);
}

function jsonError($message, $params = []) {
    echo jsonErrorFormat($message, $params);
    exit();
}

function json($data) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($data);
    exit();
}
