<?php

namespace MongoHybrid;

class MongoLegacy {

    protected $client;
    protected $db;
    protected $options;

    public function __construct($server, $options=[]) {

        $this->client  = new \MongoClient($server, $options);
        $this->db      = $this->client->selectDB($options["db"]);
        $this->options = $options;
    }

    public function getCollection($name, $db = null){

        if ($db) {
            $name = "{$db}/{$name}";
        }

        $name = str_replace('/', '_', $name);

        return $this->db->selectCollection($name);
    }

    public function findOneById($collection, $id){

        if (is_string($id)) $id = new \MongoId($id);

        $doc =  $this->getCollection($collection)->findOne(["_id" => $id]);

        if (isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];

        return $doc;
    }

    public function findOne($collection, $filter = [], $projection = []) {

        $filter = $this->_fixMongoIds($filter);

        if (!$projection) $projection = [];

        $doc =  $this->getCollection($collection)->findOne($filter, $projection);

        if (isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];

        return $doc;
    }

    public function find($collection, $options = []){

        $filter = isset($options["filter"]) && $options["filter"] ? $options["filter"] : [];
        $fields = isset($options["fields"]) && $options["fields"] ? $options["fields"] : [];
        $limit  = isset($options["limit"])  && $options["limit"]  ? $options["limit"]  : null;
        $sort   = isset($options["sort"])   && $options["sort"]   ? $options["sort"]   : null;
        $skip   = isset($options["skip"])   && $options["skip"]   ? $options["skip"]   : null;

        $filter = $this->_fixMongoIds($filter);

        $cursor = $this->getCollection($collection)->find($filter, $fields);

        if ($limit) $cursor->limit($limit);
        if ($sort)  $cursor->sort($sort);
        if ($skip)  $cursor->skip($skip);

        if ($cursor->count()) {

            $docs = array_values(iterator_to_array($cursor));

            foreach ($docs as &$doc) {
                if (isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];
            }

        } else {

            $docs = [];
        }

        $resultSet = new ResultSet($this, $docs);

        return $resultSet;
    }

    public function insert($collection, &$doc) {

        $doc = $this->_fixMongoIds($doc);
        $ref = $doc;

        $return = $this->getCollection($collection)->insert($ref);

        if (isset($ref["_id"])) $ref["_id"] = (string) $ref["_id"];

        $doc = $ref;

        return $return;
    }

    public function save($collection, &$data) {

        $data = $this->_fixMongoIds($data);
        $ref  = $data;

        $return = $this->getCollection($collection)->save($ref);

        if (isset($ref["_id"])) $ref["_id"] = (string) $ref["_id"];

        $data = $ref;

        return $return;
    }


    public function update($collection, $criteria, $data) {

        $criteria = $this->_fixMongoIds($criteria);
        $data     = $this->_fixMongoIds($data);

        return $this->getCollection($collection)->update($criteria, $data);
    }

    public function remove($collection, $filter=[]) {

        $filter = $this->_fixMongoIds($filter);

        return $this->getCollection($collection)->remove($filter);
    }

    public function count($collection, $filter=[]) {

        $filter = $this->_fixMongoIds($filter);

        return $this->getCollection($collection)->count($filter);
    }

    protected function _fixMongoIds(&$data) {

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $k => $v) {
            
            if (is_array($data[$k])) {
                $data[$k] = $this->_fixMongoIds($data[$k]);
            }

            if ($k === '_id') {

                if (is_string($v)) {
                    
                    $v = new \MongoId($v);

                } elseif (is_array($v) && isset($v['$in'])) {
                    
                    foreach ($v['$in'] as &$id) {
                        if (is_string($id)) {
                            $id = new \MongoId($id);
                        }
                    }
                }
            }

            $data[$k] = $v;
        }

        return $data;
    }
}
