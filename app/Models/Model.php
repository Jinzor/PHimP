<?php

namespace App\Models;

use App\Core\Database\Sql;
use App\Core\Request;
use DateTime;
use Exception;
use Generator;
use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * Objet de base
 *
 * Défini une méthode de chargement et une méthode d'enregistrement générique
 */
abstract class Model
{
    const IDC = 'id';

    /** @var string Nom de la table associée */
    static $table = '';

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
     * @param int|array $id
     */
    public function __construct($id = 0) {
        if (is_int($id) || is_string($id)) {
            if ($id > 0) {
                $this->load($id);
            }
        } elseif (is_array($id)) {
            $this->hydrate($id);
        } else {
            throw new InvalidArgumentException('Model requires either an id or an array of data, provided ' . gettype($id));
        }
    }

    /**
     * Retourne les colonnes utilisées pour un "select" SQL
     * Pour certains objets il est utile de ne pas sélectionner toutes les colonnes par défaut (*).
     * Exemple : colonnes BLOB / lourdes
     *
     * @return array
     */
    protected static function getFetchableColumns() {
        return ['*'];
    }

    /**
     * @return array|string[]
     */
    public static function getColumns() {
        $cols = static::$columns;
        if (!in_array(static::IDC, $cols)) {
            $cols[] = static::IDC;
        }

        return $cols;
    }

    /**
     * Chargement de l'objet depuis la base de données
     *
     * @param $id
     * @return static
     */
    protected function load($id) {

        $res = Sql::select(static::$table, [static::IDC => $id], static::getFetchableColumns());

        if ($res && $res->rowCount() > 0) {
            $object = $res->fetch(PDO::FETCH_ASSOC);
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
     * @param string[] $data
     */
    public function hydrate(array $data) {
        foreach (array_merge(static::$columns, [static::IDC]) as $c) {
            if (isset($data[$c])) {
                $this->{$c} = $data[$c];
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
            if (property_exists($this, 'created_at') && key_exists('created_at', $ret) && is_null($ret['created_at'])) {
                try {
                    $this->created_at = $ret['created_at'] = new DateTime();
                } catch (Exception $e) {
                    //
                }
            }
            $this->id = Sql::insert(static::$table, $ret);
            return $this->id;
        }

        return Sql::update(static::$table, $ret, $this->id, static::IDC);
    }

    /**
     * Supprime un objet de la base de données
     *
     * @return PDOStatement
     */
    public function delete() {
        return Sql::delete(static::$table, $this->id);
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
        return Sql::update(static::$table, [$prop => $val], $this->id);
    }

    /**
     * Vide les entrées de la table correspondante
     */
    public static function truncate() {
        Sql::truncate(static::$table);
    }

    /**
     * @param array $params
     * @param array $sort
     * @return Generator
     */
    public static function getAllAsGenerator(array $params = [], $sort = []) {
        $res = Sql::select(static::$table, $params, [static::IDC], $sort);
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
        $result = Sql::select(static::$table, $where, static::getFetchableColumns(), $orderby, $limit);
        if ($result) {
            return static::resQueryToObject($result, static::class);
        }
        return [];
    }

    /**
     * @param string $statement
     * @param array $prepareData
     * @return static[]
     */
    public static function query(string $statement, $prepareData = []) {
        if (!empty($prepareData)) {
            $result = Sql::prepare($statement, $prepareData);
        } else {
            $result = Sql::query($statement);
        }
        if ($result) {
            return self::resQueryToObject($result, static::class);
        }
        return [];
    }

    /**
     * @param string $query
     * @param array $prepareData
     * @return static[]
     */
    public static function where(string $query, $prepareData = []) {
        return static::query("SELECT * FROM " . static::$table . " WHERE " . $query, $prepareData);
    }

    /**
     * @param PDOStatement $result
     * @param string $classname
     * @return array Array of objects $classname
     */
    protected static function resQueryToObject(PDOStatement $result, string $classname) {
        $data = [];
        // On n'utilise plus "fetch class" car problème quand load() est overwrite;
        while ($rec = $result->fetch(PDO::FETCH_ASSOC)) {
            $obj = new $classname;
            $obj->loadRec($rec);
            $data[] = $obj;
        }

        return $data;
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @return static|null
     */
    public static function find($where = [], $orderBy = []) {
        if (!is_array($where)) {
            $where = [static::IDC => intval($where)];
        }
        if (empty($orderBy)) {
            $orderBy = [static::IDC => 'DESC'];
        }
        $element = static::getAll($where, $orderBy, 1);
        if (!empty($element)) {
            return $element[0];
        }
        return null;
    }

    /**
     * @param array $where
     * @return int
     */
    public static function count($where = []) {
        $result = Sql::count(static::$table, $where);
        if ($result) {
            return intval($result->fetch()[0]);
        }
        return 0;
    }

    /**
     * @param $k
     * @param $v
     * @return bool
     */
    protected function isEditableProperty($k, $v) {
        if (property_exists(static::class, $k) && ($this->$k != $v || $this->id == 0) && $k != static::IDC) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function saveData(array $data) {

        $updatedElements = [];
        foreach ($data as $k => $v) {
            if ($this->isEditableProperty($k, ($this->id > 0 ? $v : null))) {
                $updatedElements[] = [
                    'key'       => $k,
                    'value'     => $v,
                    'old_value' => $this->$k,
                ];
                $this->$k = $v;
            }
        }
        if (!empty($updatedElements)) {
            $itemSaved = $this->save();
            if ($itemSaved != false) {
                return $this->id > 0 ? $this->id : $itemSaved;
            }
            throw new Exception(Request::ERROR_SAVE);
        }
        return $this->id;
    }

    /**
     * Clone un model vers un autre, le sauvegarde et le retourne
     *
     * @param bool $save True si l'objet doit être immédiatement sauvegardé après
     * @param array $excludedVars Liste de colonne à ignorer pendant le clônage
     * @param $result ?static Model appelant (réceptacle des données)
     * @return $this
     */
    public function clone($save = true, $excludedVars = [], &$result = null) {
        $result = $result ?? new static();
        foreach (static::$columns as $k) {
            if ($k != static::IDC && !in_array($k, $excludedVars)) {
                $result->$k = $this->$k;
            }
        }
        if ($save == true) {
            $result->save();
        }
        return $result;
    }

    /**
     * @return array
     */
    public function _toArray() {
        return get_object_vars($this);
    }
}
