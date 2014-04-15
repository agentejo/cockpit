<?php

namespace MongoHybrid;


class ResultSet extends \ArrayObject {

    protected $documents;
    protected $driver;

    public function __construct($driver, &$documents) {

        $this->driver   = $driver;

        parent::__construct($documents);
    }

    public function hasOne($collections) {

        foreach ($collections as $fkey => $collection) {
            # code...
        }

    }

    public function hasMany($collections) {

        foreach ($collections as $collection => $fkey) {
            # code...
        }
    }

    public function toArray() {
        return $this->getArrayCopy();
    }
}