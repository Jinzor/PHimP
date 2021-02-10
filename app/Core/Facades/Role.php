<?php

namespace App\Core\Facades;

use App\Core\Permission;

/**
 * Facade pour les permissions dans le back office.
 *
 * @see Permission
 *
 * @method static isAdmin()
 */
abstract class Role
{
    static function __callStatic($name, $arguments) {
        $class = Permission::class;
        return call_user_func_array([new $class, $name], $arguments);
    }
}
