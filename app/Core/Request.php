<?php

namespace App\Core;


class Request
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const ERROR_NOT_AUTHORIZED = 'Vous n\'êtes pas autorisés à effectuer cette action.';
    const ERROR_SAVE = 'Erreur lors de l\'enregistrement';
    const ERROR_REQUEST = 'Erreur lors du traitement de la requête.';
    const ERROR_PHASE = 'L\'action n\'est pas disponible pour la phase en cours';

    public static function valuePost($k, $default = null, $noempty = false) {
        return isset($_POST[$k]) && (!$noempty || ($noempty && !empty(trim($_POST[$k])))) ? $_POST[$k] : $default;
    }

    public static function valueRequest($k, $default = null, $notempty = false) {
        return isset($_REQUEST[$k]) && (!$notempty || ($notempty && !empty(trim($_REQUEST[$k])))) ? $_REQUEST[$k] : $default;
    }

    public static function valueSession($k, $default = null, $notempty = false) {
        return isset($_SESSION[$k]) && (!$notempty || ($notempty && !empty(trim($_SESSION[$k])))) ? $_SESSION[$k] : $default;
    }

    /**
     * @param $k
     * @param mixed $object
     * @param bool $notempty
     * @return mixed
     */
    public static function valueRequestOrSession($k, $object = null, $notempty = false) {
        $value = self::valueRequest($k, null, $notempty);
        if (is_null($value)) {
            $value = self::valueSession($k, null, $notempty);
        }

        if (!is_null($object)) {
            if ($value instanceof $object) {
                return $value;
            }
            if (intval($value) > 0) {
                return new $object(intval($value));
            }
            return null;
        }

        return $value;
    }

    public static function filterSelect($name, $option) {
        if (isset($_GET[$name])) {
            if ($_GET[$name] == $option) {
                return 'selected="selected"';
            }
        } else {
            if ($option == null || $option == -1) {
                return 'selected="selected"';
            }
        }
        return '';
    }
}