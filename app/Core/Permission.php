<?php

namespace App\Core;

/**
 * Helper pour les permissions dans le back office.
 */
class Permission
{

    /**
     * @var Auth
     */
    var $auth;

    /**
     * Permission constructor.
     */
    public function __construct() {
        $this->auth = App::getInstance()->auth();
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return App::getInstance()->auth()->hasAnyRole([Auth::ROLE_ADMIN]);
    }

    /**
     * public static function canEditUser() {}
     */
}
