<?php

use App\Core\Str;

function dd(...$args) {
    ob_clean();
    http_response_code(500);

    array_map(function ($args) {
        var_dump($args);
    }, func_get_args());

    die(1);
}

/**
 * @param string $img
 * @return string
 */
function image(string $img) {
    return site_url() . 'assets/images/' . $img;
}

/**
 * @param string $img
 * @param bool $absolute
 * @return string
 */
function media(string $img, $absolute = false) {
    return ($absolute ? ROOT : site_url()) . 'data/media/' . $img;
}

/**
 * @return string
 */
function site_url() {
    return URL;
}

/**
 * @param $type
 * @param $file
 * @param bool $cachebusting
 * @return string
 */
function resource($type, $file, $cachebusting = false) {
    return site_url() . 'assets/' . $type . '/' . $file . ($cachebusting ? '?v=' . filemtime(ROOT . "assets/$type/$file") : '');
}

function route($route, $var = null) {
    $params = '';
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            $params .= "&$k=$v";
        }
    } elseif (intval($var) > 0) {
        $params = "&id=$var";
    } else {
        $params = $var;
    }
    return site_url() . 'index.php?p=' . $route . $params;
}

/**
 * Convert a value to studly caps case.
 *
 * @param  string $value
 * @return string
 */
function studly_case($value) {
    return Str::studly($value);
}

/**
 * Convert a value to camel case.
 *
 * @param  string $value
 * @return string
 */
function camel_case($value) {
    return Str::camel($value);
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @param  string|object $class
 * @return string
 */
function class_basename($class) {
    $class = is_object($class) ? get_class($class) : $class;

    return basename(str_replace('\\', '/', $class));
}

/**
 * Convert a value to title case.
 *
 * @param  string $value
 * @return string
 */
function title_case($value) {
    return Str::title($value);
}

/**
 * @return bool
 */
function isDev() {
    return defined('DEBUG') && DEBUG === true;
}

function quote($str) {
    return \App\Core\Sql::$instance->quote($str);
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

function getFileInfo($filename) {
    if (file_exists($filename)) {
        $fileExt = null;
        $expl = explode('.', $filename);
        if (count($expl) >= 2) {
            $info['extension'] = strtolower($expl[count($expl) - 1]);
            $info['filename'] = $filename;
            if ($info['extension'] == 'png' || $info['extension'] == 'jpg' || $info['extension'] == 'jpeg' || $info['extension'] == 'bmp' || $info['extension'] == 'gif') {
                $info['type'] = 'image';
            } else {
                $info['type'] = $info['extension'];
            }
            $fileSize = filesize($filename);
            if ($fileSize > 1000000000) {
                $info['pretty-size'] = round($fileSize / 1000000000, 2) . ' Go';
            } elseif ($fileSize > 1000000) {
                $info['pretty-size'] = round($fileSize / 1000000, 2) . ' Mo';
            } else {
                $info['pretty-size'] = round($fileSize / 1000, 2) . ' ko';
            }
            return $info;
        }
    }
    return null;
}

function str_lreplace($search, $replace, $subject) {
    $pos = strrpos($subject, $search);
    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}