<?php

namespace App\Core;


class Request
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const ERROR_NOT_AUTHORIZED = 'Vous n\'êtes pas autorisés à effectuer cette action.';
    const ERROR_SAVE = 'Erreur lors de l\'enregistrement';
    const ERROR_REQUEST = 'Erreur lors du traitement de la requête.';

    public static function valuePost($k, $default = null, $noempty = false) {
        return isset($_POST[$k]) && (!$noempty || ($noempty && !empty(trim($_POST[$k])))) ? $_POST[$k] : $default;
    }

    public static function valueRequest($k, $default = null, $notempty = false) {
        return isset($_REQUEST[$k]) && (!$notempty || ($notempty && !empty(trim($_REQUEST[$k])))) ? $_REQUEST[$k] : $default;
    }

    public static function valueSession($k, $default = null, $notempty = false) {
        return isset($_SESSION[$k]) && (!$notempty || ($notempty && !empty(trim($_SESSION[$k])))) ? $_SESSION[$k] : $default;
    }
}