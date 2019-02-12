<?php

namespace App\Models;

use App\Core\Dbg;
use App\Core\Request;
use App\Core\Sql;
use App\Core\Str;
use PDO;

/**
 * Objet de base
 *
 * Défini une méthode de chargement et une méthode d'enregistrement générique
 */
abstract class Root
{
    const IDC = 'id';
    const TBNAME = '';
    const LIBELLE = '';

    /**
     * Nom des colonnes
     *
     * @var string[]
     */
    static $columns = [];

    /**
     * Clef primaire SQL
     *
     * @var int
     */
    var $id = 0;

    /**
     * Constructeur standard
     *
     * @param int $id
     */
    public function __construct(int $id = 0) {
        if ($id > 0) {
            $this->load($id);
        }
    }

    /**
     * Chargement de l'objet depuis la base de données
     *
     * @param $id
     * @return static
     */
    protected function load($id) {

        $res = Sql::select(static::TBNAME, [static::IDC => $id]);

        if ($res->rowCount() > 0) {
            $res->setFetchMode(PDO::FETCH_CLASS, static::class);
            $object = $res->fetch();
            foreach ($object as $k => $v) {
                $this->{$k} = $v;
            }
            if (static::IDC != self::IDC) {
                $this->id = $this->{static::IDC};
            }
            return $this;
        } else {
            $this->id = 0;
        }

        return null;
    }

    /**
     * Chargement de l'objet depuis un tableau de chaînes
     * par exemple le résultat d'un fetch_assoc ou fetch_array
     *
     * @param string[] $rec
     */
    public function loadRec($rec) {
        if (isset($rec[static::IDC])) {
            $this->id = $rec[static::IDC];
        }

        foreach (static::$columns as $c) {
            if (isset($rec[$c])) {
                $this->{$c} = $rec[$c];
            }
        }
    }

    /**
     * Sauvegarde de l'objet vers la base de données
     *
     * @return boolean|int
     */
    public function save() {
        $ret = [];
        foreach (static::$columns as $c) {
            $ret[$c] = $this->{$c};
        }

        if ($this->id == 0) {
            $ret[static::IDC] = null;
            $this->id = Sql::insert(static::TBNAME, $ret);
            return $this->id;
        }

        return Sql::update(static::TBNAME, $ret, $this->id, static::IDC);
    }

    /**
     * Supprime un objet de la base de données
     *
     * @return \PDOStatement
     */
    public function delete() {
        return Sql::delete(static::TBNAME, $this->id);
    }

    /**
     * Lecture directe depuis la DB
     *
     * @param string $prop
     * @return mixed
     */
    public function directGet(string $prop) {
        $res = Sql::select(static::TBNAME, [static::IDC => $this->id], [$prop]);
        $this->{$prop} = $res->fetch()[$prop];
        return $this->{$prop};
    }

    /**
     * Enregistrement direct vers la DB
     *
     * @param string $prop
     * @param string $val
     * @return boolean
     */
    public function directSet(string $prop, $val) {
        $this->{$prop} = $val;
        return Sql::update(static::TBNAME, [$prop => $val], $this->id);
    }

    /**
     * @param array $params
     * @param array $sort
     * @return static[]
     */
    public static function itemStack(array $params = [], $sort = []) {
        $rt = [];
        foreach (self::itemStackGenerator($params, $sort) as $elem) {
            $rt[] = $elem;
        }
        return $rt;
    }

    /**
     * @param array $params
     * @param array $sort
     * @return \Generator
     */
    public static function itemStackGenerator(array $params = [], $sort = []) {
        $res = Sql::select(static::TBNAME, $params, [static::IDC], $sort);
        while ($res && $rec = $res->fetch()) {
            yield new static($rec[static::IDC]);
        }
    }

    /**
     * @param array $where
     * @param array $orderby
     * @param string $limit
     * @return static[]
     */
    public static function getAll($where = [], $orderby = [], $limit = '') {
        if (empty($orderby)) {
            $orderby = [static::IDC => 'asc'];
        }
        $result = Sql::select(static::TBNAME, $where, ['*'], $orderby, $limit);
        if ($result) {
            if (static::IDC == self::IDC) {
                return $result->fetchAll(PDO::FETCH_CLASS, static::class);
            } else {
                $data = [];
                while ($obj = $result->fetchObject(static::class)) {
                    $obj->id = $obj->{static::IDC};
                    $data[] = $obj;
                }
                return $data;
            }
        }
        return [];
    }

    /**
     * @param array $where
     * @return int
     */
    public static function count($where = []) {
        $result = Sql::select(static::TBNAME, $where, ['COUNT(*)']);
        if ($result) {
            return intval($result->fetch()[0]);
        }
        return 0;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key) {
        $method = 'get' . ucfirst($key);
        $this->$key = $this->$method();
        return $this->$key;
    }

    protected function isEditableProperty($k, $v) {
        if (property_exists(static::class, $k) && ($this->$k != $v || $this->id == 0) && $k != static::IDC) {
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function saveData(array $data) {
        Dbg::logs('Save data ' . static::class . ' ' . $this->id);
        $updatedElements = [];
        foreach ($data as $k => $v) {
            if ($this->isEditableProperty($k, ($this->id > 0 ? $v : null))) {
                if (is_array($v) && is_array($this->$k)) {
                    Dbg::logs('[' . $k . '] ' . implode('|', $this->$k) . ' => ' . implode('|', $v));
                } else {
                    Dbg::logs('[' . $k . '] ' . $this->$k . ' => ' . $v);
                }
                $updatedElements[] = [
                    'key'       => $k,
                    'value'     => $v,
                    'old_value' => $this->$k,
                ];
                if (Str::startsWith($k, 'cout_')) {
                    $v = str_replace(',', '.', $v);
                }
                $this->$k = $v;
            }
        }
        if (!empty($updatedElements)) {
            $itemSaved = $this->save();
            if ($itemSaved != false) {
                return $this->id > 0 ? $this->id : $itemSaved;
            }
            throw new \Exception(Request::ERROR_SAVE);
        }
        Dbg::debug('=> Aucune modification');
        return $this->id;
    }
}
