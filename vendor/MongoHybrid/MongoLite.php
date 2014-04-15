<?php

namespace MongoHybrid;

class MongoLite {

    protected $client;

    public function __construct($server, $options=[]) {

        $this->client = new \MongoLite\Client(str_replace('mongolite://', '', $server));
        $this->db     = $options["db"];
    }

    public function getCollection($name, $db = null){

        if(strpos($name, '/') !== false) {
            list($db, $name) = explode('/', $name, 2);
        }

        if(!$db) {
            $db = $this->db;
        }

        return $this->client->selectCollection($db, $name);
    }

    public function findOne($collection, $filter = []) {
        return $this->getCollection($collection)->findOne($filter);
    }

    public function findOneById($collection, $id){

        return $this->getCollection($collection)->findOne(["_id" => $id]);
    }

    public function find($collection, $options = []){

        $filter = isset($options["filter"]) ? $options["filter"] : null;
        $limit  = isset($options["limit"])  ? $options["limit"] : null;
        $sort   = isset($options["sort"])   ? $options["sort"] : null;
        $skip   = isset($options["skip"])   ? $options["skip"] : null;

        $cursor = $this->getCollection($collection)->find($filter);

        if($limit) $cursor->limit($limit);
        if($sort)  $cursor->sort($sort);
        if($skip)  $cursor->skip($skip);

        $docs      = $cursor->toArray();
        $resultSet = new ResultSet($this, $docs);

        return $resultSet;
    }

    public function insert($collection, &$doc) {
        return $this->getCollection($collection)->insert($doc);
    }

    public function save($collection, &$data) {
        return $this->getCollection($collection)->save($data);
    }

    public function update($collection, $criteria, $data) {
        return $this->getCollection($collection)->update($criteria, $data);
    }

    public function remove($collection, $filter=[]) {
        return $this->getCollection($collection)->remove($filter);
    }
}