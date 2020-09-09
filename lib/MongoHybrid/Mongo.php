<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MongoHybrid;

class Mongo {

    protected $client;
    protected $db;
    protected $options;

    public function __construct($server, $options=[], $driverOptions=[]) {

        $driverOptions = array_merge([
            'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']
        ], $driverOptions);

        $this->client  = new \MongoDB\Client($server, $options, $driverOptions);
        $this->db      = $this->client->selectDatabase($options['db']);
        $this->options = $options;
    }

    public function getCollection($name, $db = null){

        if ($db) {
            $name = "{$db}/{$name}";
        }

        $name = str_replace('/', '_', $name);

        return $this->db->selectCollection($name);
    }

    public function dropCollection($name, $db = null){

        if ($db) {
            $name = "{$db}/{$name}";
        }

        $name = str_replace('/', '_', $name);

        return $this->db->dropCollection($name);
    }

    public function renameCollection($name, $newname, $db = null) {

        if ($db) {
            $name = "{$db}/{$name}";
            $newname = "{$db}/{$newname}";
        }

        $name = str_replace('/', '_', $name);
        $newname = str_replace('/', '_', $newname);

        $collections = iterator_to_array($this->db->listCollections([
            'filter' => [ 'name' => $name ]
        ]));

        if (!count($collections)) {
            return false;
        }

        //$dbname = $this->db->getDatabaseName();

        // notice works for mongodb < 4.0
        $this->db->command(["eval" => "db.{$name}.renameCollection({$newname})"]);

        return true;
    }

    public function findOneById($collection, $id){

        if (is_string($id)) $id = new \MongoDB\BSON\ObjectID($id);

        $doc =  $this->getCollection($collection)->findOne(['_id' => $id]);

        if (isset($doc['_id'])) $doc['_id'] = (string) $doc['_id'];

        return $doc;
    }

    public function findOne($collection, $filter = [], $projection = []) {

        if (!$filter) $filter = [];

        $filter = $this->_fixMongoIds($filter, true);
        $doc    = $this->getCollection($collection)->findOne($filter, ['projection' => $projection]);

        if (isset($doc['_id'])) $doc['_id'] = (string) $doc['_id'];

        return $doc;
    }

    public function find($collection, $options = []){

        $filter = isset($options['filter']) && $options['filter'] ? $options['filter'] : [];
        $fields = isset($options['fields']) && $options['fields'] ? $options['fields'] : [];
        $limit  = isset($options['limit'])  && $options['limit']  ? $options['limit']  : null;
        $sort   = isset($options['sort'])   && $options['sort']   ? $options['sort']   : null;
        $skip   = isset($options['skip'])   && $options['skip']   ? $options['skip']   : null;

        $filter = $this->_fixMongoIds($filter, true);

        $cursor = $this->getCollection($collection)->find($filter, [
            'projection' => $fields,
            'limit' => $limit,
            'skip'  => $skip,
            'sort'  => $sort
        ]);

        $docs = $cursor->toArray();

        if (count($docs)) {

            foreach ($docs as &$doc) {
                if(isset($doc['_id'])) $doc['_id'] = (string) $doc['_id'];
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

        if (isset($ref['_id'])) $ref['_id'] = (string) $ref['_id'];

        $doc = $ref;

        return $return;
    }

    public function save($collection, &$data, $create = false) {

        $data = $this->_fixMongoIds($data);
        $ref  = $data;

        if (isset($data['_id'])) {

            if ($create) {
                $return = $this->getCollection($collection)->replaceOne(['_id' => $data['_id']], $ref, ['upsert' => true]);
            } else {
                $return = $this->getCollection($collection)->updateOne(['_id' => $data['_id']], ['$set' => $ref]);
            }

        } else {
            $return = $this->getCollection($collection)->insertOne($ref);
            $ref['_id'] = $return->getInsertedId();
        }

        if (isset($ref['_id'])) $ref['_id'] = (string) $ref['_id'];

        $data = $ref;

        return $return;
    }

    public function update($collection, $criteria, $data) {

        $criteria = $this->_fixMongoIds($criteria);
        $data     = $this->_fixMongoIds($data);

        return $this->getCollection($collection)->updateMany($criteria, ['$set' => $data]);
    }

    public function remove($collection, $filter=[]) {

        if (!$filter) $filter = [];

        $filter = $this->_fixMongoIds($filter);

        return $this->getCollection($collection)->deleteMany($filter);
    }

    public function removeField($collection, $field, $filter = []) {

        $opts = ['$unset' => []];
        $opts['$unset'][$field] = 1;

        return $this->getCollection($collection)->updateMany($filter, $opts);
    }

    public function renameField($collection, $field, $newfield, $filter = []) {

        $opts = ['$rename' => []];
        $opts['$rename'][$field] = $newfield;

        return $this->getCollection($collection)->updateMany($filter, $opts);
    }

    public function count($collection, $filter=[], $options=[]) {

        if (!$filter) $filter = [];

        $filter = $this->_fixMongoIds($filter, true);

        return $this->getCollection($collection)->countDocuments($filter, $options);
    }

    protected function _fixMongoIds(&$data, $infinite = false, $_level = 0) {

        if (!is_array($data)) {
            return $data;
        }

        if ($_level == 0 && isset($data[0])) {
            foreach ($data as $i => $doc) {
                $data[$i] = $this->_fixMongoIds($doc, $infinite);
            }
            return $data;
        }

        foreach ($data as $k => &$v) {

            if (is_array($data[$k]) && $infinite) {
                $data[$k] = $this->_fixMongoIds($data[$k], $infinite, $_level + 1);
            }

            if ($k === '_id') {

                if (is_string($v)) {
                    
                    $v = $v[0] === '@' ? \substr($v, 1) : new \MongoDB\BSON\ObjectID($v);

                } elseif (is_array($v)) {

                    if (isset($v['$in'])) {

                        foreach ($v['$in'] as &$id) {
                            if (is_string($id)) {
                                $id = new \MongoDB\BSON\ObjectID($id);
                            }
                        }
                    }
    
                    if (isset($v['$nin'])) {
    
                        foreach ($v['$nin'] as &$id) {
                            if (is_string($id)) {
                                $id = new \MongoDB\BSON\ObjectID($id);
                            }
                        }
                    }

                    if (isset($v['$ne']) && is_string($v['$ne'])) {
    
                        $v['$ne'] = new \MongoDB\BSON\ObjectID($v['$ne']);                    
                    }

                }
            }
        }

        return $data;
    }
}
