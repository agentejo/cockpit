<?php

/**
 * RedisLite class.
 */
class RedisLite {

    /**
     * @var string
     */
    protected $path;

    /**
     * @var object
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param string $path
     * @param array  $options
     */
    public function __construct($path = ":memory:", $options = array()) {

        $options = array_merge(array("storagetable"=>"storage"), $options);
        $dns     = "sqlite:{$path}";

        $this->path = $path;
        $this->connection = new \PDO($dns, null, null, $options);

        $stmt  = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='".$options["storagetable"]."';");
        $table = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!isset($table["name"])) {
            $this->connection->exec("CREATE TABLE ".$options["storagetable"]." (key VARCHAR PRIMARY KEY, keyval TEXT)");
        }
    }

    /**
     * Get value for specific key
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {

        $stmt = $this->connection->query("SELECT * FROM storage WHERE `key`='{$key}';");
        $res  = $stmt->fetch(\PDO::FETCH_ASSOC);

        return  isset($res["key"]) ? json_decode($res["keyval"], true) : $default;
    }

    /**
     * Set value for specific key
     *
     * @param  string $key
     * @param  mixed $value
     */
    public function set($key, $value) {

        $value = $this->connection->quote(json_encode($value,JSON_NUMERIC_CHECK));

        if($this->exists($key)) {
            $sql = "UPDATE storage SET `keyval`={$value} WHERE `key`='{$key}'";
        } else {
            $sql = "INSERT INTO storage (`key`,`keyval`) VALUES ('{$key}',{$value})";
        }

        $this->connection->exec($sql);
    }

    /**
     * Check if key exists
     *
     * @param  string $key
     */
    public function exists($key) {

        $stmt = $this->connection->query("SELECT `key` FROM storage WHERE `key`='{$key}';");
        $res  = $stmt->fetch(\PDO::FETCH_ASSOC);

        return isset($res["key"]);
    }

    /**
     * Get all keys matching a pattern
     *
     * @param  string $pattern
     * @return array
     */
    public function keys($pattern = null) {

        $keys = array();
        $stmt = $this->connection->query("SELECT `key` FROM storage ORDER BY `key`;");
        $res  = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$pattern) {

            foreach ($res as $record) {
                $keys[] = $record["key"];
            }

        } else {

            $matcher = function_exists('fnmatch') ? 'fnmatch': function($pattern, $string){
                return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
            };

            foreach ($res as $record) {
                if($matcher($pattern, $record["key"])) {
                    $keys[] = $record["key"];
                }
            }
        }

        return $keys;
    }

    /**
     * Delete Key(s)
     *
     * @param  string $key
     * @return integer
     */
    public function del($key) {

        $keys = func_get_args();
        $removed = 0;

        foreach ($keys as $key) {
            $sql = 'DELETE FROM storage WHERE `key`="'.$key.'"';
            $this->connection->exec($sql);
            $removed++;
        }

        return $removed;
    }

    /**
     * Get value type
     *
     * @param  string $key
     * @return string
     */
    public function type($key) {

        $value = $this->get($key, null);

        return gettype($value);
    }

    /**
     * Increment value by x
     *
     * @param  string  $key
     * @param  integer $by
     * @return integer
     */
    public function incr($key, $by = 1) {

        $current = $this->get($key, 0);
        $newone  = $current + $by;

        $this->set($key, $newone);

        return $newone;
    }

    /**
     * Decrement value by x
     *
     * @param  string  $key
     * @param  integer $by
     * @return integer
     */
    public function decr($key, $by = 1) {

        return $this->incr($key, ($by * -1));
    }

    /**
     * Count $value items
     *
     * @param  string $key
     * @return integer
     */
    public function llen($key) {

        $value = $this->get($key, array());

        return is_array($value) ? count($value):0;
    }

    /**
     * Add item to a value (right)
     *
     * @param  string $key
     * @param  mixed $value
     * @return integer
     */
    public function rpush($key, $value) {

        $list = $this->get($key, array());

        $list[] = $value;

        $this->set($key, $list);

        return count($list);
    }

    /**
     * Add item to a value (left)
     *
     * @param  string $key
     * @param  mixed $value
     * @return integer
     */
    public function lpush($key, $value) {

        $list = $this->get($key, array());

        array_unshift($list, $value);

        $this->set($key, $list);

        return count($list);
    }

    /**
     * Set the value of an element in a list by its index
     *
     * @param  string $key
     * @param  integer $index
     * @param  mixed $value
     * @return boolean
     */
    public function lset($key, $index, $value) {

        $list = $this->get($key, array());

        if($index < 0) {
            $index = count($list) - abs(index);
        }

        if(isset($list[$index])){
            $list[index] = $value;
            $this->set($key, $list);

            return true;
        }

        return false;
    }

    /**
     * Get an element from a list by its index
     *
     * @param  string $key
     * @param  integer $index
     * @return mixed
     */
    public function lindex($key, $index) {

        $list = $this->get($key, array());

        if($index < 0) {
            $index = count($list) - abs(index);
        }

        return isset($list[$index]) ? $list[$index]:null;
    }

    /**
     * Set the string value of a hash field
     *
     * @param  string $key
     * @param  string $field
     * @param  mixed $value
     */
    public function hset($key, $field, $value) {

        $set = $this->get($key, array());

        $set[$field] = $value;
        $this->set($key, $set);
    }

    /**
     * Get the value of a hash field
     *
     * @param  string $key
     * @param  string $field
     * @param  mixed $default
     * @return mixed
     */
    public function hget($key, $field, $default=null) {

        $set = $this->get($key, array());

        return isset($set[$field]) ? $set[$field]:$default;
    }

    /**
     * Get all the fields and values in a hash
     *
     * @param  string $key
     * @return array
     */
    public function hgetall($key) {

        $set = $this->get($key, array());

        return $set;
    }

    /**
     * Determine if a hash field exists
     *
     * @param  string $key
     * @param  string $field
     * @return boolean
     */
    public function hexists($key, $field) {

        $set = $this->get($key, array());

        return isset($set[$field]);
    }

    /**
     * Get all the fields in a hash
     *
     * @param  string $key
     * @return array
     */
    public function hkeys($key) {

        $set = $this->get($key, array());

        return array_keys($set);
    }

    /**
     * Get all the values in a hash
     *
     * @param  string $key
     * @return array
     */
    public function hvals($key) {

        $set = $this->get($key, array());

        return array_values($set);
    }

    /**
     * Get the number of fields in a hash
     *
     * @param  string $key
     * @return integer
     */
    public function hlen($key) {

        return count($this->hkeys($key));
    }

    /**
     * Delete one or more hash fields
     *
     * @param  string $key
     * @return integer
     */
    public function hdel($key) {

        $set = $this->get($key, array());

        if(!count($set)) return 0;

        $fields  = func_get_args();
        $removed = 0;

        for ($i=1; $i<count($fields); $i++){

            $field = $fields[$i];

            if(isset($set[$field])){
                unset($set[$field]);
                $removed++;
            }
        }

        $this->set($key, $set);

        return $removed;
    }

    /**
     * Increment the integer value of a hash field by the given number
     *
     * @param  string  $key
     * @param  string  $field
     * @param  integer $by
     * @return integer
     */
    public function hincrby($key, $field, $by = 1) {

        $current = $this->hget($key, $field, 0);
        $newone  = $current+by;

        $this->hset($key, $field, $newone);

        return $newone;
    }

    /**
     * Get the values of all the given hash fields
     *
     * @param  string $key
     * @return array
     */
    public function hmget($key) {

        $set     = $this->get($key, array());
        $fields  = func_get_args();
        $values  = array();

        for ($i=1; $i<count($fields); $i++){
            $field = $fields[$i];
            $values[] = isset($set[$field]) ? $set[$field]:null;
        }

        return $values;
    }

    /**
     * Set multiple hash fields to multiple values
     *
     * @param  string $key
     */
    public function hmset($key) {

        $set     = $this->get($key, array());
        $args    = func_get_args();

        for ($i=1; $i<count($fields); $i++){
            $field = $args[$i];
            $value = isset($args[($i+1)]) ? $args[($i+1)] : null;

            $set[$field] = $value;
            $i = $i + 1;
        }

        $this->set($key, $set);
    }
}