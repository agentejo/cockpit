<?php

namespace MongoHybrid;

class Client {

    protected $driver;
    public $type;

    public function __construct($server, $options=[]) {

        if (strpos($server, 'mongodb://')===0) {

            $cls = class_exists('\MongoClient') ? 'MongoHybrid\\MongoLegacy':'MongoHybrid\\Mongo';

            $this->driver = new $cls($server, $options);
            $this->type = 'mongodb';
        }

        if (strpos($server, 'mongolite://')===0) {
            $this->driver = new MongoLite($server, $options);
            $this->type = 'mongolite';
        }
    }

    public function dropCollection($name, $db = null) {
        return $this->driver->getCollection($name, $db)->drop();
    }

    public function renameCollection($newname, $db = null) {

        return $this->driver->getCollection($name, $db)->renameCollection($newname);
    }

    public function save($collection, &$data) {
        return $this->driver->save($collection, $data);
    }

    public function insert($collection, &$doc) {
        return $this->driver->insert($collection, $doc);
    }


    /*
        simple key-value storage implementation
    */

    /**
     * Get value for specific key
     *
     * @param  string $collection
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function getKey($collection, $key, $default = null) {

        $entry = $this->driver->findOne($collection, ['key' => $key]);

        return $entry ? $entry['val'] : $default;
    }

    /**
     * Set value for specific key
     *
     * @param  string $collection
     * @param  string $key
     * @param  mixed $value
     */
    public function setKey($collection, $key, $value) {

        $entry = $this->driver->findOne($collection, ['key' => $key]);

        if ($entry) {
            $entry['val'] = $value;
        } else {
            $entry = [
                'key' => $key,
                'val' => $value
            ];
        }

        return $this->driver->save($collection, $entry);
    }


    /**
     * Delete Key(s)
     *
     * @param  string $collection
     * @param  string $key
     * @return integer
     */
    public function removeKey($collection, $key) {
        return $this->driver->remove($collection, ['key' => (is_array($key) ? ['$in' => $key] : $key)]);
    }

    /**
     * Check if key exists
     *
     * @param  string $collection @param  string $collection
     * @param  string $key
     */
    public function keyExists($collection, $key) {
        return $this->driver->count($collection, ['key' => $key]);;
    }

    /**
     * Increment value by x
     *
     * @param  string  $collection
     * @param  string  $key
     * @param  integer $by
     * @return integer
     */
    public function incrKey($collection, $key, $by = 1) {

        $current = $this->getKey($collection, $key, 0);
        $newone  = $current + $by;

        $this->setKey($collection, $key, $newone);

        return $newone;
    }

    /**
     * Decrement value by x
     *
     * @param  string  $collection
     * @param  string  $key
     * @param  integer $by
     * @return integer
     */
    public function decrKey($collection, $key, $by = 1) {
        return $this->incr($collection, $key, ($by * -1));
    }

    /**
     * Add item to a value (right)
     *
     * @param  string $collection @param  string $collection
     * @param  string $key
     * @param  mixed $value
     * @return integer
     */
    public function rpush($collection, $key, $value) {

        $list = $this->getKey($collection, $key, []);

        $list[] = $value;

        $this->setKey($collection, $key, $list);

        return count($list);
    }

    /**
     * Add item to a value (left)
     *
     * @param  string $collection @param  string $collection
     * @param  string $key
     * @param  mixed $value
     * @return integer
     */
    public function lpush($collection, $key, $value) {

        $list = $this->getKey($collection, $key, []);

        array_unshift($list, $value);

        $this->setKey($collection, $key, $list);

        return count($list);
    }



    /**
     * Set the value of an element in a list by its index
     *
     * @param  string $collection
     * @param  string $key
     * @param  integer $index
     * @param  mixed $value
     * @return boolean
     */
    public function lset($collection, $key, $index, $value) {

        $list = $this->getKey($collection, $key, []);

        if ($index < 0) {
            $index = count($list) - abs($index);
        }

        if (isset($list[$index])){
            $list[$index] = $value;
            $this->setKey($collection, $key, $list);

            return true;
        }

        return false;
    }

    /**
     * Get an element from a list by its index
     *
     * @param  string $collection
     * @param  string $key
     * @param  integer $index
     * @return mixed
     */
    public function lindex($collection, $key, $index) {

        $list = $this->getKey($collection, $key, []);

        if ($index < 0) {
            $index = count($list) - abs($index);
        }

        return isset($list[$index]) ? $list[$index]:null;
    }

    /**
     * Set the string value of a hash field
     *
     * @param  string $collection
     * @param  string $key
     * @param  string $field
     * @param  mixed $value
     */
    public function hset($collection, $key, $field, $value) {

        $set = $this->getKey($collection, $key, []);

        $set[$field] = $value;
        $this->setKey($collection, $key, $set);
    }

    /**
     * Get the value of a hash field
     *
     * @param  string $collection
     * @param  string $key
     * @param  string $field
     * @param  mixed $default
     * @return mixed
     */
    public function hget($collection, $key, $field, $default=null) {

        $set = $this->getKey($collection, $key, []);

        return isset($set[$field]) ? $set[$field]:$default;
    }

    /**
     * Get all the fields and values in a hash
     *
     * @param  string $collection
     * @param  string $key
     * @return array
     */
    public function hgetall($key) {

        $set = $this->getKey($collection, $key, []);

        return $set;
    }

    /**
     * Determine if a hash field exists
     *
     * @param  string $collection
     * @param  string $key
     * @param  string $field
     * @return boolean
     */
    public function hexists($collection, $key, $field) {

        $set = $this->getKey($collection, $key, []);

        return isset($set[$field]);
    }

    /**
     * Get all the fields in a hash
     *
     * @param  string $collection
     * @param  string $key
     * @return array
     */
    public function hkeys($key) {

        $set = $this->getKey($collection, $key, []);

        return array_keys($set);
    }

    /**
     * Get all the values in a hash
     *
     * @param  string $collection
     * @param  string $key
     * @return array
     */
    public function hvals($key) {

        $set = $this->getKey($collection, $key, []);

        return array_values($set);
    }

    /**
     * Get the number of fields in a hash
     *
     * @param  string $collection
     * @param  string $key
     * @return integer
     */
    public function hlen($key) {

        return count($this->hkeys($key));
    }

    /**
     * Delete one or more hash fields
     *
     * @param  string $collection
     * @param  string $key
     * @return integer
     */
    public function hdel($key) {

        $set = $this->getKey($collection, $key, []);

        if (!count($set)) return 0;

        $fields  = func_get_args();
        $removed = 0;

        for ($i=1; $i<count($fields); $i++){

            $field = $fields[$i];

            if (isset($set[$field])){
                unset($set[$field]);
                $removed++;
            }
        }

        $this->setKey($collection, $key, $set);

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
    public function hincrby($collection, $key, $field, $by = 1) {

        $current = $this->hget($collection, $key, $field, 0);
        $newone  = $current+$by;

        $this->hset($collection, $key, $field, $newone);

        return $newone;
    }

    /**
     * Get the values of all the given hash fields
     *
     * @param  string $collection
     * @param  string $key
     * @return array
     */
    public function hmget($key) {

        $set     = $this->getKey($collection, $key, []);
        $fields  = func_get_args();
        $values  = [];

        for ($i=1; $i<count($fields); $i++){
            $field = $fields[$i];
            $values[] = isset($set[$field]) ? $set[$field]:null;
        }

        return $values;
    }

    /**
     * Set multiple hash fields to multiple values
     *
     * @param  string $collection
     * @param  string $key
     */
    public function hmset($key) {

        $set     = $this->getKey($collection, $key, []);
        $args    = func_get_args();

        for ($i=1; $i<count($fields); $i++){
            $field = $args[$i];
            $value = isset($args[($i+1)]) ? $args[($i+1)] : null;

            $set[$field] = $value;
            $i = $i + 1;
        }

        $this->setKey($collection, $key, $set);
    }


    public function __call($method, $args) {

        return call_user_func_array([$this->driver, $method], $args);
    }
}
