<?php

namespace MongoHybrid;

class Client {

    protected $driver;

    public function __construct($server, $options=[]) {

        if(strpos($server, 'mongodb://')===0) {
            $this->driver = new Mongo($server, $options);
        }

        if(strpos($server, 'mongolite://')===0) {
            $this->driver = new MongoLite($server, $options);
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


    /* simple key-value storage */
    public function getKey($collection, $key, $default = null) {

        $entry = $this->driver->findOne($collection, ['key' => $key]);

        return $entry ? $entry['val'] : $default;
    }

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

    public function removeKey($collection, $key, $default = null) {

        return $this->driver->remove($collection, ['key' => $key]);
    }

    public function __call($method, $args) {

        return call_user_func_array([$this->driver, $method], $args);
    }
}