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

class MongoLite {

    protected $client;

    public function __construct($server, $options=[]) {

        $this->client = new \MongoLite\Client(str_replace('mongolite://', '', $server));
        $this->db     = $options['db'];
    }

    public function getCollection($name, $db = null){

        if (strpos($name, '/') !== false) {
            list($db, $name) = explode('/', $name, 2);
        }

        if (!$db) {
            $db = $this->db;
        }

        $name = str_replace('/', '_', $name);

        return $this->client->selectCollection($db, $name);
    }

    public function dropCollection($name, $db = null){

        if (strpos($name, '/') !== false) {
            list($db, $name) = explode('/', $name, 2);
        }

        if (!$db) {
            $db = $this->db;
        }

        $name = str_replace('/', '_', $name);

        return $this->client->selectDB($db)->dropCollection($name);
    }

    public function renameCollection($name, $newname, $db = null) {

        if (strpos($name, '/') !== false) {
            list($db, $name) = explode('/', $name, 2);
        }

        if (!$db) {
            $db = $this->db;
        }

        $db = $this->client->selectDB($db);

        $name = str_replace('/', '_', $name);
        $newname = str_replace('/', '_', $newname);

        if (!in_array($name, $db->getCollectionNames())) {
            return false;
        }

        $db->connection->exec("ALTER TABLE `{$name}` RENAME TO `{$newname}`");

        return true;
    }

    public function findOne($collection, $filter = [], $projection = null) {
        return $this->getCollection($collection)->findOne($filter, $projection);
    }

    public function findOneById($collection, $id){

        return $this->getCollection($collection)->findOne(['_id' => $id]);
    }

    public function find($collection, $options = []){

        $filter = isset($options['filter']) ? $options['filter'] : null;
        $fields = isset($options['fields']) && $options['fields'] ? $options['fields'] : null;
        $limit  = isset($options['limit'])  ? $options['limit'] : null;
        $sort   = isset($options['sort'])   ? $options['sort'] : null;
        $skip   = isset($options['skip'])   ? $options['skip'] : null;

        $cursor = $this->getCollection($collection)->find($filter, $fields);

        if ($limit) $cursor->limit($limit);
        if ($sort)  $cursor->sort($sort);
        if ($skip)  $cursor->skip($skip);

        $docs      = $cursor->toArray();
        $resultSet = new ResultSet($this, $docs);

        return $resultSet;
    }

    public function insert($collection, &$doc) {
        return $this->getCollection($collection)->insert($doc);
    }

    public function save($collection, &$data, $create = false) {
        return $this->getCollection($collection)->save($data, $create);
    }

    public function update($collection, $criteria, $data) {
        return $this->getCollection($collection)->update($criteria, $data);
    }

    public function remove($collection, $filter=[]) {
        return $this->getCollection($collection)->remove($filter);
    }

    public function removeField($collection, $field, $filter = []) {

        $collection = $this->getCollection($collection);

        foreach ($collection->find($filter) as $doc) {

            if (isset($doc[$field])) {
                unset($doc[$field]);
                $collection->update(['_id' => $doc['_id']], $doc, false);
            }
        }

        return true;
    }

    public function renameField($collection, $field, $newfield, $filter = []) {

        $collection = $this->getCollection($collection);

        foreach ($collection->find($filter) as $doc) {

            if (isset($doc[$field])) {
                $doc[$newfield] = $doc[$field];
                unset($doc[$field]);
                $collection->update(['_id' => $doc['_id']], $doc, false);
            }
        }

        return true;
    }

    public function count($collection, $filter=[]) {
        return $this->getCollection($collection)->count($filter);
    }
}
