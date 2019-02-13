<?php

namespace App\Core;

/**
 * Class App
 *
 * @author Loïc Brisset
 */
class App
{
    private static $_instance;

    protected $auth;

    const SESSION_ALERT = 'flash_message';

    /**
     * @return App
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new App();
        }
        return self::$_instance;
    }

    public function auth() {
        if (is_null($this->auth)) {
            $this->auth = new Auth();
        }
        return $this->auth;
    }

    public function setAlert($data) {
        if (!is_array($data) || key($data) === 0) {
            $data['message'] = $data;
        }
        $_SESSION[self::SESSION_ALERT] = $data;
    }

    public function getAlert($key = '') {
        if (isset($_SESSION[self::SESSION_ALERT]) && !empty($_SESSION[self::SESSION_ALERT])) {
            $alerts = $_SESSION[self::SESSION_ALERT];
            unset($_SESSION[self::SESSION_ALERT]);
            if (!empty($key) && isset($alerts[$key])) {
                return $alerts[$key];
            }
            return $alerts;
        }
        return null;
    }
}
