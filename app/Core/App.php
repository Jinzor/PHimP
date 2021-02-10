<?php

namespace App\Core;

use App\Core\Database\DatabaseConnector;

/**
 * Class App
 *
 * @author Loïc Brisset
 */
class App
{
    /** @var App */
    private static $_instance;

    /** @var Auth */
    private $auth;

    /** @var DatabaseConnector */
    private $database;

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

    /**
     * @return Auth
     */
    public function auth() {
        if (is_null($this->auth)) {
            $this->auth = new Auth();
        }
        return $this->auth;
    }

    /**
     * @return DatabaseConnector
     */
    public function db() {
        if (is_null($this->database)) {
            $this->database = new DatabaseConnector(getenv('MYSQL_DB'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'), getenv('MYSQL_HOST'), isDev());
        }
        return $this->database;
    }

    /**
     * @param $data
     */
    public function setAlert($data) {
        if (!is_array($data) || key($data) === 0) {
            $data['message'] = $data;
        }
        $_SESSION[self::SESSION_ALERT] = $data;
    }

    /**
     * @param string $key
     * @return mixed
     */
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
