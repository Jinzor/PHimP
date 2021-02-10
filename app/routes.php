<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;

return [
    'dashboard' => ['GET', '/', [AdminController::class, 'dashboard']],

    'auth.login'                    => ['GET', '/login', [AuthController::class, 'pageLogin']],
    'auth.logout'                   => [['POST', 'GET'], '/logout', [AuthController::class, 'logout']],
    'auth.auth'                     => ['POST', '/auth', [AuthController::class, 'auth']],
    'auth.reauth'                   => [['POST', 'GET'], '/services/reauth', [AuthController::class, 'reauth']],
    'auth.password-recover-request' => ['POST', '/password-recover', [AuthController::class, 'passwordRecoverRequest']],
    'auth.password-creation'        => ['GET', '/sign-in-password', [AuthController::class, 'passwordCreationPage']],
    'auth.password-recover'         => ['GET', '/password-recover', [AuthController::class, 'passwordRecoverPage']],
    'auth.password-post'            => ['POST', '/set-password', [AuthController::class, 'passwordSet']],
];