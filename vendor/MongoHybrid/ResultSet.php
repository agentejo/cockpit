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

        $cache = [];

        foreach ($this as &$doc) {

            foreach ($collections as $fkey => $collection) {

                if (isset($doc[$fkey]) && $doc[$fkey]) {

                    if (!isset($cache[$collection][$doc[$fkey]])) {
                        $cache[$collection][$doc[$fkey]] = $this->driver->findOneById($collection, $doc[$fkey]);
                    }

                    $doc[$collection] = $cache[$collection][$doc[$fkey]];
                }
            }
        }

    }

    public function hasMany($collections) {

        foreach ($this as &$doc) {

            foreach ($collections as $collection => $fkey) {

                $doc[$collection] = $this->driver->find($collection, ['filter' => [$fkey=>$doc['_id']]]);
            }
        }
    }

    public function toArray() {
        return $this->getArrayCopy();
    }
}