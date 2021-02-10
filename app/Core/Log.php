<?php

namespace App\Core;

class Log
{
    /**
     * Message type notice
     */
    const L_NOTICE = "0;37";
    /**
     * Message type warning
     */
    const L_WARNING = "0;35";
    /**
     * Message type erreur
     */
    const L_ERROR = "1;33";
    /**
     * Message type critique
     */
    const L_CRITICAL = "0;31";
    /**
     * Message success
     */
    const L_SUCCESS = "1;32";
    /**
     * Message debug
     */
    const L_DEBUG = '1;90';

    /** @var bool */
    private static $useDaysAsFile = false;

    /**
     * @param string $msg
     * @param int|string $level
     * @return bool
     */
    public static function logs($msg, $level = LOG_NOTICE) {

        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }

        if (is_object($msg) && !method_exists($msg, '__tostring')) {
            $msg = Str::displayRawJson($msg);
        }

        if (is_int($level)) {
            $level = self::colorize($level);
        }
        $ms = substr(microtime(true) - time(), 2, 2);
        $content = "\e[1;90m" . date('H:i:s') . ".$ms | " . (App::getInstance()->auth()->user ? App::getInstance()->auth()->user->id : '-') . " | \e[" . $level . "m" . $msg . "\e[0m\n";

        return file_put_contents(self::getFileName(), $content, FILE_APPEND);
    }

    private static function colorize(int $level) {
        $colors = [
            LOG_DEBUG   => self::L_DEBUG,
            LOG_INFO    => self::L_SUCCESS,
            LOG_NOTICE  => self::L_NOTICE,
            LOG_WARNING => self::L_WARNING,
            LOG_ERR     => self::L_ERROR,
            LOG_CRIT    => self::L_CRITICAL,
        ];
        return (isset($colors[$level]) ? $colors[$level] : $level);
    }

    static public function info($data) {
        self::logs($data, LOG_NOTICE);
    }

    static public function debug($data) {
        self::logs($data, LOG_DEBUG);
    }

    static public function warning($data) {
        self::logs($data, LOG_WARNING);
    }

    static public function error($data) {
        self::logs($data, LOG_ERR);
    }

    static public function critical($data) {
        self::logs($data, LOG_CRIT);
    }

    static public function success($data) {
        self::logs($data, LOG_INFO);
    }

    /**
     * Path de stockage logs
     *
     * @return string
     */
    static public function getPath() {
        $filePath = [ROOT . 'data/logs/'];
        if (self::$useDaysAsFile) {
            $filePath[1] = $filePath[0] . date('Y') . '/';
            $filePath[2] = $filePath[1] . date('m') . '/';
        }

        foreach ($filePath as $fp) {
            if (!file_exists($fp)) {
                mkdir($fp);
                chmod($fp, 0774);
            }
        }

        return end($filePath);
    }

    /**
     * @return string
     */
    public static function getFileName() {
        $filename = 'debug.log';
        if (self::$useDaysAsFile) {
            $filename = date('d') . '.log';
        }
        return self::getPath() . $filename;
    }

}
