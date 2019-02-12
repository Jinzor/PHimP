<?php

namespace App\Core;

use PDO;

class Sql
{
    /**
     * Instance PDO
     *
     * @var \PDO
     */
    static $instance;

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
     * Etabli la connexion SQL
     *
     * @return PDO
     * @throws \Exception
     */
    public static function connect() {
        self::$sqlDebuging = DEBUG && php_sapi_name() != 'cli';

        try {
            self::$instance = new PDO('mysql:dbname=' . MYSQL_DB . ';host=' . MYSQL_HOST, MYSQL_USER, MYSQL_PASS,
                [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            self::$stampConnect = microtime(true);
        } catch (\Exception $e) {
            Dbg::logs("SQL connect fail " . MYSQL_USER . "@" . MYSQL_HOST . "/" . MYSQL_DB);
            Dbg::logs("Message: " . $e->getMessage() . " (" . $e->getCode() . ")");
            throw new \Exception($e);
        }

        return self::$instance;
    }

    /**
     * @param $statement
     * @param $className
     * @return array
     */
    public static function getObjectList($statement, $className) {
        $result = Sql::query($statement);
        if ($result) {
            return $result->fetchAll(\PDO::FETCH_CLASS, $className);
        }
        return [];
    }

    /**
     * Requete sql standard
     *
     * @param string $statement Requete SQL
     * @return \PDOStatement
     */
    public static function query(string $statement) {
        // Dbg::debug($statement);
        if (self::$sqlDebuging) {
            $start = microtime(true);
        }

        $res = self::$instance->query($statement);

        if (!$res) {
            $rp = debug_backtrace(0);
            Dbg::error("MYSQL (" . self::$instance->errorCode() . ") " . self::$instance->errorInfo()[2] . " \nRequest : \"" . $statement . "\" in " . basename($rp[0]['file']) . " line " . $rp[0]['line']);
        }

        if (self::$sqlDebuging) {
            $trace = debug_backtrace();

            if (!isset(self::$queryDebug[$statement])) {
                $st = microtime(true);
                self::$queryDebug[$statement] = [
                    "time"  => $st - $start,
                    "stamp" => $st,
                    "trace" => $trace,
                    "qty"   => 0,
                ];
            }

            self::$queryDebug[$statement]['qty']++;
        }

        return $res;
    }

    /**
     * Requete insert standard
     *
     * @param string $qr Requete SQL
     * @return int
     */
    public static function queryi(string $qr) {
        return self::query($qr) ? self::$instance->lastInsertId() : false;
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
                        $attrs[] = $v;
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

            if (self::prepare($req, $attrs)) {
                return self::$instance->lastInsertId();
            }
        }
        return false;
    }

    public static function prepare($statement, $attributes) {
        try {
            $req = self::$instance->prepare($statement);
            if (!$req->execute($attributes)) {
                Dbg::error("Erreur MYSQL : Request prepare : \"" . $statement . "\" with attributes " . implode(',', $attributes));
                return false;
            }
        } catch (\Exception $e) {
            Dbg::error($e->getMessage());
            return false;
        }
        return $req;
    }

    /**
     * requete update auto
     *
     * @param string $table Nom de la table SQL
     * @param array $data Array colonne => valeur
     * @param int $id Identifiant clef primaire
     * @param string $id_col Nom de la colonne de clef primaire
     * @return false|\PDOStatement
     */
    public static function update(string $table, array $data, $id, $id_col = "id") {
        if (!empty($data) && $id > 0) {

            $req = "UPDATE `" . $table . "` SET ";
            $z = 0;
            $g = count($data);
            $attrs = [];

            foreach ($data as $k => $v) {
                if (!is_numeric($k)) {
                    $attrs[] = $v;
                    $req .= '`' . $k . '`= ? ';
                    if ($z != $g - 1) {
                        $req .= ',';
                    }
                }
                $z++;
            }

            if (is_array($id)) {
                $where = '';
                foreach ($id as $key => $value) {
                    if ($where != '') {
                        $where .= " AND";
                    }
                    $where .= " $key='$value'";
                }
                $req .= " WHERE $where";
            } else {
                $req .= " WHERE `" . $id_col . "`='" . $id . "'";
            }

            return self::prepare($req, $attrs);
        }
        return false;
    }

    /**
     * Requete select auto
     *
     * @param string $table Nom de la table SQL
     * @param array $where Array conditions where
     * @param array $data Array nom des colonnes à sélectionner
     * @param array $orderby
     * @param string $limit
     * @return \PDOStatement
     */
    public static function select(string $table, array $where = [], array $data = ["*"], $orderby = [], $limit = '') {
        if (empty($data)) {
            $data = ['*'];
        }

        $req = "SELECT " . implode(",", $data) . " FROM `$table`";
        $c = count($where);
        $attrs = [];

        if ($c > 0) {
            $req .= " WHERE ";
            $l = 0;
            foreach ($where as $k => $v) {
                if (!is_int($k)) {
                    if ($v === null) {
                        $req .= "$k IS NULL";
                    } else {
                        $req .= "$k=?";
                        $attrs[] = $v;
                    }
                    if ($l < $c - 1) {
                        $req .= " AND ";
                    }
                } else {
                    $req .= $v;
                    $c++;
                }
                $l++;
            }
        }

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

        if ($limit > 0) {
            $req .= ' LIMIT ' . $limit;
        }

        if (!empty($attrs)) {
            return self::prepare($req, $attrs);
        }

        return self::query($req);
    }

    /**
     * Supprime une ou plusieurs entrées de la base de données
     *
     * @param string $table
     * @param string $id
     * @param string $col
     * @return \PDOStatement
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
            return self::query("DELETE FROM `$table` WHERE $where");
        }
        return self::query("DELETE FROM `$table` WHERE `$col`=$id");
    }

    /**
     * @param $tableName
     * @return int
     */
    public static function getNextPrimaryKey(string $tableName) {
        $res = Sql::query("SELECT MAX(id) AS m FROM `$tableName`");
        $rec = $res->fetch();
        return intval($rec['m']) + 1;
    }

    public function lastInsertId() {
        return self::$instance->lastInsertId();
    }

    public function transaction(callable $callable) {
        self::startTransaction();
        $callable();
        self::endTransaction();
    }

    public static function startTransaction() {
        Sql::query("START TRANSACTION");
    }

    public static function endTransaction() {
        return Sql::query("COMMIT");
    }
}