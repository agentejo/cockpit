<?php

namespace MongoHybrid;

include_once(__DIR__.'/../MongoDB/functions.php');

class Mongo {

    protected $client;
    protected $db;
    protected $options;

    public function __construct($server, $options=[]) {

        $this->client  = new \MongoDB\Client($server, $options, ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]);
        $this->db      = $this->client->selectDatabase($options["db"]);
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

        if (is_string($id)) $id = new \MongoDB\BSON\ObjectID($id);

        $doc =  $this->getCollection($collection)->findOne(["_id" => $id]);

        if (isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];

        return $doc;
    }

    public function findOne($collection, $filter = [], $projection = []) {

        if (!$filter) $filter = [];

        $filter = $this->_fixMongoIds($filter);
        $doc    = $this->getCollection($collection)->findOne($filter, ['projection' => $projection]);

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

        $cursor = $this->getCollection($collection)->find($filter, [
            'projection' => $fields,
            'limit' => $limit,
            'skip'  => $skip,
            'sort'  => $sort
        ]);

        $docs = $cursor->toArray();

        if (count($docs)) {

            foreach ($docs as &$doc) {
                if(isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];
            }

        } else {
            $docs = [];
        }

        $resultSet = new ResultSet($this, $docs);

        return $resultSet;
    }

    public function insert($collection, &$doc) {

        if (isset($doc[0])) {

            foreach($doc as &$d) {
                $this->insert($collection, $d);
            }

            return $doc;
        }

        $doc = $this->_fixMongoIds($doc);
        $ref = $doc;

        $return = $this->getCollection($collection)->insertOne($ref);

        $ref['_id'] = $return->getInsertedId();

        if (isset($ref["_id"])) $ref["_id"] = (string) $ref["_id"];

        $doc = $ref;

        return $return;
    }

    public function save($collection, &$data) {

        $data = $this->_fixMongoIds($data);

        $ref = $data;

        if (isset($data["_id"])) {
            $return = $this->getCollection($collection)->updateOne(['_id' => $data["_id"]], ['$set' => $ref]);
        } else {
            $return = $this->getCollection($collection)->insertOne($ref);
            $ref['_id'] = $return->getInsertedId();
        }

        if (isset($ref["_id"])) $ref["_id"] = (string) $ref["_id"];

        $data = $ref;

        return $return;
    }

    public function update($collection, $criteria, $data) {

        $criteria = $this->_fixMongoIds($criteria);
        $data     = $this->_fixMongoIds($data);

        return $this->getCollection($collection)->updateMany($criteria, $data);
    }

    public function remove($collection, $filter=[]) {

        if (!$filter) $filter = [];

        $filter = $this->_fixMongoIds($filter);

        return $this->getCollection($collection)->deleteMany($filter);
    }

    public function count($collection, $filter=[]) {

        if (!$filter) $filter = [];

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
                    
                    $v = new \MongoDB\BSON\ObjectID($v);

                } elseif (is_array($v) && isset($v['$in'])) {
                    
                    foreach ($v['$in'] as &$id) {
                        if (is_string($id)) {
                            $id = new \MongoDB\BSON\ObjectID($id);
                        }
                    }
                }
            }

            $data[$k] = $v;
        }

        return $data;
    }
}
