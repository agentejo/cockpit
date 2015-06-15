<?php

namespace MongoLite;

/**
 * Database object.
 */
class Database {

    /**
     * @var PDO object
     */
    public $connection;

    /**
     * @var array
     */
    protected $collections = array();

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $document_criterias = array();


    /**
     * Constructor
     *
     * @param string $path
     * @param array  $options
     */
    public function __construct($path = ":memory:", $options = array()) {

        $dns = "sqlite:{$path}";

        $this->path = $path;
        $this->connection = new \PDO($dns, null, null, $options);

        $database = $this;

        $this->connection->sqliteCreateFunction('document_key', function($key, $document){

            $document = json_decode($document, true);

            return isset($document[$key]) ? $document[$key] : '';
        }, 2);

        $this->connection->sqliteCreateFunction('document_criteria', function($funcid, $document) use($database) {

            $document = json_decode($document, true);

            return $database->callCriteriaFunction($funcid, $document);
        }, 2);

        $this->connection->exec('PRAGMA journal_mode = MEMORY');
        $this->connection->exec('PRAGMA synchronous = OFF');
        $this->connection->exec('PRAGMA PAGE_SIZE = 4096');
    }

    /**
     * Register Criteria function
     *
     * @param  mixed $criteria
     * @return mixed
     */
    public function registerCriteriaFunction($criteria) {

        $id = uniqid("criteria");

        if (is_callable($criteria)) {
           $this->document_criterias[$id] = $criteria;
           return $id;
        }

        if (is_array($criteria)) {

            $this->document_criterias[$id] = create_function('$document','return '.UtilArrayQuery::buildCondition($criteria).';');

            return $id;
        }

        return null;
    }

    /**
     * Execute registred criteria function
     *
     * @param  string $id
     * @param  array $document
     * @return boolean
     */
    public function callCriteriaFunction($id, $document) {

        return isset($this->document_criterias[$id]) ? $this->document_criterias[$id]($document):false;
    }

    /**
     * Vacuum database
     */
    public function vacuum() {
        $this->connection->query('VACUUM');
    }

    /**
     * Drop database
     */
    public function drop() {
        if ($this->path != ":memory:") {
            unlink($this->path);
        }
    }

    /**
     * Create a collection
     *
     * @param  string $name
     */
    public function createCollection($name) {
        $this->connection->exec("CREATE TABLE {$name} ( id INTEGER PRIMARY KEY AUTOINCREMENT, document TEXT )");
    }

    /**
     * Drop a collection
     *
     * @param  string $name
     */
    public function dropCollection($name) {
        $this->connection->exec("DROP TABLE `{$name}`");
    }

    /**
     * Get all collection names in the database
     *
     * @return array
     */
    public function getCollectionNames() {

        $stmt   = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence';");
        $tables = $stmt->fetchAll( \PDO::FETCH_ASSOC);
        $names  = array();

        foreach($tables as $table) {
            $names[] = $table["name"];
        }

        return $names;
    }

    /**
     * Get all collections in the database
     *
     * @return array
     */
    public function listCollections() {

        foreach ($this->getCollectionNames() as $name) {
            if(!isset($this->collections[$name])) {
                $this->collections[$name] = new Collection($name, $this);
            }
        }

        return $this->collections;
    }

    /**
     * Select collection
     *
     * @param  string $name
     * @return object
     */
    public function selectCollection($name) {

        if(!isset($this->collections[$name])) {

            if(!in_array($name, $this->getCollectionNames())) {
                $this->createCollection($name);
            }

            $this->collections[$name] = new Collection($name, $this);
        }

        return $this->collections[$name];
    }

    public function __get($collection) {

        return $this->selectCollection($collection);
    }
}


class UtilArrayQuery {

    public static function buildCondition($criteria, $concat = " && ") {

        $fn = array();

        foreach ($criteria as $key => $value) {

            switch($key) {

                case '$and':
                    $fn[] = '('.self::buildCondition($value, ' && ').')';
                    break;
                case '$or':
                    $fn[] = '('.self::buildCondition($value, ' || ').')';
                    break;
                default:

                    $d = '$document';

                    if(strpos($key, ".") !== false) {
                        $keys = explode('.', $key);

                        foreach ($keys as &$k) {
                            $d .= '["'.$k.'"]';
                        }

                    } else {
                        $d .= '["'.$key.'"]';
                    }

                    $fn[] = is_array($value) ? "\\MongoLite\\UtilArrayQuery::check((isset({$d}) ? {$d} : null), ".var_export($value, true).")": "(isset({$d}) && {$d}==".(is_string($value) ? "'{$value}'": var_export($value, true)).")";
            }
        }

        return count($fn) ? trim(implode($concat, $fn)) : 'true';
    }


    public static function check($value, $condition) {

        if(is_null($value)) return false;

        $keys  = array_keys($condition);

        foreach ($keys as &$key) {
            if(!self::evaluate($key, $value, $condition[$key])) {
                return false;
            }
        }

        return true;
    }

    private static function evaluate($func, $a, $b) {

        $r = false;

        switch ($func) {
            case '$eq' :
                $r = $a == $b;
                break;
            case '$not' :
                $r = $a != $b;
                break;
            case '$gte' :
            case '$gt' :
                if (is_numeric($a) && is_numeric($b)) {
                    $r = $a > $b;
                }
                break;

            case '$lte' :
            case '$lt' :
                if (is_numeric($a) && is_numeric($b)) {
                    $r = $a < $b;
                }
                break;
            case '$in' :
                if (! is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $in option must be array');
                $r = in_array($a, $b);
                break;

            case '$has' :
                if (is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $has array not supported');
                $a = @json_decode($a, true) ?  : array();
                $r = in_array($b, $a);
                break;

            case '$all' :
                $a = @json_decode($a, true) ?  : array();
                if (! is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $all option must be array');
                $r = count(array_intersect_key($a, $b)) == count($b);
                break;

            case '$regex' :
            case '$preg' :
            case '$match' :
                $r = (boolean) @preg_match('/'.$b.'/', $a, $match);
                break;

            case '$size' :
                $a = @json_decode($a, true) ?  : array();
                $r = (int) $b == count($a);
                break;

            case '$mod' :
                if (! is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $mod option must be array');
                list($x, $y) = each($b);
                $r = $a % $x == 0;
                break;

            case '$func' :
            case '$fn' :
            case '$f' :
                if (! is_callable($b))
                    throw new \InvalidArgumentException('Function should be callable');
                $r = $b($a);
                break;

            default :
                throw new \ErrorException("Condition not valid ... Use {$func} for custom operations");
                break;
        }

        return $r;
    }
}
