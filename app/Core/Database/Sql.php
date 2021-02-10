<?php

namespace App\Core\Database;

use App\Core\App;
use App\Core\Log;
use DateTime;
use Exception;
use PDO;
use PDOStatement;

abstract class Sql
{

    /**
     * @var array
     */
    static $queryDebug = [];

    /**
     * @var int
     */
    static $stampConnect = 0;

    /**
     * @var bool
     */
    static $sqlDebuging = false;

    /**
     * @return DatabaseConnector
     */
    public static function db() {
        return App::getInstance()->db();
    }

    /**
     * @param $statement
     * @param $className
     * @return array
     *
     * Attention ne pas utiliser pour de trop grosses listes de données
     */
    public static function getObjectList($statement, $className) {
        $result = self::db()->query($statement);
        if ($result) {
            return $result->fetchAll(PDO::FETCH_CLASS, $className);
        }
        return [];
    }

    /**
     * Requete sql standard
     *
     * @param string $statement Requete SQL
     * @return PDOStatement
     */
    public static function query(string $statement) {
        return self::db()->query($statement);
    }

    /**
     * requete insert auto
     *
     * @param string $table Nom de la table
     * @param array $data Array colonne => valeur
     * @return int|boolean
     */
    public static function insert(string $table, array $data) {

        if (!empty($data) && is_array($data)) {

            $req = "INSERT INTO `$table` (";
            $values = '';
            $u = 0;
            $attrs = [];

            foreach ($data as $k => $v) {
                if (!is_numeric($k)) {
                    $req .= '`' . $k . '`';

                    if (!is_null($v)) {
                        $values .= '?';
                        if ($v instanceof DateTime) {
                            $attrs[] = $v->format('Y-m-d H:i');
                        } else {
                            $attrs[] = $v;
                        }
                    } else {
                        $values .= 'NULL';
                    }

                    if ($u != count($data) - 1) {
                        $req .= ',';
                        $values .= ',';
                    }
                }
                $u++;
            }

            $req .= ") VALUES (" . $values . ")";
            $res = self::db()->prepare($req, $attrs);
            if ($res) {
                return self::db()->lastInsertId();
            }
        }
        return false;
    }

    /**
     * requete update auto
     *
     * @param string $table Nom de la table SQL
     * @param array $data Array colonne => valeur
     * @param array|int $id Identifiant clef primaire ou array where
     * @param string $id_col Nom de la colonne de clef primaire
     * @return false|PDOStatement
     */
    public static function update(string $table, array $data, $where, $id_col = "id") {
        if (!empty($data) && !empty($where)) {
            $req = "UPDATE `" . $table . "` SET ";
            $z = 0;
            $g = count($data);
            $attrs = [];

            foreach ($data as $k => $v) {
                if (!is_numeric($k)) {
                    if ($v instanceof DateTime) {
                        $attrs[] = $v->format('Y-m-d H:i');
                    } else {
                        $attrs[] = $v;
                    }
                    $req .= '`' . $k . '`= ? ';
                    if ($z != $g - 1) {
                        $req .= ',';
                    }
                }
                $z++;
            }

            if (is_array($where)) {
                $q = '';
                foreach ($where as $key => $value) {
                    if ($q != '') {
                        $q .= " AND";
                    }
                    $q .= " $key='$value'";
                }
                $req .= " WHERE $q";
            } else {
                $req .= " WHERE `" . $id_col . "`='" . $where . "'";
            }
            return self::db()->prepare($req, $attrs);
        }
        return false;
    }

    /**
     * Requête select auto
     *
     * @param string $table Nom de la table SQL
     * @param array $where Array conditions where
     * @param array $data Array nom des colonnes à sélectionner
     * @param array $orderby Array [col => asc]
     * @param string $limit
     * @return PDOStatement
     */
    public static function select(string $table, array $where = [], array $data = ["*"], $orderby = [], $limit = '') {
        if (empty($data)) {
            $data = ['*'];
        }

        $req = "SELECT " . (!empty($data) && $data[0] != '*' ? implode(",", $data) : "*") . " FROM `$table`";
        $attrs = self::queryWhere($req, $where);

        if (!empty($orderby)) {
            if (is_array($orderby)) {
                $order = [];
                foreach ($orderby as $col => $type) {
                    $order[] = "`$col` $type ";
                }
                $req .= " ORDER BY " . implode(', ', $order);
            } else {
                $req .= " ORDER BY " . $orderby;
            }
        }

        if (!empty($limit)) {
            $req .= ' LIMIT ' . $limit;
        }

        if (!empty($attrs)) {
            return self::db()->prepare($req, $attrs);
        }

        return self::db()->query($req);
    }

    /**
     * @param string $table
     * @param array $where
     * @return PDOStatement
     */
    public static function count(string $table, array $where = []) {
        $req = "SELECT COUNT(*) FROM $table";
        $attrs = self::queryWhere($req, $where);

        if (!empty($attrs)) {
            return self::db()->prepare($req, $attrs);
        }

        return self::db()->query($req);
    }

    /**
     * @param string $req
     * @param array $where
     * @return array
     */
    private static function queryWhere(string &$req, array $where = []) {
        $c = count($where);
        $attrs = [];

        if ($c > 0) {
            $req .= " WHERE ";
            $l = 0;
            foreach ($where as $k => $v) {
                $sign = "=";
                if (is_string($v) && (isset($v[0]) && ($v[0] == ">" || $v[0] == "<"))) {
                    $sign = $v[0];
                    if ($v[1] == "=") {
                        $sign .= "=";
                    }
                    $v = intval(substr($v, strlen($sign)));
                }
                if (!is_int($k)) {
                    if ($v === null) {
                        $req .= "`$k` IS NULL";
                    } elseif (is_array($v)) {
                        $vvx = 0;
                        $req .= "`$k` IN (";
                        foreach ($v as $vv) {
                            $req .= $vvx == 0 ? "" : ",";
                            $req .= "?";
                            $attrs[] = $vv;
                            $vvx++;
                        }
                        $req .= ")";
                    } else {
                        $req .= "`$k` $sign ?";
                        $attrs[] = $v;
                    }
                } else {
                    $req .= $v;
                }

                if ($l < $c - 1) {
                    $req .= " AND ";
                }

                $l++;
            }
        }

        return $attrs;
    }

    /**
     * Supprime une ou plusieurs entrées de la base de données
     *
     * @param string $table
     * @param array|int $id
     * @param string $col
     * @return PDOStatement
     */
    public static function delete(string $table, $id, string $col = "id") {
        if (is_array($id)) {
            $where = "";
            foreach ($id as $key => $value) {
                if (!empty($where)) {
                    $where .= " AND ";
                }
                $where .= "`$key`='$value'";
            }
            return self::db()->query("DELETE FROM `$table` WHERE $where");
        }
        return self::db()->query("DELETE FROM `$table` WHERE `$col`=$id");
    }

    /**
     * @param $statement
     * @param $attrs
     * @return bool|PDOStatement
     */
    public static function prepare($statement, $attrs) {
        return self::db()->prepare($statement, $attrs);
    }

    /**
     * @param string $tableName
     * @return int
     */
    public static function getNextPrimaryKey(string $tableName) {
        $res = self::db()->query("SELECT MAX(id) AS m FROM `$tableName`");
        $rec = $res->fetch();
        return intval($rec['m']) + 1;
    }

    /**
     * @param string $TBNAME
     */
    public static function truncate(string $TBNAME) {
        self::db()->query("TRUNCATE $TBNAME");
    }

    /**
     * @return string
     */
    public function lastInsertId() {
        return self::db()->lastInsertId();
    }

    /**
     * @param callable $callable
     */
    public function transaction(callable $callable) {
        self::db()->transaction($callable);
    }

    /**
     * @param $timestamp
     * @param string $outputFormat
     * @return string
     * @throws Exception
     */
    public static function timestampToDate(int $timestamp, $outputFormat = 'Y-m-d H:i') {
        $dt = new DateTime();
        $dt->setTimestamp($timestamp);
        return $dt->format($outputFormat);
    }
}
