<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MongoLite;

/**
 * Database object.
 */
class Database {

    /**
     * @var string - DSN path form memory database
     */
    public const DSN_PATH_MEMORY = ':memory:';

    /**
     * @var PDO object
     */
    public $connection;

    /**
     * @var array
     */
    protected $collections = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $document_criterias = [];

    /**
     * Constructor
     *
     * @param string $path
     * @param array  $options
     */
    public function __construct($path = self::DSN_PATH_MEMORY, $options = []) {

        $dns = "sqlite:{$path}";

        $this->path = $path;
        $this->connection = new \PDO($dns, null, null, $options);

        $database = $this;

        $this->connection->sqliteCreateFunction('document_key', function($key, $document){

            $document = \json_decode($document, true);
            $val      = '';

            if (strpos($key, '.') !== false) {

                $keys = \explode('.', $key);

                switch (\count($keys)) {
                    case 2:
                        $val = isset($document[$keys[0]][$keys[1]]) ? $document[$keys[0]][$keys[1]] : '';
                        break;
                    case 3:
                        $val = isset($document[$keys[0]][$keys[1]][$keys[2]]) ? $document[$keys[0]][$keys[1]][$keys[2]] : '';
                        break;
                    default:
                        $val = isset($document[$keys[0]]) ? $document[$keys[0]] : '';
                }

            } else {
                $val = isset($document[$key]) ? $document[$key] : '';
            }

            return \is_array($val) || \is_object($val) ? \json_encode($val) : $val;
        }, 2);

        $this->connection->sqliteCreateFunction('document_criteria', function($funcid, $document) use($database) {

            $document = \json_decode($document, true);

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

        $id = \uniqid('criteria');

        if (\is_callable($criteria)) {
           $this->document_criterias[$id] = $criteria;
           return $id;
        }

        if (is_array($criteria)) {

            $fn = null;

            eval('$fn = function($document) { return '.UtilArrayQuery::buildCondition($criteria).'; };');

            $this->document_criterias[$id] = $fn;

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
        if ($this->path != static::DSN_PATH_MEMORY) {
            \unlink($this->path);
        }
    }

    /**
     * Create a collection
     *
     * @param  string $name
     */
    public function createCollection($name) {
        $this->connection->exec("CREATE TABLE `{$name}` ( id INTEGER PRIMARY KEY AUTOINCREMENT, document TEXT )");
    }

    /**
     * Drop a collection
     *
     * @param  string $name
     */
    public function dropCollection($name) {
        $this->connection->exec("DROP TABLE `{$name}`");

        // Remove collection from cache
        unset($this->collections[$name]);
    }

    /**
     * Get all collection names in the database
     *
     * @return array
     */
    public function getCollectionNames() {

        $stmt   = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence';");
        $tables = $stmt->fetchAll( \PDO::FETCH_ASSOC);
        $names  = [];

        foreach ($tables as $table) {
            $names[] = $table['name'];
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

        if (!isset($this->collections[$name])) {

            if (!in_array($name, $this->getCollectionNames())) {
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

    public static function buildCondition($criteria, $concat = ' && ') {

        $fn = [];

        foreach ($criteria as $key => $value) {

            switch($key) {

                case '$and':

                    $_fn = [];

                    foreach ($value as $v) {
                        $_fn[] = self::buildCondition($v, ' && ');
                    }

                    $fn[] = '('.\implode(' && ', $_fn).')';

                    break;
                case '$or':

                    $_fn = [];

                    foreach ($value as $v) {
                        $_fn[] = self::buildCondition($v, ' && ');
                    }

                    $fn[] = '('.\implode(' || ', $_fn).')';

                    break;

                case '$where':

                    if (\is_callable($value)) {

                        // need implementation
                    }

                    break;

                default:

                    $d = '$document';

                    if (\strpos($key, '.') !== false) {

                        $keys = \explode('.', $key);

                        foreach ($keys as $k) {
                            $d .= '[\''.$k.'\']';
                        }

                    } else {
                        $d .= '[\''.$key.'\']';
                    }

                    if (\is_array($value)) {
                        $fn[] = "\\MongoLite\\UtilArrayQuery::check((isset({$d}) ? {$d} : null), ".\var_export($value, true).')';
                    } else {

                        if (is_null($value)) {

                            $fn[] = "(!isset({$d}))";

                        } else {

                            $_value = \var_export($value, true);

                            $fn[] = "(isset({$d}) && (
                                is_array({$d}) && is_string({$_value})
                                    ? in_array({$_value}, {$d})
                                    : {$d}=={$_value}
                                )
                            )";
                        }
                    }
            }
        }

        return \count($fn) ? \trim(\implode($concat, $fn)) : 'true';
    }


    public static function check($value, $condition) {

        $keys = \array_keys($condition);

        foreach ($keys as &$key) {

            if ($key == '$options') continue;

            if (!self::evaluate($key, $value, $condition[$key])) {
                return false;
            }
        }

        return true;
    }

    private static function evaluate($func, $a, $b) {

        $r = false;

        if (\is_null($a) && $func != '$exists') {
            return false;
        }

        switch ($func) {
            case '$eq' :
                $r = $a == $b;
                break;

            case '$ne' :
                $r = $a != $b;
                break;

            case '$gte' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a >= $b;
                }
                break;

            case '$gt' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a > $b;
                }
                break;

            case '$lte' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a <= $b;
                }
                break;

            case '$lt' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a < $b;
                }
                break;

            case '$in' :
                if (\is_array($a)) {
                    $r = \is_array($b) ? \count(\array_intersect($a, $b)) : false;
                } else {
                    $r = \is_array($b) ? \in_array($a, $b) : false;
                }
                break;

            case '$nin' :
                if (\is_array($a)) {
                    $r = \is_array($b) ? (\count(\array_intersect($a, $b)) === 0) : false;
                } else {
                    $r = \is_array($b) ? (\in_array($a, $b) === false) : false;
                }
                break;

            case '$has' :
                if (\is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $has array not supported');
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                $r = \in_array($b, $a);
                break;

            case '$all' :
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                if (!\is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $all option must be array');
                $r = \count(\array_intersect_key($a, $b)) == \count($b);
                break;

            case '$regex' :
            case '$preg' :
            case '$match' :
            case '$not':
                $r = (boolean) @\preg_match(isset($b[0]) && $b[0]=='/' ? $b : '/'.$b.'/iu', $a, $match);
                if ($func === '$not') {
                    $r = !$r;
                }
                break;

            case '$size' :
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                $r = (int) $b == \count($a);
                break;

            case '$mod' :
                if (! \is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $mod option must be array');
                $r = $a % $b[0] == $b[1] ?? 0;
                break;

            case '$func' :
            case '$fn' :
            case '$f' :
                if (\is_string($b) || !\is_callable($b))
                    throw new \InvalidArgumentException('Function should be callable');
                $r = $b($a);
                break;

            case '$exists':
                $r = $b ? !\is_null($a) : \is_null($a);
                break;

            case '$fuzzy':
            case '$text':

                $distance = 3;
                $minScore = 0.7;

                if (\is_array($b) && isset($b['$search'])) {

                    if (isset($b['$minScore']) && \is_numeric($b['$minScore'])) $minScore = $b['$minScore'];
                    if (isset($b['$distance']) && \is_numeric($b['$distance'])) $distance = $b['$distance'];

                    $b = $b['search'];
                }

                $r = fuzzy_search($b, $a, $distance) >= $minScore;
                break;

            default :
                throw new \ErrorException("Condition not valid ... Use {$func} for custom operations");
                break;
        }

        return $r;
    }
}


// Helper Functions
function levenshtein_utf8($s1, $s2) {

    $map = [];
    $utf8_to_extended_ascii = function($str) use($map) {

        // find all multibyte characters (cf. utf-8 encoding specs)
        $matches = [];

        if (!\preg_match_all('/[\xC0-\xF7][\x80-\xBF]+/', $str, $matches)) return $str; // plain ascii string

        // update the encoding map with the characters not already met
        foreach ($matches[0] as $mbc) {
            if (!isset($map[$mbc])) $map[$mbc] = \chr(128 + \count($map));
        }

        // finally remap non-ascii characters
        return \strtr($str, $map);
    };

    return levenshtein($utf8_to_extended_ascii($s1), $utf8_to_extended_ascii($s2));
}

function fuzzy_search($search, $text, $distance = 3){

    $needles = \explode(' ', \mb_strtolower($search, 'UTF-8'));
    $tokens  = \explode(' ', \mb_strtolower($text, 'UTF-8'));
    $score   = 0;

    foreach ($needles as $needle){

        foreach ($tokens as $token) {

            if (\strpos($token, $needle) !== false) {
                $score += 1;
            } else {

                $d = levenshtein_utf8($needle, $token);

                if ($d <= $distance) {
                    $l       = \mb_strlen($token, 'UTF-8');
                    $matches = $l - $d;
                    $score  += ($matches / $l);
                }
            }
        }

    }

    return $score / \count($needles);
}

function createMongoDbLikeId() {

    // use native MongoDB ObjectId if available
    if (class_exists('MongoDB\\BSON\\ObjectId')) {
        $objId = new \MongoDB\BSON\ObjectId();
        return (string)$objId;
    }

    // based on https://gist.github.com/h4cc/9b716dc05869296c1be6

    $timestamp = \microtime(true);
    $processId = \random_int(10000, 99999);
    $id        = \random_int(10, 1000);
    $result    = '';

    // Building binary data.
    $bin = \sprintf(
        '%s%s%s%s',
        \pack('N', $timestamp),
        \substr(md5(uniqid()), 0, 3),
        \pack('n', $processId),
        \substr(\pack('N', $id), 1, 3)
    );

    // Convert binary to hex.
    for ($i = 0; $i < 12; $i++) {
        $result .= \sprintf('%02x', ord($bin[$i]));
    }

    return $result;
}
