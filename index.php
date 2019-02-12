<?php
require 'app/boot.php';

/**
 * Routes
 */
$page = null;
$vars = [];
foreach ($_GET as $k => $v) {
    if ($k == 'p') {
        $page = $v;
    } else {
        $vars[$k] = $v;
    }
}

if (is_null($page)) {
    $page = 'admin.index'; // TODO set default page
}

try {
    $p = explode('.', $page);
    if (isset($p[1])) {
        $basename = $p[0];
        $method = $p[1];
    } else {
        $basename = '';
        $method = $p[0];
    }
    $controller = '\App\Controllers\\' . ucfirst($basename) . 'Controller';

    if (class_exists($controller)) {
        /**
         * @var \App\Controllers\Controller $controller
         */
        $controller = new $controller();

        if (method_exists($controller, $method)) {
            if (!empty($vars)) {
                call_user_func_array([$controller, $method], $vars);
            } else {
                $controller->$method();
            }
            exit();
        }
    }

} catch (\Exception $e) {
    //
}

(new \App\Controllers\Controller())->error_404();
exit();

