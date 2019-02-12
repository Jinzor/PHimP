<?php
/**
 * Gestion des permissions
 */

use App\Core\App;
use App\Core\Auth;

function isAdmin() {
    return App::getInstance()->auth()->hasRole(Auth::ROLE_ADMIN);
}

function peutAccederAdmin(Auth $auth) {
    return $auth->hasAnyRole([
        Auth::ROLE_ADMIN,
    ]);
}
