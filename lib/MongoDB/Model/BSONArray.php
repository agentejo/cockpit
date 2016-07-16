<?php

namespace MongoDB\Model;

use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use ArrayObject;
use JsonSerializable;

/**
 * Model class for a BSON array.
 *
 * The internal data will be filtered through array_values() during BSON
 * serialization to ensure that it becomes a BSON array.
 *
 * @api
 */
class BSONArray extends ArrayObject implements JsonSerializable, Serializable, Unserializable
{
    /**
     * Factory method for var_export().
     *
     * @see http://php.net/oop5.magic#object.set-state
     * @see http://php.net/var-export
     * @param array $properties
     * @return self
     */
    public static function __set_state(array $properties)
    {
        $array = new static;
        $array->exchangeArray($properties);

        return $array;
    }

    /**
     * Serialize the array to BSON.
     *
     * The array data will be numerically reindexed to ensure that it is stored
     * as a BSON array.
     *
     * @see http://php.net/mongodb-bson-serializable.bsonserialize
     * @return array
     */
    public function bsonSerialize()
    {
        return array_values($this->getArrayCopy());
    }

    /**
     * Unserialize the document to BSON.
     *
     * @see http://php.net/mongodb-bson-unserializable.bsonunserialize
     * @param array $data Array data
     */
    public function bsonUnserialize(array $data)
    {
        self::__construct($data);
    }

    /**
     * Serialize the array to JSON.
     *
     * The array data will be numerically reindexed to ensure that it is stored
     * as a JSON array.
     *
     * @see http://php.net/jsonserializable.jsonserialize
     * @return array
     */
    public function jsonSerialize()
    {
        return array_values($this->getArrayCopy());
    }
}
