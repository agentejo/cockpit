<?php

namespace MongoLite;

/**
 * Collection object.
 */
class Collection {

    /**
     * @var object Database
     */
    public $database;

    /**
     * @var string
     */
    public $name;

    /**
     * Constructor
     *
     * @param string $name
     * @param object $database
     */
    public function __construct($name, $database) {
        $this->name = $name;
        $this->database = $database;
    }

    /**
     * Drop collection
     */
    public function drop() {
        $this->database->dropCollection($this->name);
    }

    /**
     * Insert document
     *
     * @param  array $document
     * @return mixed last_insert_id for single document or
     * count count of inserted documents for arrays
     */
    public function insert(&$document) {

        if (isset($document[0])) {

            $this->database->connection->beginTransaction();

            foreach ($document as &$doc) {

                if(!is_array($doc)) continue;

                $res = $this->_insert($doc);
                if(!$res) {
                    $this->database->connection->rollBack();
                    return $res;
                }
            }
            $this->database->connection->commit();
            return count($document);
        } else {
            return $this->_insert($document);
        }
    }
    /**
     * Insert document
     *
     * @param  array $document
     * @return mixed
     */
    protected function _insert(&$document) {

        $table           = $this->name;
        $document["_id"] = uniqid().'doc'.rand();
        $data            = array("document" => json_encode($document, JSON_NUMERIC_CHECK));

        $fields = array();
        $values = array();

        foreach($data as $col=>$value){
            $fields[] = "`{$col}`";
            $values[] = (is_null($value) ? 'NULL':$this->database->connection->quote($value));
        }

        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";

        $res = $this->database->connection->exec($sql);

        if($res){
            return $this->database->connection->lastInsertId();
        }else{
            trigger_error('SQL Error: '.implode(', ', $this->database->connection->errorInfo()).":\n".$sql);
            return false;
        }
    }

    /**
     * Save document
     *
     * @param  array $document
     * @return mixed
     */
    public function save(&$document) {

        return isset($document["_id"]) ? $this->update(array("_id" => $document["_id"]), $document) : $this->insert($document);
    }

    /**
     * Update documents
     *
     * @param  mixed $criteria
     * @param  array $data
     * @return integer
     */
    public function update($criteria, $data) {

        $sql    = 'SELECT id, document FROM '.$this->name.' WHERE document_criteria("'.$this->database->registerCriteriaFunction($criteria).'", document)';
        $stmt   = $this->database->connection->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as &$doc) {

            $document = array_merge(json_decode($doc["document"], true), $data);

            $sql = "UPDATE ".$this->name." SET document=".$this->database->connection->quote(json_encode($document,JSON_NUMERIC_CHECK))." WHERE id=".$doc["id"];

            $this->database->connection->exec($sql);
        }

        return count($result);
    }

    /**
     * Remove documents
     *
     * @param  mixed $criteria
     * @return mixed
     */
    public function remove($criteria) {

        $sql = 'DELETE FROM '.$this->name.' WHERE document_criteria("'.$this->database->registerCriteriaFunction($criteria).'", document)';

        return $this->database->connection->exec($sql);
    }

    /**
     * Count documents in collections
     *
     * @param  mixed $criteria
     * @return integer
     */
    public function count($criteria = null) {

        return $this->find($criteria)->count();
    }

    /**
     * Find documents
     *
     * @param  mixed $criteria
     * @return object Cursor
     */
    public function find($criteria = null, $projection = null) {
        return new Cursor($this, $this->database->registerCriteriaFunction($criteria), $projection);
    }

    /**
     * Find one document
     *
     * @param  mixed $criteria
     * @return array
     */
    public function findOne($criteria = null, $projection = null) {

        $items = $this->find($criteria, $projection)->limit(1)->toArray();

        return isset($items[0]) ? $items[0]:null;
    }

    /**
     * Rename Collection
     *
     * @param  string $newname [description]
     * @return boolean
     */
    public function renameCollection($newname) {

        if (!in_array($newname, $this->getCollectionNames())) {

            $this->database->connection->exec("ALTER TABLE '.$this->name.' RENAME TO {$newname}");

            $this->name = $newname;

            return true;
        }

        return false;
    }
}
