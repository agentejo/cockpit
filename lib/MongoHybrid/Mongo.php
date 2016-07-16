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

        if (isset($filter["_id"]) && is_string($filter["_id"])) $filter["_id"] = new \MongoDB\BSON\ObjectID($filter["_id"]);

        $doc =  $this->getCollection($collection)->findOne($filter, ['projection' => $projection]);

        if (isset($doc["_id"])) $doc["_id"] = (string) $doc["_id"];

        return $doc;
    }

    public function find($collection, $options = []){

        $filter = isset($options["filter"]) && $options["filter"] ? $options["filter"] : [];
        $fields = isset($options["fields"]) && $options["fields"] ? $options["fields"] : [];
        $limit  = isset($options["limit"])  && $options["limit"]  ? $options["limit"]  : null;
        $sort   = isset($options["sort"])   && $options["sort"]   ? $options["sort"]   : null;
        $skip   = isset($options["skip"])   && $options["skip"]   ? $options["skip"]   : null;

        if ($filter && isset($filter["_id"])) {
            $filter["_id"] = new \MongoDB\BSON\ObjectID($filter["_id"]);
        }

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

        if (isset($doc["_id"]) && is_string($doc["_id"])) $doc["_id"] = new \MongoDB\BSON\ObjectID($doc["_id"]);

        $ref = $doc;

        $return = $this->getCollection($collection)->insertOne($ref);

        $ref['_id'] = $return->getInsertedId();

        if (isset($ref["_id"])) $ref["_id"] = (string) $ref["_id"];

        $doc = $ref;

        return $return;
    }

    public function save($collection, &$data) {

        if (isset($data["_id"]) && is_string($data["_id"])) $data["_id"] = new \MongoDB\BSON\ObjectID($data["_id"]);

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

        if (isset($criteria["_id"]) && is_string($criteria["_id"])) $criteria["_id"] = new \MongoDB\BSON\ObjectID($criteria["_id"]);
        if (isset($data["_id"]) && is_string($data["_id"])) $data["_id"] = new \MongoDB\BSON\ObjectID($data["_id"]);

        return $this->getCollection($collection)->updateMany($criteria, $data);
    }

    public function remove($collection, $filter=[]) {

        if (isset($filter["_id"]) && is_string($filter["_id"])) $filter["_id"] = new \MongoDB\BSON\ObjectID($filter["_id"]);

        return $this->getCollection($collection)->deleteMany($filter);
    }

    public function count($collection, $filter=[]) {

        return $this->getCollection($collection)->count($filter);
    }


}
